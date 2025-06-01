<?php
// public/annulerRDVSalle.php

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

// 4) Récupérer l’ID du RDV de salle à annuler (GET parameter “id”)
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: rendezvous.php?error=rdvsalle_invalide');
    exit;
}
$rdvSalleId = (int) $_GET['id'];

// 5) Vérifier que ce RDV de salle appartient bien à l’utilisateur
$stmtCheck = $pdo->prepare("
    SELECT ID_Rdv
      FROM `rdv_salle`
     WHERE ID_Rdv = :rsid
       AND User_id = :uid
");
$stmtCheck->execute([
    ':rsid' => $rdvSalleId,
    ':uid'  => $userId
]);
$rsRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$rsRow) {
    // Soit ce RDV de salle n’existe pas, soit il n’appartient pas à cet utilisateur
    header('Location: rendezvous.php?error=rdvsalle_introuvable');
    exit;
}

// 6) Supprimer la ligne correspondante dans rdv_salle
$stmtDelete = $pdo->prepare("
    DELETE 
      FROM `rdv_salle`
     WHERE ID_Rdv = :rsid
       AND User_id = :uid
");
$stmtDelete->execute([
    ':rsid' => $rdvSalleId,
    ':uid'  => $userId
]);

// 7) Redirection finale
header('Location: rendezvous.php?success=salle_annule');
exit;
