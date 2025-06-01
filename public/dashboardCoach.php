<?php
// public/dashboardCoach.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Protection : seuls les coaches accèdent ici
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'coach') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';

// Récupérer l’ID du coach depuis la session
$coachId = $_SESSION['coach_id'];

// Recharger les informations du coach en base (en cas de mise à jour)
$stmt = $pdo->prepare("
  SELECT Name_Coach, LName_Coach, EMail_Coach, Image_Coach, Specialty_Coach
    FROM `personel/coach`
   WHERE ID_Coach = ?
");
$stmt->execute([$coachId]);
$coach = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l’enregistrement n’existe plus, on déconnecte
if (!$coach) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Déterminer le chemin réel de l’image
$photoPath = htmlspecialchars($coach['Image_Coach']);
?>

<main class="container py-5" style="max-width:600px;">
  <h2 class="mb-4">Tableau de bord Coach</h2>

  <div class="card mb-4">
    <div class="row g-0">
      <div class="col-md-4 text-center p-3">
        <!-- Affichage de la photo du coach -->
        <?php if (!empty($photoPath) && file_exists(__DIR__ . '/../' . $photoPath)): ?>
          <img
            src="../<?= $photoPath ?>"
            class="img-fluid rounded-circle"
            alt="Photo de <?= htmlspecialchars($coach['Name_Coach']) ?>"
            style="max-width:150px; max-height:150px;"
          >
        <?php else: ?>
          <div
            class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
            style="width:150px; height:150px; font-size:1.2rem;"
          >
            Pas de photo
          </div>
        <?php endif; ?>
      </div>
      <div class="col-md-8">
        <div class="card-body">
          <h5 class="card-title">
            <?= htmlspecialchars($coach['Name_Coach']) ?> <?= htmlspecialchars($coach['LName_Coach']) ?>
          </h5>
          <p class="card-text">
            <strong>Email :</strong> <?= htmlspecialchars($coach['EMail_Coach']) ?>
          </p>
          <p class="card-text">
            <strong>Spécialité :</strong> <?= htmlspecialchars($coach['Specialty_Coach']) ?>
          </p>
        </div>
      </div>
    </div>
  </div>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
