<?php
// Affichage des erreurs en développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session et connexion PDO
session_start();
require __DIR__ . '/../includes/db.php';

// Inclusion de l’en-tête (meta, CSS, nav, ouverture <main>)
include __DIR__ . '/../includes/header.php';
?>

  <!-- Welcome Section -->
  <section id="welcome" class="container text-center py-5">
    <h1 class="display-5">Bienvenue sur Sportify</h1>
    <p class="lead">Découvrez et réservez vos activités sportives préférées avec nos coachs et experts.</p>
  </section>

  <!-- Évènement de la semaine -->
  <section id="event-week" class="container bg-primary bg-opacity-10 rounded py-4 mb-5">
    <h2>Évènement de la semaine</h2>
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="card-title">Porte ouverte Sportify</h3>
        <p class="card-text">Rencontrez nos coachs et découvrez nos installations le samedi 30 mai à 14h.</p>
      </div>
    </div>
  </section>

  <!-- Carrousel Section -->
  <section id="specialists" class="container mb-5">
    <h2 class="text-center mb-4">Nos Spécialistes</h2>
    <div id="specialistsCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="../assets/images/carrousel/AntoineGAUTIER.png" class="d-block w-100" alt="Football" loading="lazy">
        </div>
        <div class="carousel-item">
          <img src="../assets/images/carrousel/EmmanuelTITOT.png" class="d-block w-100" alt="Basketball" loading="lazy">
        </div>
        <div class="carousel-item">
          <img src="../assets/images/carrousel/EvanDUPONT.png" class="d-block w-100" alt="Rugby" loading="lazy">
        </div>
        <div class="carousel-item">
          <img src="../assets/images/carrousel/JulesFRANCOIS.png" class="d-block w-100" alt="Tennis" loading="lazy">
        </div>
        <div class="carousel-item">
          <img src="../assets/images/carrousel/VictorHENRY.png" class="d-block w-100" alt="Natation" loading="lazy">
        </div>       
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#specialistsCarousel" data-bs-slide="prev" aria-label="Précédent">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#specialistsCarousel" data-bs-slide="next" aria-label="Suivant">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>
  </section>

<?php
// Inclusion du footer (fermeture </main>, footer, JS)
include __DIR__ . '/../includes/footer.php';
?>
