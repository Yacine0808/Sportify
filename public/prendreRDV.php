<?php
// public/prendreRDV.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 2) Redirection si visiteur non connecté
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';

// 3) Récupérer l’ID du coach (GET ou POST)
$coachId = null;
if (isset($_GET['coach_id'])) {
    $coachId = intval($_GET['coach_id']);
} elseif (isset($_POST['coach_id'])) {
    $coachId = intval($_POST['coach_id']);
}

if (empty($coachId) || $coachId <= 0) {
    echo "<main class='container py-5'><div class='alert alert-danger'>Coach non précisé.</div></main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// 4) Charger les infos du coach
$stmtCoach = $pdo->prepare("
  SELECT 
    c.ID_Coach,
    c.Name_Coach,
    c.LName_Coach,
    c.Specialty_Coach
  FROM `personel/coach` AS c
  WHERE c.ID_Coach = ?
");
$stmtCoach->execute([$coachId]);
$coachRow = $stmtCoach->fetch(PDO::FETCH_ASSOC);

if (!$coachRow) {
    echo "<main class='container py-5'><div class='alert alert-danger'>Coach introuvable.</div></main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$estFitness = (strcasecmp($coachRow['Specialty_Coach'], 'Fitness') === 0);
$userId = (int)$_SESSION['user_id'];


// 5) Si on reçoit un POST avec un créneau, on traite la réservation (ou paiement si Fitness)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_slot'])) {
    $dateTimeSlot = trim($_POST['selected_slot']);
    $error = '';

    // 5.a) Validation du créneau
    if ($dateTimeSlot === '') {
        $error = 'Aucun créneau sélectionné.';
    } else {
        try {
            $dt = new DateTime($dateTimeSlot);
            $formattedSlot = $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $error = 'Format de date invalide.';
        }
    }

    // 5.b) Si Fitness, vérifier si le paiement a été fait (via champs POST)
    if ($error === '' && $estFitness) {
        // On vérifie si l’utilisateur vient de soumettre le formulaire de paiement
        $aChampPaiement = isset($_POST['card_number'], $_POST['card_name'], $_POST['expiry_date'], $_POST['security_code']);
        if (!$aChampPaiement) {
            // On affiche le formulaire de paiement
            ?>
            <main class="container py-5">
              <h1 class="mb-4">
                Valider le paiement pour 
                <?= htmlspecialchars($coachRow['Name_Coach'] . ' ' . $coachRow['LName_Coach']) ?> (Fitness)
                <span class="badge bg-warning text-dark">Service payant</span>
              </h1>
              <form method="post" class="mb-5">
                <!-- Transmettre coach_id et slot -->
                <input type="hidden" name="coach_id" value="<?= htmlspecialchars($coachId) ?>">
                <input type="hidden" name="selected_slot" value="<?= htmlspecialchars($dateTimeSlot) ?>">

                <div class="mb-3">
                  <label for="card_type" class="form-label">Type de carte</label>
                  <select name="card_type" id="card_type" class="form-select" required>
                    <option value="">-- Sélectionnez --</option>
                    <option value="Visa">Visa</option>
                    <option value="MasterCard">MasterCard</option>
                    <option value="American Express">American Express</option>
                    <option value="PayPal">PayPal</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="card_number" class="form-label">Numéro de la carte</label>
                  <input
                    type="text"
                    id="card_number"
                    name="card_number"
                    class="form-control"
                    pattern="\d{13,19}"
                    maxlength="19"
                    required
                  >
                </div>

                <div class="mb-3">
                  <label for="card_name" class="form-label">Nom affiché sur la carte</label>
                  <input
                    type="text"
                    id="card_name"
                    name="card_name"
                    class="form-control"
                    required
                  >
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="expiry_date" class="form-label">Date d’expiration</label>
                    <input
                      type="month"
                      id="expiry_date"
                      name="expiry_date"
                      class="form-control"
                      required
                    >
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="security_code" class="form-label">Code de sécurité (CVV)</label>
                    <input
                      type="text"
                      id="security_code"
                      name="security_code"
                      class="form-control"
                      pattern="\d{3,4}"
                      maxlength="4"
                      placeholder="***"
                      required
                    >
                  </div>
                </div>

                <button type="submit" class="btn btn-success">
                  Valider le paiement et réserver
                </button>
              </form>
            </main>
            <?php
            include __DIR__ . '/../includes/footer.php';
            exit;
        }

        // 5.c) le formulaire de paiement a été soumis : on récupère les champs
        $cardNumber   = trim($_POST['card_number']);
        $cardName     = trim($_POST['card_name']);
        $expiryDate   = trim($_POST['expiry_date']);
        $securityCode = trim($_POST['security_code']);

        // 5.d) On compare aux infos en base (table `client`)
        $stmtClient = $pdo->prepare("
          SELECT 
            Numero_Carte_User,
            Nom_Carte_User,
            Date_Expiration_User,
            Code_Securite_User
          FROM `client`
          WHERE ID_User = ?
        ");
        $stmtClient->execute([$userId]);
        $clientRow = $stmtClient->fetch(PDO::FETCH_ASSOC);

        if (!$clientRow) {
            $error = 'Utilisateur introuvable en base.';
        } else {
            if (
                $cardNumber   !== $clientRow['Numero_Carte_User'] ||
                strtoupper($cardName)   !== strtoupper($clientRow['Nom_Carte_User']) ||
                $expiryDate   !== $clientRow['Date_Expiration_User'] ||
                $securityCode !== $clientRow['Code_Securite_User']
            ) {
                $error = 'Informations de carte invalides.';
            }
        }
    }

    // 5.e) vérifie qu’on n’a pas déjà réservé
    if ($error === '') {
        $stmtCheck = $pdo->prepare("
          SELECT COUNT(*) AS nb 
            FROM `rdv` 
           WHERE ID_Coach = ? 
             AND User_id = ? 
             AND Date_RDV = ?
        ");
        $stmtCheck->execute([$coachId, $userId, $formattedSlot]);
        $exists = (int)$stmtCheck->fetch(PDO::FETCH_ASSOC)['nb'];

        if ($exists > 0) {
            $error = 'Vous avez déjà réservé ce créneau pour ce coach.';
        }
    }

    // 5.f) insère la réservation
    if ($error === '') {
        $stmtInsert = $pdo->prepare("
          INSERT INTO `rdv` 
            (ID_Coach, Date_RDV, User_id, Statut_RDV) 
          VALUES (?, ?, ?, 1)
        ");
        $stmtInsert->execute([$coachId, $formattedSlot, $userId]);

        echo "<main class='container py-5'>
                <div class='alert alert-success'>
                  " . ($estFitness 
                        ? "Paiement effectué ! " 
                        : ""
                      ) . "Votre RDV avec <strong>"
                  . htmlspecialchars($coachRow['Name_Coach'] . ' ' . $coachRow['LName_Coach']) 
                  . "</strong> est confirmé pour le <strong>{$formattedSlot}</strong>.
                </div>
              </main>";
        include __DIR__ . '/../includes/footer.php';
        exit;
    }

    // 5.g) En cas d’erreur, on l’affiche
    if ($error !== '') {
        echo "<main class='container py-5'>
                <div class='alert alert-danger'>" 
                  . htmlspecialchars($error) . 
                "</div>
              </main>";
        include __DIR__ . '/../includes/footer.php';
        exit;
    }
}


// 6) Si on est en GET (ou POST sans selected_slot), on affiche la grille de planning
//    (Même pour Fitness, on n’affiche le planning qu’en GET ou avant d’avoir choisi un slot)

function getDispo(int $coachId, PDO $pdo): array {
    $s = $pdo->prepare("
      SELECT DayOfWeek, StartTime, EndTime 
      FROM `planning_coach` 
      WHERE ID_Coach = ? 
      ORDER BY DayOfWeek, StartTime
    ");
    $s->execute([$coachId]);
    return $s->fetchAll(PDO::FETCH_ASSOC);
}

$planningRows = getDispo($coachId, $pdo);

// 7) Récupérer tous les RDV existants pour ce coach (cette semaine)
$stmtRdvs = $pdo->prepare("SELECT Date_RDV FROM `rdv` WHERE ID_Coach = ?");
$stmtRdvs->execute([$coachId]);
$allRdvsRaw = $stmtRdvs->fetchAll(PDO::FETCH_COLUMN, 0);

// 8) Calcul du dimanche de la semaine courante
$today = new DateTime('today');
$weekday = (int)$today->format('w');
$sunday = clone $today;
$sunday->sub(new DateInterval("P{$weekday}D"));

// 9) Construire la grille horaires -> slotsByDay
function datePourDayOfWeek(DateTime $dimanche, int $dayOfWeek): string {
    $target = clone $dimanche;
    if ($dayOfWeek > 0) {
        $target->add(new DateInterval("P{$dayOfWeek}D"));
    }
    return $target->format('Y-m-d');
}

$slotsByDay = [];
foreach ($planningRows as $row) {
    $dow   = (int)$row['DayOfWeek'];
    $start = substr($row['StartTime'], 0, 5);
    $end   = substr($row['EndTime'],   0, 5);

    $dateBase = datePourDayOfWeek($sunday, $dow);
    try {
        $dtStart = new DateTime("$dateBase $start:00");
        $dtEnd   = new DateTime("$dateBase $end:00");
    } catch (Exception $e) {
        continue;
    }

    $cursor = clone $dtStart;
    while ($cursor < $dtEnd) {
        $slotKey   = $cursor->format('Y-m-d H:i:s');
        $slotLabel = $cursor->format('H:i');
        if (!isset($slotsByDay[$dow])) {
            $slotsByDay[$dow] = [];
        }
        $slotsByDay[$dow][] = ['key' => $slotKey, 'label' => $slotLabel];
        $cursor->add(new DateInterval('PT1H'));
    }
}

// 10) Repérer quels slots sont déjà pris cette semaine
$rdvTaken = [];
foreach ($allRdvsRaw as $dtString) {
    try {
        $d = new DateTime($dtString);
        if ($d >= $sunday && $d < (clone $sunday)->add(new DateInterval('P7D'))) {
            $key = $d->format('Y-m-d H:i:s');
            $rdvTaken[$key] = true;
        }
    } catch (Exception $e) {
        continue;
    }
}

// 11) Calculer le nombre maximal de plages horaires le plus dense
$maxSlots = 0;
for ($dow = 0; $dow <= 6; $dow++) {
    $count = isset($slotsByDay[$dow]) ? count($slotsByDay[$dow]) : 0;
    if ($count > $maxSlots) {
        $maxSlots = $count;
    }
}

// 12) Afficher la grille
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

<main class="container py-5">
  <h1 class="mb-4">
    Prendre un RDV avec 
    <?= htmlspecialchars($coachRow['Name_Coach'] . ' ' . $coachRow['LName_Coach']) ?>
    (<?= htmlspecialchars($coachRow['Specialty_Coach']) ?>)
    <?php if ($estFitness): ?>
      <span class="badge bg-warning text-dark">Service payant</span>
    <?php endif; ?>
  </h1>

  <p>
    Ci-dessous, le planning de la semaine 
    <strong><?= $sunday->format('d/m/Y') ?></strong> 
    au 
    <strong><?= (clone $sunday)->add(new DateInterval('P6D'))->format('d/m/Y') ?></strong>.
  </p>

  <?php if (empty($planningRows)): ?>
    <div class="alert alert-info">Aucun créneau défini pour ce coach.</div>
  <?php else: ?>
    <form method="post" class="mb-5">
      <input type="hidden" name="coach_id" value="<?= htmlspecialchars($coachId) ?>">

      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:12%;">Heure</th>
            <?php for ($dow = 0; $dow <= 6; $dow++): ?>
              <th><?= jourTexte($dow) ?></th>
            <?php endfor; ?>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 0; $i < $maxSlots; $i++): ?>
            <tr>
              <td class="fw-bold">
                <?php
                  $foundLabel = '';
                  for ($dow = 0; $dow <= 6; $dow++) {
                    if (isset($slotsByDay[$dow][$i])) {
                      $foundLabel = $slotsByDay[$dow][$i]['label'];
                      break;
                    }
                  }
                  echo htmlspecialchars($foundLabel);
                ?>
              </td>

              <?php for ($dow = 0; $dow <= 6; $dow++): ?>
                <td>
                  <?php
                    if (isset($slotsByDay[$dow][$i])) {
                      $slotKey = $slotsByDay[$dow][$i]['key'];
                      if (isset($rdvTaken[$slotKey])) {
                        echo '<span class="badge bg-primary text-white">Réservé</span>';
                      } else {
                        // Bouton de soumission : au clic, on envoie selected_slot=...
                        echo '<button type="submit" name="selected_slot" value="' 
                              . htmlspecialchars($slotKey) 
                              . '" class="btn btn-sm btn-outline-success">Réserver</button>';
                      }
                    } else {
                      echo '&nbsp;';
                    }
                  ?>
                </td>
              <?php endfor; ?>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </form>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
