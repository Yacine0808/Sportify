<?php
// public/deleteCoach.php

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
    $stmtImg = $pdo->prepare("SELECT Image_Coach, CV_Coach FROM `personel/coach` WHERE ID_Coach = ?");
    $stmtImg->execute([$id]);
    $row = $stmtImg->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        if (!empty($row['Image_Coach']) && file_exists(__DIR__ . '/' . $row['Image_Coach'])) {
            unlink(__DIR__ . '/' . $row['Image_Coach']);
        }
        if (!empty($row['CV_Coach']) && file_exists(__DIR__ . '/' . $row['CV_Coach'])) {
            unlink(__DIR__ . '/' . $row['CV_Coach']);
        }
    }

    $del = $pdo->prepare("DELETE FROM `personel/coach` WHERE ID_Coach = ?");
    $del->execute([$id]);
}

header('Location: manageCoachs.php');
exit;
