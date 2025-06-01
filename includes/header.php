<?php
// includes/header.php

// 1) Démarre la session si elle n’est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sportify</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
  <header class="bg-light border-bottom mb-4">
    <div class="container d-flex align-items-center justify-content-between py-3">
      <a href="index.php">
        <img src="../assets/images/logoSportify.png" alt="Logo Sportify" height="50">
      </a>
      <nav>
        <ul class="nav">
          <?php
            // Récupère le rôle de l’utilisateur (ou vide si non connecté)
            $role = $_SESSION['role'] ?? '';
          ?>

          <!-- 1) Accueil  -->
          <li class="nav-item">
            <a
              class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"
              href="index.php"
            >Accueil</a>
          </li>

          <!-- 2) "Tout parcourir" (seulement pour les clients) -->
          <?php if ($role === 'client'): ?>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'toutParcourir.php' ? 'active' : '' ?>"
                href="toutParcourir.php"
              >Tout parcourir</a>
            </li>
          <?php endif; ?>

          <!-- 3) "Recherche" (visiteur et client uniquement) -->
          <?php if ($role === '' || $role === 'client'): ?>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'recherche.php' ? 'active' : '' ?>"
                href="recherche.php"
              >Recherche</a>
            </li>
          <?php endif; ?>

          <!-- 4) "Rendez-vous" (visiteur, client, coach ; pas admin) -->
          <?php if ($role !== 'admin'): ?>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'rendezvous.php' ? 'active' : '' ?>"
                href="rendezvous.php"
              >Rendez-vous</a>
            </li>
          <?php endif; ?>

          <!-- 5) "Chatroom" (coachs) -->
          <?php if ($role === 'coach'): ?>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'chatroom.php' ? 'active' : '' ?>"
                href="chatroom.php"
              >Chatroom</a>
            </li>
          <?php endif; ?>

          <!-- 6) Liens selon le rôle -->
          <?php if ($role === 'admin'): ?>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboardAdmin.php' ? 'active' : '' ?>"
                href="dashboardAdmin.php"
              >Espace Admin</a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'manageCoachs.php' ? 'active' : '' ?>"
                href="manageCoachs.php"
              >Gestion des coachs</a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'gestionSalles.php' ? 'active' : '' ?>"
                href="gestionSalles.php"
              >Gestion des salles</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Déconnexion</a>
            </li>

          <?php elseif ($role === 'coach'): ?>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboardCoach.php' ? 'active' : '' ?>"
                href="dashboardCoach.php"
              >Mon compte</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Déconnexion</a>
            </li>

          <?php elseif ($role === 'client'): ?>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"
                href="dashboard.php"
              >Compte</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Déconnexion</a>
            </li>

          <?php else: // visiteur non connecté ?>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : '' ?>"
                href="login.php"
              >Connexion</a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'register.php' ? 'active' : '' ?>"
                href="register.php"
              >S’inscrire</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>
  <main>
