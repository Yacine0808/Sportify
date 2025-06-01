<?php
// public/gestionSalles.php

// 1) Affichage des erreurs (développement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Protection : seuls les utilisateurs ayant le rôle "admin" peuvent accéder
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// 3) Connexion à la base de données et inclusion du header
require __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';

// 4) Messages d’état
$messageSalle   = '';
$errorSalle     = '';
$messageCreneau = '';
$errorCreneau   = '';

// 5) Section « Édition / Pré-remplissage d’une salle » via ?edit_salle=ID_Salle
$salleId        = '';
$numeroSalle    = '';
$emailSalle     = '';
$telephoneSalle = '';
$existingSlots  = [];  // contiendra les créneaux indexés par DayOfWeek

if (isset($_GET['edit_salle']) && ctype_digit($_GET['edit_salle'])) {
    $tmpId = (int) $_GET['edit_salle'];
    if ($tmpId > 0) {
        // 5.a) Récupérer la salle
        $stmt = $pdo->prepare("SELECT * FROM `salle de sport` WHERE ID_Salle = ?");
        $stmt->execute([$tmpId]);
        $s = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($s) {
            $salleId        = $s['ID_Salle'];
            $numeroSalle    = $s['Numero_Salle'];
            $emailSalle     = $s['EMail_Salle'];
            $telephoneSalle = $s['Telephone_Salle'];

            // 5.b) Récupérer tous les créneaux existants pour cette salle
            $stmt2 = $pdo->prepare("
                SELECT DayOfWeek, StartTime, EndTime
                  FROM `planning_salle`
                 WHERE ID_Salle = ?
            ");
            $stmt2->execute([$tmpId]);
            $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            // Indexer par DayOfWeek pour préremplir facilement
            foreach ($rows as $r) {
                $dow = intval($r['DayOfWeek']);
                $existingSlots[$dow] = [
                    'start' => substr($r['StartTime'], 0, 5),
                    'end'   => substr($r['EndTime'],   0, 5)
                ];
            }
        }
    }
}

// 6) Traitement du formulaire « Ajouter / Éditer une salle »
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_salle']) && $_POST['action_salle'] === 'valider_salle') {
    $salleIdPost     = trim($_POST['salle_id'] ?? '');
    $emailSallePost  = trim($_POST['email_salle'] ?? '');
    $telephonePost   = trim($_POST['telephone_salle'] ?? '');

    // Validation
    if ($emailSallePost === '' || $telephonePost === '') {
        $errorSalle = 'Tous les champs (e-mail, téléphone) sont obligatoires.';
    } elseif (!filter_var($emailSallePost, FILTER_VALIDATE_EMAIL)) {
        $errorSalle = 'Le format de l’e-mail est invalide.';
    }

    if ($errorSalle === '') {
        try {
            if ($salleIdPost !== '') {
                // === UPDATE existant ===
                $upd = $pdo->prepare("
                  UPDATE `salle de sport`
                  SET EMail_Salle = ?, Telephone_Salle = ?
                  WHERE ID_Salle = ?
                ");
                $upd->execute([
                    $emailSallePost,
                    $telephonePost,
                    (int)$salleIdPost
                ]);
                $messageSalle = "Salle #{$salleIdPost} mise à jour avec succès.";
                // Réinitialiser pour afficher formulaire vide
                $salleId        = '';
                $numeroSalle    = '';
                $emailSalle     = '';
                $telephoneSalle = '';
                $existingSlots  = [];
            } else {
                // === INSERT d’une nouvelle salle avec numéro automatique ===
                // Calculer le prochain numéro disponible
                $maxStmt = $pdo->query("SELECT MAX(Numero_Salle) AS max_num FROM `salle de sport`");
                $maxRow  = $maxStmt->fetch(PDO::FETCH_ASSOC);
                $nextNumero = (int)$maxRow['max_num'] + 1;

                $ins = $pdo->prepare("
                  INSERT INTO `salle de sport`
                    (Numero_Salle, EMail_Salle, Telephone_Salle)
                  VALUES (?, ?, ?)
                ");
                $ins->execute([
                    $nextNumero,
                    $emailSallePost,
                    $telephonePost
                ]);
                $newId = $pdo->lastInsertId();
                $messageSalle = "Nouvelle salle créée (ID={$newId}, Numéro={$nextNumero}).";
                // Assigner au formulaire pour permettre l’édition/ajout de créneaux
                $salleId        = $newId;
                $numeroSalle    = $nextNumero;
                $emailSalle     = $emailSallePost;
                $telephoneSalle = $telephonePost;
                $existingSlots  = [];
            }
        } catch (Exception $e) {
            $errorSalle = "Erreur BDD (salle) : " . htmlspecialchars($e->getMessage());
        }
    }
}

// 7) Traitement du formulaire « Ajouter/Mise à jour des créneaux hebdomadaires »
// On lit 7 jours (0=Dimanche … 6=Samedi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_creneau']) && $_POST['action_creneau'] === 'valider_creneau') {
    $salleIdCreneau = intval($_POST['salle_id_creneau'] ?? 0);

    // 7.a) Récupérer les 7 créneaux via POST
    $slotsSalle = [];
    for ($day = 0; $day < 7; $day++) {
        $hDeb = trim($_POST['startTime'][$day] ?? '');
        $hFin = trim($_POST['endTime'][$day]   ?? '');
        if ($hDeb !== '' && $hFin !== '') {
            if (preg_match('/^\d{2}:\d{2}$/', $hDeb) && preg_match('/^\d{2}:\d{2}$/', $hFin)) {
                $slotsSalle[] = [
                    'day'   => $day,
                    'start' => $hDeb . ':00',
                    'end'   => $hFin . ':00'
                ];
            }
        }
    }

    // 7.b) Validation minimale
    if ($salleIdCreneau <= 0) {
        $errorCreneau = 'Veuillez d’abord créer ou sélectionner une salle.';
    } elseif (empty($slotsSalle)) {
        $errorCreneau = 'Au moins un créneau doit être rempli.';
    }

    // 7.c) Si ok, on efface les anciens créneaux et on insère les nouveaux
    if ($errorCreneau === '') {
        try {
            // Supprimer tous les créneaux existants pour cette salle
            $del = $pdo->prepare("DELETE FROM `planning_salle` WHERE ID_Salle = ?");
            $del->execute([$salleIdCreneau]);

            // Insérer chacun des créneaux valides
            foreach ($slotsSalle as $slot) {
                $insC = $pdo->prepare("
                  INSERT INTO `planning_salle`
                    (ID_Salle, DayOfWeek, StartTime, EndTime)
                  VALUES (?, ?, ?, ?)
                ");
                $insC->execute([
                    $salleIdCreneau,
                    $slot['day'],
                    $slot['start'],
                    $slot['end']
                ]);
            }

            $messageCreneau = "Créneaux mis à jour pour la salle #{$salleIdCreneau}.";
            // Recharger les créneaux pour préremplissage
            $stmt3 = $pdo->prepare("SELECT DayOfWeek, StartTime, EndTime FROM `planning_salle` WHERE ID_Salle = ?");
            $stmt3->execute([$salleIdCreneau]);
            $rows2 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            $existingSlots = [];
            foreach ($rows2 as $r) {
                $dow = intval($r['DayOfWeek']);
                $existingSlots[$dow] = [
                    'start' => substr($r['StartTime'], 0, 5),
                    'end'   => substr($r['EndTime'],   0, 5)
                ];
            }
        } catch (Exception $e) {
            $errorCreneau = "Erreur BDD (créneau) : " . htmlspecialchars($e->getMessage());
        }
    }
}

// 8) Charger la liste des salles (pour affichage et dropdown)
$allSalles = $pdo->query("SELECT * FROM `salle de sport` ORDER BY ID_Salle DESC")
                 ->fetchAll(PDO::FETCH_ASSOC);

// 9) Charger la liste des créneaux (JOIN salle de sport → Numero_Salle)
$allCreneaux = $pdo->query("
  SELECT p.ID_Planning, p.ID_Salle, s.Numero_Salle, p.DayOfWeek, p.StartTime, p.EndTime
    FROM `planning_salle` p
    JOIN `salle de sport` s ON p.ID_Salle = s.ID_Salle
   ORDER BY p.ID_Salle, p.DayOfWeek, p.StartTime
")->fetchAll(PDO::FETCH_ASSOC);

// 10) Fonction utilitaire pour traduire DayOfWeek → texte
function jourTexte(int $d): string {
    $map = [
      0 => 'Dimanche',
      1 => 'Lundi',
      2 => 'Mardi',
      3 => 'Mercredi',
      4 => 'Jeudi',
      5 => 'Vendredi',
      6 => 'Samedi'
    ];
    return $map[$d] ?? '';
}
?>

<main class="container py-5" style="max-width:900px;">
  <h2 class="mb-4">Gestion des salles</h2>

  <!-- 11) Messages Salle -->
  <?php if ($messageSalle): ?>
    <div class="alert alert-success"><?= $messageSalle ?></div>
  <?php endif; ?>
  <?php if ($errorSalle): ?>
    <div class="alert alert-danger"><?= $errorSalle ?></div>
  <?php endif; ?>

  <!-- 12) Formulaire d’ajout / édition de la salle -->
  <div class="card mb-4">
    <div class="card-header">
      <?= $salleId !== '' ? "Éditer la salle #{$salleId}" : 'Ajouter une nouvelle salle' ?>
    </div>
    <div class="card-body">
      <form method="post" class="row g-3">
        <!-- Champ caché pour l’ID, attribué automatiquement -->
        <input type="hidden" name="salle_id" value="<?= htmlspecialchars($salleId) ?>">

        <div class="col-md-3">
          <label for="numero_salle" class="form-label">Numéro de salle</label>
          <input
            type="text"
            id="numero_salle"
            name="numero_salle"
            class="form-control"
            required
            value="<?= htmlspecialchars($numeroSalle) ?>"
            pattern="\d+"
            title="Entrez uniquement des chiffres"
            <?= $salleId === '' ? 'disabled' : '' ?>
          >
        </div>

        <div class="col-md-4">
          <label for="email_salle" class="form-label">E-mail de la salle</label>
          <input
            type="text"
            id="email_salle"
            name="email_salle"
            class="form-control"
            required
            value="<?= htmlspecialchars($emailSalle) ?>"
          >
        </div>

        <div class="col-md-3">
          <label for="telephone_salle" class="form-label">Téléphone</label>
          <input
            type="tel"
            id="telephone_salle"
            name="telephone_salle"
            class="form-control"
            required
            value="<?= htmlspecialchars($telephoneSalle) ?>"
          >
        </div>

        <div class="col-12">
          <button
            type="submit"
            name="action_salle"
            value="valider_salle"
            class="btn btn-primary"
          >
            <?= $salleId !== '' ? 'Mettre à jour la salle' : 'Ajouter la salle' ?>
          </button>
          <?php if ($salleId !== ''): ?>
            <a href="gestionSalles.php" class="btn btn-secondary">Annuler</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- 13) Tableau des salles existantes -->
  <h3>Liste des salles</h3>
  <?php if (empty($allSalles)): ?>
    <p>Aucune salle enregistrée.</p>
  <?php else: ?>
    <table class="table table-striped mb-5">
      <thead>
        <tr>
          <th>ID</th>
          <th>Numéro</th>
          <th>E-mail</th>
          <th>Téléphone</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allSalles as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['ID_Salle']) ?></td>
            <td><?= htmlspecialchars($s['Numero_Salle']) ?></td>
            <td><?= htmlspecialchars($s['EMail_Salle']) ?></td>
            <td><?= htmlspecialchars($s['Telephone_Salle']) ?></td>
            <td>
              <a
                href="gestionSalles.php?edit_salle=<?= $s['ID_Salle'] ?>"
                class="btn btn-sm btn-primary"
              >Éditer</a>
              <a
                href="deleteSalle.php?id=<?= $s['ID_Salle'] ?>"
                class="btn btn-sm btn-danger"
                onclick="return confirm('Supprimer la salle #<?= $s['ID_Salle'] ?> ?')"
              >Supprimer</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <!-- 14) Messages Créneau -->
  <?php if ($messageCreneau): ?>
    <div class="alert alert-success"><?= $messageCreneau ?></div>
  <?php endif; ?>
  <?php if ($errorCreneau): ?>
    <div class="alert alert-danger"><?= $errorCreneau ?></div>
  <?php endif; ?>

  <!-- 15) Formulaire d’ajout / édition des créneaux hebdomadaires pour la salle sélectionnée -->
  <div class="card mb-4">
    <div class="card-header">Ajouter / Modifier les créneaux hebdomadaires</div>
    <div class="card-body">
      <form method="post" class="row g-3 mb-3">
        <input type="hidden" name="action_creneau" value="valider_creneau">
        <input type="hidden" name="salle_id_creneau" value="<?= htmlspecialchars($salleId) ?>">

        <h5 class="mb-2">Disponibilités hebdomadaires (sept jours)</h5>
        <p class="text-muted mb-3">
          Pour chaque jour, indiquez l’heure de début et de fin. Laissez vide si indisponible.
        </p>

        <?php
        // Boucle pour chaque jour (0=Dimanche … 6=Samedi)
        for ($day = 0; $day < 7; $day++):
          $dayName = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'][$day];
          $prefillStart = $existingSlots[$day]['start'] ?? '';
          $prefillEnd   = $existingSlots[$day]['end'] ?? '';
        ?>
          <div class="row mb-2">
            <div class="col-md-3">
              <label class="form-label"><?= $dayName ?></label>
            </div>
            <div class="col-md-3">
              <input
                type="time"
                name="startTime[<?= $day ?>]"
                class="form-control"
                value="<?= htmlspecialchars($prefillStart) ?>"
              >
            </div>
            <div class="col-md-3">
              <input
                type="time"
                name="endTime[<?= $day ?>]"
                class="form-control"
                value="<?= htmlspecialchars($prefillEnd) ?>"
              >
            </div>
          </div>
        <?php endfor; ?>

        <div class="row mt-3">
          <div class="col">
            <button
              type="submit"
              class="btn btn-success"
              <?= ($salleId === '') ? 'disabled title="Créez d’abord la salle"' : '' ?>
            >Enregistrer les créneaux</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- 16) Affichage du tableau des créneaux existants -->
  <h3>Disponibilités hebdomadaires (créneaux existants)</h3>
  <?php if (empty($allCreneaux)): ?>
    <p>Aucun créneau défini.</p>
  <?php else: ?>
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th># Salle</th>
          <th>Jour</th>
          <th>Début</th>
          <th>Fin</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allCreneaux as $cr): ?>
          <tr>
            <td><?= htmlspecialchars($cr['ID_Planning']) ?></td>
            <td><?= htmlspecialchars($cr['Numero_Salle']) ?></td>
            <td><?= jourTexte((int)$cr['DayOfWeek']) ?></td>
            <td><?= htmlspecialchars($cr['StartTime']) ?></td>
            <td><?= htmlspecialchars($cr['EndTime']) ?></td>
            <td>
              <a
                href="deletePlanningSalle.php?id=<?= $cr['ID_Planning'] ?>"
                class="btn btn-sm btn-danger"
                onclick="return confirm('Supprimer ce créneau ?')"
              >Supprimer</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
