<?php
// public/login.php

// 1) Affichage des erreurs (dev seulement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session
session_start();

// 3) Connexion PDO et inclusion des fonctions d’authentification
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

// 4) Inclusion du header (nav, CSS…)
include __DIR__ . '/../includes/header.php';

// Variables initialisées
$error = '';
$login = '';
$pass  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On récupère ce que l’utilisateur a saisi
    $login = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    //
    // 5) Tentative de connexion “client” (mot de passe en clair)
    //
    $stmt = $pdo->prepare("
      SELECT ID_User, Password_User
        FROM client
       WHERE EMail_User = ?
       LIMIT 1
    ");
    $stmt->execute([ $login ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $pass === $user['Password_User']) {
        // Authentification OK pour un client
        $_SESSION['user_id'] = $user['ID_User'];
        $_SESSION['role']    = 'client';
        header('Location: dashboard.php');
        exit;
    }

    //
    // 6) Tentative de connexion “admin” (via is_admin)
    //
    $adminData = [];
    if (is_admin($login, $pass, $pdo, $adminData)) {
        $_SESSION['admin_id']   = $adminData['id'];
        $_SESSION['admin_name'] = $adminData['Name_Admin'];
        $_SESSION['role']       = 'admin';
        header('Location: dashboardAdmin.php');
        exit;
    }

    //
    // 7) Tentative de connexion “coach” 
    //
    $stmt2 = $pdo->prepare("
      SELECT ID_Coach, Name_Coach, LName_Coach, Image_Coach
        FROM `personel/coach`
       WHERE EMail_Coach = ?
         AND Code_Coach  = ?
       LIMIT 1
    ");
    $stmt2->execute([ $login, $pass ]);
    $coach = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($coach) {
        $_SESSION['coach_id']    = $coach['ID_Coach'];
        $_SESSION['coach_name']  = $coach['Name_Coach'];
        $_SESSION['coach_lname'] = $coach['LName_Coach'];
        $_SESSION['coach_image'] = $coach['Image_Coach'];
        $_SESSION['role']        = 'coach';
        header('Location: dashboardCoach.php');
        exit;
    }

    
    $error = 'Email ou mot de passe incorrect';
}
?>

<main class="container py-5" style="max-width:480px;">
  <h2 class="mb-4">Se connecter</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off" novalidate>
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input
        id="email"
        name="email"
        type="text"
        class="form-control"
        value="<?= htmlspecialchars($login) ?>"
      >
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Mot de passe</label>
      <input
        id="password"
        name="password"
        type="password"
        class="form-control"
        value=""
        autocomplete="new-password"
      >
    </div>
    <button type="submit" class="btn btn-primary w-100">Connexion</button>
  </form>

  <p class="text-center mt-3">
    Pas encore de compte client ? <a href="register.php">Inscrivez-vous ici</a>.
  </p>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
