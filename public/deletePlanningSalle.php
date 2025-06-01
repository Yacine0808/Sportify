<?php
// public/deletePlanningSalle.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $del = $pdo->prepare("DELETE FROM `planning_salle` WHERE ID_Planning = ?");
    $del->execute([$id]);
}

header('Location: gestionSalles.php');
exit;
