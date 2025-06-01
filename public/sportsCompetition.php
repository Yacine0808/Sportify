<?php
// public/sportsCompetition.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session (pour adapter le menu si besoin)
session_start();

// 3) Inclusion du header (navbar + CSS Bootstrap)
//    Vérifiez que header.php ne contient ni redirection, ni meta‐refresh
include __DIR__ . '/../includes/header.php';

// 4) Connexion à la base de données (PDO)
require __DIR__ . '/../includes/db.php';

// 5) Tableau fixe des disciplines “Sports de compétition”
$disciplines = [
    'Basketball',
    'Football',
    'Rugby',
    'Tennis',
    'Natation',
    'Plongeon'
];

// 6) Mapping discipline 
$imageMap = [
    'Basketball' => 'BASKET.png',
    'Football'   => 'FOOT.png',
    'Rugby'      => 'RUGBY.png',
    'Tennis'     => 'TENNIS.png',
    'Natation'   => 'NATATION.png',
    'Plongeon'   => 'PLONGEON.png'
];
?>
<main class="container py-5">
  <h1 class="mb-4">Sports de compétition</h1>

  <div class="row gx-3 gy-4">
    <?php foreach ($disciplines as $disc): ?>
      <?php
        // a) Compter le nombre de coachs pour cette discipline
        $stmt = $pdo->prepare("
          SELECT COUNT(*) AS nb
            FROM `personel/coach`
           WHERE Specialty_Coach = ?
        ");
        $stmt->execute([$disc]);
        $row      = $stmt->fetch(PDO::FETCH_ASSOC);
        $nbCoachs = (int)($row['nb'] ?? 0);

        // b) URL de la page de détail
        $urlDetail = "sportDetail.php?discipline=" . urlencode($disc);

        // c) Déterminer le chemin de l’image de fond pour ce sport
        //    Note : sportsCompetition.php est dans public/, donc on remonte d’un niveau vers assets/
        $imgFile = $imageMap[$disc] ?? '';
        $imgUrl  = $imgFile
          ? "../assets/images/sportsCompetition/{$imgFile}"
          : "../assets/images/sportsCompetition/default.jpg";
      ?>

      <div class="col-sm-6 col-md-4">
        <a href="<?= htmlspecialchars($urlDetail) ?>" class="text-decoration-none">
          <div class="border rounded h-100 d-flex flex-column justify-content-between text-white"
               style="
                 /* On superpose un voile gris (semi‐transparent) sur l’image de fond */
                 background:
                   linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
                   url('<?= htmlspecialchars($imgUrl) ?>') no-repeat center center;
                 background-size: cover;
                 padding: 1rem;
               ">
            <div>
              <h2 class="h5"><?= htmlspecialchars($disc) ?></h2>
              <p class="mb-0">
                <strong><?= $nbCoachs ?></strong>
                coach<?= $nbCoachs > 1 ? 's' : '' ?> disponible<?= $nbCoachs > 1 ? 's' : '' ?>.
              </p>
            </div>
            <div class="text-center mt-3">
              <span class="btn btn-light btn-sm">Voir les coachs</span>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
