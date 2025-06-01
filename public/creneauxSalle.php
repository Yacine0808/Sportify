<?php
// public/creneauxSalle.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session
session_start();

// 3) Inclusion du header (navbar + CSS Bootstrap)
include __DIR__ . '/../includes/header.php';

// 4) Connexion à la base de données (PDO)
require __DIR__ . '/../includes/db.php';

// 5) Vérifier la présence de l’ID de salle dans l’URL
if (!isset($_GET['salle_id']) || !ctype_digit($_GET['salle_id'])) {
    echo "<main class='container py-5'>
            <div class='alert alert-danger text-center'>Salle introuvable.</div>
          </main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}
$salleId = (int) $_GET['salle_id'];

// 6) Récupérer les infos de la salle pour titre
$stmtSalle = $pdo->prepare("
    SELECT Numero_Salle 
      FROM `salle de sport`
     WHERE ID_Salle = ?
");
$stmtSalle->execute([$salleId]);
$salleInfo = $stmtSalle->fetch(PDO::FETCH_ASSOC);
if (!$salleInfo) {
    echo "<main class='container py-5'>
            <div class='alert alert-danger text-center'>Salle introuvable.</div>
          </main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}
$numeroSalle = htmlspecialchars($salleInfo['Numero_Salle']);

// 7) Récupérer tous les créneaux hebdomadaires pour cette salle
$stmtCreneaux = $pdo->prepare("
    SELECT DayOfWeek, StartTime, EndTime
      FROM `planning_salle`
     WHERE ID_Salle = ?
     ORDER BY DayOfWeek, StartTime
");
$stmtCreneaux->execute([$salleId]);
$creneauxRaw = $stmtCreneaux->fetchAll(PDO::FETCH_ASSOC);

// 8) Fonction pour découper un intervalle (HH:MM -> HH:MM) en segments d’une heure
function decouperEnUnitesDHeure(string $start, string $end): array {
    $slots = [];
    try {
        $tStart = new DateTime($start);
        $tEnd   = new DateTime($end);
    } catch (Exception $e) {
        return [];
    }
    while ($tStart < $tEnd) {
        $tSlotEnd = (clone $tStart)->modify('+1 hour');
        if ($tSlotEnd > $tEnd) {
            // si le créneau ne fait pas une heure complète, on ne l'inclut pas
            break;
        }
        $slots[] = [
            'de' => $tStart->format('H:i'),
            'a'  => $tSlotEnd->format('H:i'),
        ];
        $tStart->modify('+1 hour');
    }
    return $slots;
}

// 9) Organiser les créneaux par jour de la semaine
//     $creneauxHoraires[$jour][ "HH:MM-HH:MM" ] = ['de'=>'HH:MM','a'=>'HH:MM']
$creneauxHoraires = [];
foreach ($creneauxRaw as $row) {
    $d = (int) $row['DayOfWeek'];                  // 0=Dimanche … 6=Samedi
    $sCourt = substr($row['StartTime'], 0, 5);      // ex. "08:00"
    $eCourt = substr($row['EndTime'],   0, 5);      // ex. "12:00"
    $segments = decouperEnUnitesDHeure($sCourt, $eCourt);
    if (!isset($creneauxHoraires[$d])) {
        $creneauxHoraires[$d] = [];
    }
    foreach ($segments as $seg) {
        $key = "{$seg['de']}-{$seg['a']}";
        $creneauxHoraires[$d][$key] = $seg;
    }
}

// 10) Récupérer les créneaux déjà réservés pour cette salle (depuis rdv_salle)
$stmtRes = $pdo->prepare("
    SELECT DayOfWeek, StartTime
      FROM `rdv_salle`
     WHERE ID_Salle = ?
");
$stmtRes->execute([$salleId]);
$resRaw = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

// 11) Transformer en un tableau indexé par jour et tranche horaire
//     $reserves[$jour] = ['HH:MM-HH:MM', ...]
$reserves = [];
foreach ($resRaw as $r) {
    $d = (int) $r['DayOfWeek'];
    $startCourt = substr($r['StartTime'], 0, 5);
    // on ajoute +1h pour la fin (tous les RDV salle sont d’une heure)
    $tEnd = date('H:i', strtotime($startCourt . ' +1 hour'));
    $key = "{$startCourt}-{$tEnd}";
    if (!isset($reserves[$d])) {
        $reserves[$d] = [];
    }
    $reserves[$d][] = $key;
}

// 12) Définir les heures fixes :
//    - On affiche de 08:00 à 21:00 (le créneau 21:00–22:00 sera visible)
//    Les jours où la salle ferme à 20 h (dimanche -> vendredi), la dernière tranche (20–21) s’affichera en “—”.
//    Le samedi, la tranche 21 h–22 h sera réservable si elle est planifiée en base.
$heures = [];
for ($h = 8; $h <= 21; $h++) {
    $heures[] = sprintf('%02d:00', $h);
}

// 13) Tableau pour les noms des jours
$joursTexte = [
    0 => 'Dimanche',
    1 => 'Lundi',
    2 => 'Mardi',
    3 => 'Mercredi',
    4 => 'Jeudi',
    5 => 'Vendredi',
    6 => 'Samedi'
];
?>

<main class="container py-5">
  <h1 class="mb-4 text-center">
    Réserver un créneau — Salle #<?= $numeroSalle ?>
  </h1>

  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-light">
        <tr>
          <th>Heure</th>
          <?php foreach ($joursTexte as $idx => $nom): ?>
            <th><?= htmlspecialchars($nom) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($heures as $h): ?>
          <tr>
            <td class="fw-bold"><?= $h ?></td>
            <?php foreach (array_keys($joursTexte) as $d):
                // clé du créneau d'une heure : ex. "08:00-09:00"
                $clefSlot = "{$h}-" . sprintf('%02d:00', ((int)$h + 1));
                // est-ce que ce créneau est planifié dans planning_salle ?
                $estDispo   = isset($creneauxHoraires[$d][$clefSlot]);
                // est-ce que ce créneau a déjà été réservé (dans rdv_salle) ?
                $estRes     = $estDispo 
                             && isset($reserves[$d]) 
                             && in_array($clefSlot, $reserves[$d], true);
            ?>
              <td style="min-width:120px;">
                <?php if (!$estDispo): ?>
                  <span class="text-muted">—</span>
                <?php elseif ($estRes): ?>
                  <!-- Indicateur “Réservé” : badge bleu identique à celui de prendreRDV.php -->
                  <span class="badge bg-primary text-white">Réservé</span>
                <?php else: ?>
                  <a 
                    href="confirmerRDVSalle.php?salle_id=<?= $salleId ?>&jour=<?= $d ?>&heure=<?= urlencode($h) ?>"
                    class="btn btn-sm btn-outline-success"
                  >
                    Réserver
                  </a>
                <?php endif; ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-4 text-center">
    <a href="salleSportOmnes.php" class="btn btn-outline-dark">
      ← Retour à la liste des salles
    </a>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
