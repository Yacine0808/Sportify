<?php
// public/rendezvous.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Si aucun rôle en session, on redirige vers la connexion
if (empty($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];

include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/db.php';

// ------------------------------------------
// 2) Partie CLIENT : afficher ses propres RDV
// ------------------------------------------
if ($role === 'client') {
    // On suppose que, à la connexion client, vous avez fait :
    //    $_SESSION['user_id'] = ID_User;
    if (empty($_SESSION['user_id'])) {
        header('Location: logout.php');
        exit;
    }
    $userId = (int) $_SESSION['user_id'];

    // 2.a) Requête pour récupérer les rendez-vous “coach” du client
    $sqlCoach = "
        SELECT 
          r.ID_RDV,
          r.Date_RDV,
          r.Statut_RDV,
          c.ID_Coach,
          CONCAT(c.Name_Coach, ' ', c.LName_Coach) AS Nom_Prenom_Coach,
          c.Specialty_Coach
        FROM `rdv` AS r
        INNER JOIN `personel/coach` AS c 
          ON r.ID_Coach = c.ID_Coach
        WHERE r.User_id = :uid
        ORDER BY r.Date_RDV ASC
    ";
    $stmtCoach = $pdo->prepare($sqlCoach);
    $stmtCoach->execute([':uid' => $userId]);
    $coachAppointments = $stmtCoach->fetchAll(PDO::FETCH_ASSOC);

    // 2.b) Requête pour récupérer les rendez-vous “salle” du client
    $sqlSalle = "
        SELECT 
          rs.ID_Rdv,
          rs.DayOfWeek,
          rs.StartTime,
          rs.ID_Salle,
          rs.CreatedAt
        FROM `rdv_salle` AS rs
        WHERE rs.User_id = :uid
        ORDER BY rs.DayOfWeek, rs.StartTime
    ";
    $stmtSalle = $pdo->prepare($sqlSalle);
    $stmtSalle->execute([':uid' => $userId]);
    $salleAppointments = $stmtSalle->fetchAll(PDO::FETCH_ASSOC);

    // 2.c) Fonction utilitaire pour convertir DayOfWeek -> texte
    function jourTexte(int $d): string {
        $map = [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi'
        ];
        return $map[$d] ?? '';
    }

    $now = new DateTime(); // pour comparer avec Date_RDV
    ?>

    <main class="container py-5">
      <h1 class="mb-4">Mes rendez-vous</h1>

      <!-- Section : Rendez-vous coach -->
      <section class="mb-5">
        <h2 class="h4 mb-3">Cours particuliers avec un coach</h2>

        <?php if (empty($coachAppointments)): ?>
          <div class="alert alert-info">
            Vous n’avez aucun rendez-vous coach pour le moment.
          </div>
        <?php else: ?>
          <div class="row gy-4">
            <?php foreach ($coachAppointments as $rdv):
              // Conversion de la date pour affichage
              $dt = new DateTime($rdv['Date_RDV']);
              $dateTexte = $dt->format('d/m/Y \à H:i');
              // Statut : 1 = confirmé, 0 = annulé
              $statut = ((int)$rdv['Statut_RDV'] === 1) ? 'Confirmé' : 'Annulé';

              // Déterminer si le RDV est passé
              $isPast = ($dt < $now && (int)$rdv['Statut_RDV'] === 1);
            ?>
              <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2"><?= htmlspecialchars($rdv['Nom_Prenom_Coach']) ?></h5>
                    <p class="mb-1"><strong>Spécialité :</strong> <?= htmlspecialchars($rdv['Specialty_Coach']) ?></p>
                    <p class="mb-1"><strong>Date & Heure :</strong> <?= htmlspecialchars($dateTexte) ?></p>
                    <p class="mb-1"><strong>Statut :</strong> <?= htmlspecialchars($statut) ?></p>
                    <div class="mt-auto">
                      <?php if ($isPast): ?>
                        <span class="badge bg-secondary">Passé</span>
                      <?php elseif ((int)$rdv['Statut_RDV'] === 1): ?>
                        <a 
                          href="annulerRDVCoach.php?id=<?= (int)$rdv['ID_RDV'] ?>" 
                          class="btn btn-sm btn-danger"
                          onclick="return confirm('Confirmer l’annulation de ce rendez-vous ?');"
                        >
                          Annuler le RDV
                        </a>
                      <?php else: ?>
                        <span class="badge bg-secondary">Annulé</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <hr>

      <!-- Section : Rendez-vous salle -->
      <section>
        <h2 class="h4 mb-3">Visite de la salle de sport</h2>

        <?php if (empty($salleAppointments)): ?>
          <div class="alert alert-info">
            Vous n’avez aucun rendez-vous à la salle pour le moment.
          </div>
        <?php else: ?>
          <div class="row gy-4">
            <?php foreach ($salleAppointments as $rs):
              // Conversion du jour de la semaine et de l’heure
              $jour = jourTexte((int)$rs['DayOfWeek']);
              // On affiche une plage de 1 h à partir de StartTime
              $heureDebut = substr($rs['StartTime'], 0, 5);
              $heureFin = date('H:i', strtotime($rs['StartTime'] . ' +1 hour'));
              $created = (new DateTime($rs['CreatedAt']))->format('d/m/Y H:i');

              // Déterminer si le créneau est passé
              // On reconstruit une DateTime pour le jour et heure de la semaine
              $dSalle = new DateTime(date('Y') . '-' . date('m') . '-' . date('d') . ' ' . $rs['StartTime']); 
              $isPastSalle = (strtotime($rs['StartTime']) < strtotime((new DateTime())->format('H:i'))) && ((int)$rs['DayOfWeek'] === (int)(new DateTime())->format('w'));
            ?>
              <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2">Salle #<?= (int)$rs['ID_Salle'] ?></h5>
                    <p class="mb-1"><strong>Jour :</strong> <?= htmlspecialchars($jour) ?></p>
                    <p class="mb-1">
                      <strong>Créneau :</strong> 
                      <?= htmlspecialchars($heureDebut) ?> – <?= htmlspecialchars($heureFin) ?>
                    </p>
                    <p class="mb-1"><strong>Réservé le :</strong> <?= htmlspecialchars($created) ?></p>
                    <div class="mt-auto">
                      <?php if ($isPastSalle): ?>
                        <span class="badge bg-secondary">Passé</span>
                      <?php else: ?>
                        <a 
                          href="annulerRDVSalle.php?id=<?= (int)$rs['ID_Rdv'] ?>" 
                          class="btn btn-sm btn-danger"
                          onclick="return confirm('Confirmer l’annulation de la visite ?');"
                        >
                          Annuler le RDV Salle
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>

    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// ---------------------------------------
// 3) Partie COACH : afficher RDV clients
// ---------------------------------------
if ($role === 'coach') {
    // On suppose que, à la connexion coach, vous avez fait :
    //    $_SESSION['coach_id'] = ID_Coach;
    if (empty($_SESSION['coach_id'])) {
        header('Location: logout.php');
        exit;
    }
    $coachId = (int) $_SESSION['coach_id'];

    // 3.a) Requête pour récupérer les rendez-vous
    $sql = "
      SELECT
        r.ID_RDV,
        r.Date_RDV,
        r.Statut_RDV,
        u.ID_User,
        CONCAT(u.Name_User, ' ', u.LName_User) AS NomPrenomClient,
        u.EMail_User
      FROM `rdv` AS r
      INNER JOIN `client` AS u 
        ON r.User_id = u.ID_User
      WHERE r.ID_Coach = :cid
      ORDER BY r.Date_RDV ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cid' => $coachId]);
    $myAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3.b) Fonction utilitaire pour convertir DayOfWeek -> texte 
    // (inutile ici, mais conservée pour cohérence)
    function jourTexte(int $d): string {
        $map = [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi'
        ];
        return $map[$d] ?? '';
    }

    $nowCoach = new DateTime(); // pour comparer avec Date_RDV
    ?>

    <main class="container py-5">
      <h1 class="mb-4">Mes rendez-vous (clients)</h1>

      <?php if (empty($myAppointments)): ?>
        <div class="alert alert-info">
          Vous n’avez aucun rendez-vous client pour le moment.
        </div>
      <?php else: ?>
        <div class="row gy-4">
          <?php foreach ($myAppointments as $rdv):
            // Conversion de la date pour affichage
            $dt = new DateTime($rdv['Date_RDV']);
            $dateTexte = $dt->format('d/m/Y \à H:i');
            // Statut : 1 = confirmé, 0 = annulé
            $statut = ((int)$rdv['Statut_RDV'] === 1) ? 'Confirmé' : 'Annulé';

            // Déterminer si le RDV est passé
            $isPastCoach = ($dt < $nowCoach && (int)$rdv['Statut_RDV'] === 1);
          ?>
            <div class="col-md-6">
              <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-2">
                    Client : <?= htmlspecialchars($rdv['NomPrenomClient']) ?>
                  </h5>
                  <p class="mb-1"><strong>E-mail :</strong> <?= htmlspecialchars($rdv['EMail_User']) ?></p>
                  <p class="mb-1"><strong>Date & Heure :</strong> <?= htmlspecialchars($dateTexte) ?></p>
                  <p class="mb-1"><strong>Statut :</strong> <?= htmlspecialchars($statut) ?></p>
                  <div class="mt-auto">
                    <?php if ($isPastCoach): ?>
                      <span class="badge bg-secondary">Passé</span>
                    <?php elseif ((int)$rdv['Statut_RDV'] === 1): ?>
                      <a 
                        href="annulerRDVClientParCoach.php?id=<?= (int)$rdv['ID_RDV'] ?>" 
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Confirmer l’annulation de ce rendez-vous ?');"
                      >
                        Annuler le RDV
                      </a>
                    <?php else: ?>
                      <span class="badge bg-secondary">Annulé</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>

    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// ----------------------------------
// 4) Autres rôles (admin, visiteur…)
// ----------------------------------
header('Location: login.php');
exit;
