
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Protection : seuls les admins accèdent ici
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

require __DIR__ . '/../includes/db.php';  
include __DIR__ . '/../includes/header.php';

// On récupère l'ID de l'admin en session
$adminId = $_SESSION['admin_id'];

// Chargement des infos de l'admin
$stmt = $pdo->prepare("
  SELECT ID_Admin, Name_Admin, LName_Admin, Image_Admin, EMail_Admin, Code_Admin
    FROM admin
   WHERE ID_Admin = ?
");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

// Construction du chemin complet vers l’image
// Si Photo_Admin stocke "Images/Sportify/Zeyna.jpeg", on fait :
$photoPath = '../assets/images/Sportify/' . basename($admin['Image_Admin']);
?>

<main class="container py-5" style="max-width: 600px;">
  <h2 class="mb-4">Tableau de bord Admin</h2>

  <div class="card mb-4" style="max-width: 400px;">
    <div class="row g-0">
      <div class="col-md-4">
        <!-- Affichage de la photo -->
        <img src="<?= htmlspecialchars($photoPath) ?>"
             class="img-fluid rounded-start"
             alt="Photo de <?= htmlspecialchars($admin['Name_Admin']) ?>">
      </div>
      <div class="col-md-8">
        <div class="card-body">
          <h5 class="card-title">
            <?= htmlspecialchars($admin['Name_Admin'] . ' ' . $admin['LName_Admin']) ?>
          </h5>
          <p class="card-text"><strong>Email :</strong> <?= htmlspecialchars($admin['EMail_Admin']) ?></p>
           <p class="card-text"><strong>ID Admin :</strong> <?= htmlspecialchars($admin['ID_Admin']) ?></p>
        </div>
      </div>
    </div>
  </div>



</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
