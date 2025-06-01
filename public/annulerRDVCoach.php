<?php
// public/annulerRDVCoach.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session (pour récupérer l’ID de l’utilisateur connecté)
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = (int) $_SESSION['user_id'];

// 3) Connexion à la base de données
require __DIR__ . '/../includes/db.php';

// 4) Récupérer l’ID du RDV à annuler (GET parameter “id”)
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    // Si aucun ID valide, on redirige avec un message d’erreur
    header('Location: rendezvous.php?error=rdv_invalide');
    exit;
}
$rdvId = (int) $_GET['id'];

// 5) Vérifier que ce RDV appartient bien à l’utilisateur connecté et qu’il est encore actif
$stmtCheck = $pdo->prepare("
    SELECT ID_RDV, ID_Coach, Statut_RDV
      FROM `rdv`
     WHERE ID_RDV = :rid
       AND User_id = :uid
");
$stmtCheck->execute([
    ':rid' => $rdvId,
    ':uid' => $userId
]);
$rdvRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$rdvRow) {
    // Soit ce RDV n’existe pas, soit il n’appartient pas à cet utilisateur
    header('Location: rendezvous.php?error=rdv_introuvable');
    exit;
}

// 6) Si le RDV est déjà annulé (Statut_RDV = 0), on redirige sans rien faire
if ((int)$rdvRow['Statut_RDV'] === 0) {
    header('Location: rendezvous.php?notice=deja_annule');
    exit;
}

// 7) Mettre à jour Statut_RDV = 0
$stmtUpdate = $pdo->prepare("
    UPDATE `rdv`
       SET Statut_RDV = 0
     WHERE ID_RDV = :rid
       AND User_id = :uid
");
$stmtUpdate->execute([
    ':rid' => $rdvId,
    ':uid' => $userId
]);

// 8) Redirection finale
header('Location: rendezvous.php?success=coach_annule');
exit;
