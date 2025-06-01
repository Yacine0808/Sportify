<?php
// public/confirmerRDVSalle.php

// 1) Mode développement : afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session
session_start();

// 3) Inclusion du header (navbar + Bootstrap) – sans redirection ni meta-refresh
include __DIR__ . '/../includes/header.php';

// 4) Connexion à la BDD (PDO)
require __DIR__ . '/../includes/db.php';

/*
  5) On s’attend à recevoir en GET :
     - salle_id  : l’ID de la salle
     - jour      : jour de la semaine (0=Dimanche … 6=Samedi)
     - heure     : horaire de début du créneau (format "HH:MM")
*/
if (
    !isset($_GET['salle_id'], $_GET['jour'], $_GET['heure']) ||
    !ctype_digit($_GET['salle_id']) ||
    !ctype_digit($_GET['jour'])
) {
    echo "<main class='container py-5'>
            <div class='alert alert-danger text-center'>
              Paramètres manquants ou invalides.
            </div>
          </main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$salleId = (int) $_GET['salle_id'];
$jour    = (int) $_GET['jour'];
$heure   = trim($_GET['heure']); // ex. "08:00"


// 7) Vérifier que l’utilisateur est connecté (on stocke son ID en session)
if (empty($_SESSION['user_id'])) {
    echo "<main class='container py-5'>
            <div class='alert alert-warning text-center'>
              Vous devez être connecté pour réserver un créneau.
            </div>
          </main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}
$userId = (int) $_SESSION['user_id'];

// 8) Vérifier que le créneau n’est pas déjà pris
try {
    $stmtExiste = $pdo->prepare("
      SELECT COUNT(*) 
        FROM `rdv_salle` 
       WHERE ID_Salle   = ?
         AND DayOfWeek  = ?
         AND StartTime  = ?
    ");
    $stmtExiste->execute([$salleId, $jour, $heure]);
    $count = (int) $stmtExiste->fetchColumn();
    if ($count > 0) {
        echo "<main class='container py-5'>
                <div class='alert alert-info text-center'>
                  Désolé, ce créneau est déjà réservé.
                </div>
                <div class='text-center mt-4'>
                  <a href='creneauxSalle.php?salle_id={$salleId}' class='btn btn-outline-primary'>
                    Retour aux créneaux
                  </a>
                </div>
              </main>";
        include __DIR__ . '/../includes/footer.php';
        exit;
    }
} catch (\Exception $e) {
    echo "<main class='container py-5'>
            <div class='alert alert-danger text-center'>
              Erreur BDD lors de la vérification du créneau : " . htmlspecialchars($e->getMessage()) . "
            </div>
          </main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// 9) Insérer le rendez-vous
try {
    $stmtIns = $pdo->prepare("
      INSERT INTO `rdv_salle`
        (ID_Salle, DayOfWeek, StartTime, User_id, CreatedAt)
      VALUES (?, ?, ?, ?, NOW())
    ");
    $stmtIns->execute([$salleId, $jour, $heure, $userId]);
} catch (\Exception $e) {
    echo "<main class='container py-5'>
            <div class='alert alert-danger text-center'>
              Impossible d’enregistrer le rendez-vous : " . htmlspecialchars($e->getMessage()) . "
            </div>
          </main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// 10) Confirmation et redirection simple
echo "<main class='container py-5'>
        <div class='alert alert-success text-center'>
          Votre créneau pour la salle #{$salleId} le jour {$jour} à {$heure} a bien été réservé !
        </div>
        <div class='text-center mt-4'>
          <a href='creneauxSalle.php?salle_id={$salleId}' class='btn btn-outline-primary'>
            Retour aux créneaux
          </a>
        </div>
      </main>";

include __DIR__ . '/../includes/footer.php';
