<?php
// public/dashboard.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session 
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}
$userId = (int) $_SESSION['user_id'];

// 3) Connexion à la BDD (PDO)
require __DIR__ . '/../includes/db.php';

// 4) Inclusion du header (navbar + Bootstrap)
include __DIR__ . '/../includes/header.php';

// 5) Récupération des données du client
$stmtUser = $pdo->prepare("SELECT * FROM client WHERE ID_User = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// 6) Masquage des données bancaires
$maskedCard = str_repeat('*', max(0, strlen($user['Numero_Carte_User']) - 4))
            . substr($user['Numero_Carte_User'], -4);
$maskedSec  = str_repeat('*', max(0, strlen($user['Code_Securite_User']) - 1))
            . substr($user['Code_Securite_User'], -1);

// 7) Choix de l’onglet )
$tab = $_GET['tab'] ?? 'profile';
$validTabs = ['profile','history','payment'];
if (!in_array($tab, $validTabs, true)) {
    $tab = 'profile';
}

// 8) Si l’onglet est “history”, charger uniquement les rendez-vous coach passés + toutes les réservations salle
$coachAppointments = [];
$salleAppointments = [];
if ($tab === 'history') {
    // 8.a) Rendez-vous coach passés (Date_RDV < NOW())
    $sqlCoach = "
      SELECT
        r.ID_RDV,
        r.Date_RDV,
        r.Statut_RDV,
        c.ID_Coach,
        CONCAT(c.Name_Coach, ' ', c.LName_Coach) AS Nom_Prenom_Coach,
        c.Specialty_Coach
      FROM `rdv` AS r
      INNER JOIN `personel/coach` AS c
        ON r.ID_Coach = c.ID_Coach
      WHERE r.User_id = :uid
        AND r.Date_RDV < NOW()
      ORDER BY r.Date_RDV DESC
    ";
    $stmtCoach = $pdo->prepare($sqlCoach);
    $stmtCoach->execute([':uid' => $userId]);
    $coachAppointments = $stmtCoach->fetchAll(PDO::FETCH_ASSOC);

    // 8.b) Réservations de salle (aucun filtrage temporel possible sans date réelle)
    $sqlSalle = "
      SELECT
        rs.ID_Rdv            AS ID_RdvSalle,
        rs.ID_Salle,
        rs.DayOfWeek,
        rs.StartTime,
        rs.CreatedAt,
        s.Numero_Salle
      FROM `rdv_salle` AS rs
      INNER JOIN `salle de sport` AS s
        ON rs.ID_Salle = s.ID_Salle
      WHERE rs.User_id = :uid
      ORDER BY rs.CreatedAt DESC
    ";
    $stmtSalle = $pdo->prepare($sqlSalle);
    $stmtSalle->execute([':uid' => $userId]);
    $salleAppointments = $stmtSalle->fetchAll(PDO::FETCH_ASSOC);
}

// 9) Fonction utilitaire : convertir DayOfWeek (0–6) en texte
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
  <h2>Mon compte</h2>

  <!-- Navigation par onglets -->
  <div class="mb-4">
    <a href="dashboard.php?tab=profile"
       class="btn btn-outline-primary <?= $tab==='profile'?'active':'' ?>">
      Profil
    </a>
    <a href="dashboard.php?tab=history"
       class="btn btn-outline-primary <?= $tab==='history'?'active':'' ?>">
      Historique
    </a>
    <a href="dashboard.php?tab=payment"
       class="btn btn-outline-primary <?= $tab==='payment'?'active':'' ?>">
      Paiement
    </a>
  </div>

  <!-- ====================== Onglet “Profil” ====================== -->
  <?php if ($tab === 'profile'): ?>

    <h3>Profil</h3>
    <ul class="list-group mb-4">
      <li class="list-group-item">
        <strong>Nom :</strong> <?= htmlspecialchars($user['Name_User']) ?>
      </li>
      <li class="list-group-item">
        <strong>Prénom :</strong> <?= htmlspecialchars($user['LName_User']) ?>
      </li>
      <li class="list-group-item">
        <strong>Email :</strong> <?= htmlspecialchars($user['EMail_User']) ?>
      </li>
      <li class="list-group-item">
        <strong>Adresse :</strong>
        <?= htmlspecialchars($user['AdressLigne1_User']) ?>
        <?php if (!empty($user['AdressLigne2_User'])): ?>
          , <?= htmlspecialchars($user['AdressLigne2_User']) ?>
        <?php endif; ?>
        , <?= htmlspecialchars($user['Ville_User']) ?> <?= htmlspecialchars($user['CodePostal_User']) ?>,
        <?= htmlspecialchars($user['Pays_User']) ?>
      </li>
      <li class="list-group-item">
        <strong>Téléphone :</strong> <?= htmlspecialchars($user['Telephone_User']) ?>
      </li>
    </ul>

  <!-- ====================== Onglet “Historique” (lecture seule) ====================== -->
  <?php elseif ($tab === 'history'): ?>

    <h3>Historique des rendez-vous</h3>

    <!-- ----- 8.a) Cours particuliers avec un coach (uniquement passés) ----- -->
    <section class="mb-5">
      <h4 class="mb-3">Cours particuliers avec un coach</h4>

      <?php if (empty($coachAppointments)): ?>
        <div class="alert alert-info">
          Vous n’avez aucun rendez-vous coach passé pour le moment.
        </div>
      <?php else: ?>
        <div class="row gy-4">
          <?php foreach ($coachAppointments as $rdv): 
            // Formater la date en “d/m/Y à H:i”
            $dt = new DateTime($rdv['Date_RDV']);
            $dateTexte = $dt->format('d/m/Y \à H:i');
            // Statut : 1 = confirmé, 0 = annulé
            $statut = ((int)$rdv['Statut_RDV'] === 1) ? 'Confirmé' : 'Annulé';
          ?>
            <div class="col-md-6">
              <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-2">
                    <?= htmlspecialchars($rdv['Nom_Prenom_Coach']) ?>
                  </h5>
                  <p class="mb-1">
                    <strong>Spécialité :</strong> <?= htmlspecialchars($rdv['Specialty_Coach']) ?>
                  </p>
                  <p class="mb-1">
                    <strong>Date &amp; Heure :</strong> <?= htmlspecialchars($dateTexte) ?>
                  </p>
                  <p class="mb-1">
                    <strong>Statut :</strong> <?= htmlspecialchars($statut) ?>
                  </p>
                  <!-- *** Plus de bouton “Annuler” ici *** -->
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <hr>

    <!-- ----- 8.b) Réservations de salle ----- -->
    <section>
      <h4 class="mb-3">Réservations de la salle de sport</h4>

      <?php if (empty($salleAppointments)): ?>
        <div class="alert alert-info">
          Vous n’avez aucune réservation de salle pour le moment.
        </div>
      <?php else: ?>
        <div class="row gy-4">
          <?php foreach ($salleAppointments as $rs):
            // Convertion DayOfWeek → texte
            $jour = jourTexte((int)$rs['DayOfWeek']);
            // Heure de début “HH:MM” puis +1h pour fin
            $heureDebut = substr($rs['StartTime'], 0, 5);
            $heureFin   = date('H:i', strtotime($rs['StartTime'] . ' +1 hour'));
            // Date de création “CreatedAt”
            $created = new DateTime($rs['CreatedAt']);
            $createdTexte = $created->format('d/m/Y H:i');
          ?>
            <div class="col-md-6">
              <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-2">
                    Salle #<?= (int)$rs['ID_Salle'] ?>
                    <?php if (!empty($rs['Numero_Salle'])): ?>
                      (<?= htmlspecialchars($rs['Numero_Salle']) ?>)
                    <?php endif; ?>
                  </h5>
                  <p class="mb-1">
                    <strong>Jour :</strong> <?= htmlspecialchars($jour) ?>
                  </p>
                  <p class="mb-1">
                    <strong>Créneau :</strong> <?= htmlspecialchars($heureDebut) ?> – <?= htmlspecialchars($heureFin) ?>
                  </p>
                  <p class="mb-1">
                    <strong>Réservé le :</strong> <?= htmlspecialchars($createdTexte) ?>
                  </p>
                  <!-- *** Plus de bouton “Annuler” ici *** -->
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  <!-- ====================== Onglet “Paiement” ====================== -->
  <?php else: // $tab === 'payment' ?>

    <h3>Informations bancaires</h3>
    <ul class="list-group mb-4">
      <li class="list-group-item">
        <strong>Type de carte :</strong> <?= htmlspecialchars($user['Carte_Bleu_User']) ?>
      </li>
      <li class="list-group-item">
        <strong>Numéro :</strong> <?= htmlspecialchars($maskedCard) ?>
      </li>
      <li class="list-group-item">
        <strong>Code sécurité :</strong> <?= htmlspecialchars($maskedSec) ?>
      </li>
      <li class="list-group-item">
        <strong>Expiration :</strong> <?= htmlspecialchars($user['Date_Expiration_User']) ?>
      </li>
    </ul>

  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
