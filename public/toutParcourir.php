<?php
// public/toutParcourir.php

// 1) Affichage des erreurs (mode dev)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session (pas de redirection ici)
session_start();

// 3) Inclusion du header (navbar, CSS Bootstrap, etc.)
//    Veillez à ce que header.php ne contient ni redirection automatique ni meta‐refresh
include __DIR__ . '/../includes/header.php';
?>
<main class="container py-5">
  <h1 class="mb-4">Tout parcourir</h1>

  <div class="row gy-4">
    <?php
      // Mapping type → image de fond 
      $imageMap = [
        'activites'      => 'ActivitesSportives.png',
        'competition'    => 'SportsCompetition.png',
        'salle'          => 'SallesdeSport.png',
      ];
    ?>

    <!-- 1. Activités sportives -->
    <div class="col-sm-6 col-md-4">
      <a href="activitesSportives.php" class="text-decoration-none">
        <div class="border rounded h-100 d-flex flex-column justify-content-between text-white"
             style="
               background:
                 linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
                 url('../assets/images/<?= $imageMap['activites'] ?>') no-repeat center center;
               background-size: cover;
               padding: 1rem;
             ">
          <div>
            <h5 class="fw-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.6);">
              Activités sportives
            </h5>
            <p style="font-size: 0.95rem;">
              Découvre toutes les activités sportives ouvertes à tous les membres d’Omnes :  
              musculation, fitness, biking, cardio, cours collectifs…
            </p>
          </div>
          <div class="text-center mt-3">
            <span class="btn btn-light btn-sm">Voir les activités</span>
          </div>
        </div>
      </a>
    </div>

    <!-- 2. Sports de compétition -->
    <div class="col-sm-6 col-md-4">
      <a href="sportsCompetition.php" class="text-decoration-none">
        <div class="border rounded h-100 d-flex flex-column justify-content-between text-white"
             style="
               background:
                 linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
                 url('../assets/images/<?= $imageMap['competition'] ?>') no-repeat center center;
               background-size: cover;
               padding: 1rem;
             ">
          <div>
            <h5 class="fw-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.6);">
              Sports de compétition
            </h5>
            <p style="font-size: 0.95rem;">
              Accède aux différentes spécialités sportives compétitives :  
              basketball, football, rugby, tennis, natation, plongeon…
            </p>
          </div>
          <div class="text-center mt-3">
            <span class="btn btn-light btn-sm">Voir les sports</span>
          </div>
        </div>
      </a>
    </div>

    <!-- 3. Salles de sport Omnes -->
    <div class="col-sm-6 col-md-4">
      <a href="salleSportOmnes.php" class="text-decoration-none">
        <div class="border rounded h-100 d-flex flex-column justify-content-between text-white"
             style="
               background:
                 linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
                 url('../assets/images/<?= $imageMap['salle'] ?>') no-repeat center center;
               background-size: cover;
               padding: 1rem;
             ">
          <div>
            <h5 class="fw-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.6);">
              Salles de sport Omnes
            </h5>
            <p style="font-size: 0.95rem;">
              Découvre les règles d’utilisation, les horaires, et les coordonnées  
              de toutes les salles de sport Omnes.
            </p>
          </div>
          <div class="text-center mt-3">
            <span class="btn btn-light btn-sm">Voir les salles</span>
          </div>
        </div>
      </a>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
