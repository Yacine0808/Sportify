<?php
// public/deleteSalle.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Protection : seuls les admins
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../includes/db.php';

// 2) Récupération de l’ID passé en GET
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    // 3) Suppression de la salle (et de ses créneaux via ON DELETE CASCADE)
    $del = $pdo->prepare("DELETE FROM `salle de sport` WHERE ID_Salle = ?");
    $del->execute([$id]);
}

// 4) Rediriger vers la page de gestion des salles
header('Location: gestionSalles.php');
exit;
