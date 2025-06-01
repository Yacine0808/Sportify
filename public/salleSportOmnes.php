<?php
// public/salleSportOmnes.php

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

/*
  5) Récupérer la liste de toutes les salles
*/
$salles = $pdo
    ->query("
      SELECT ID_Salle, Numero_Salle, EMail_Salle, Telephone_Salle 
        FROM `salle de sport` 
       ORDER BY ID_Salle ASC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container py-5">

  <!-- ================================
       Partie 1 : Liste des salles
       ================================ -->
  <h1 class="mb-4 text-center">Salles de sport Omnes</h1>

  <?php if (empty($salles)): ?>
    <div class="alert alert-info text-center">
      Aucune salle de sport n’est disponible pour le moment.
    </div>
  <?php else: ?>
    <div class="row gy-4">
      <?php foreach ($salles as $salle):
        $idSalle = (int) $salle['ID_Salle'];
        $numero  = htmlspecialchars($salle['Numero_Salle']);
        $email   = htmlspecialchars($salle['EMail_Salle']);
        $tel     = htmlspecialchars($salle['Telephone_Salle']);
      ?>
        <div class="col-sm-12 col-md-6">
          <!-- Encadré arrondi + ombre légère -->
          <div class="border rounded-3 shadow-sm p-4 d-flex flex-column justify-content-between" 
               style="background-color: #f8f9fa; height: 100%;">
            
            <!-- Coordonnées de la salle -->
            <div>
              <h2 class="h5 mb-2">Salle : <?= $numero ?></h2>
              <p class="mb-1"><strong>Téléphone :</strong> <?= $tel ?></p>
              <p class="mb-3">
                <strong>Email :</strong> 
                <a href="mailto:<?= $email ?>"><?= $email ?></a>
              </p>
            </div>

            <!-- Bouton “Voir les créneaux” centré -->
            <div class="text-center mt-auto">
              <a 
                href="creneauxSalle.php?salle_id=<?= $idSalle ?>" 
                class="btn btn-primary btn-lg rounded-pill px-4"
              >
                Voir les créneaux
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- ================================================================
       Partie 2 :	 Sections d’informations générales 
       ================================================================ -->
  <hr class="my-5">

  <section class="text-center">
    <div class="row justify-content-center gx-4 gy-5">
      
      <div class="col-md-4">
        <h2 class="h5 mb-3">Règles sur l’utilisation des machines</h2>
        <p class="text-muted">
          • Nettoyer chaque équipement avant et après utilisation.<br>
          • Porter une tenue de sport et des chaussures adaptées.<br>
          • Respecter les autres usagers et les zones de circulation.<br>
          • Ne pas laisser d’objets personnels sur les machines.
        </p>
      </div>
      
      <div class="col-md-4">
        <h2 class="h5 mb-3">Nouveaux clients</h2>
        <p class="text-muted">
          • Présentez-vous à l’accueil pour finaliser votre inscription.<br>
          • Bénéficiez d’une visite guidée des installations.<br>
          • Recevez votre badge d’accès et le plan d’orientation de la salle.<br>
          • Profitez d’un coaching d’introduction offert (30 min).
        </p>
      </div>
      
      <div class="col-md-4">
        <h2 class="h5 mb-3">Alimentation & nutrition</h2>
        <p class="text-muted">
          • Privilégiez protéines maigres, légumes frais et fruits.<br>
          • Hydratez-vous régulièrement : au moins 1,5 L d’eau/jour.<br>
          • Évitez les sucres rapides avant un entraînement intense.<br>
          • Consultez notre diététicien pour un plan nutritionnel.
        </p>
      </div>
      
    </div>
  </section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
