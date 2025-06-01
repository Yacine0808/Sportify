<?php
// public/sportDetail.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session (pour adapter le menu si besoin)
session_start();

// 3) Inclusion du header (navbar + CSS Bootstrap)
//    Attention : header.php ne doit PAS contenir de redirection ou meta-refresh
include __DIR__ . '/../includes/header.php';

// 4) Connexion à la base de données (PDO)
require __DIR__ . '/../includes/db.php';

// 5) Vérifier que la discipline est passée en paramètre
if (!isset($_GET['discipline']) || trim($_GET['discipline']) === '') {
    echo "<main class='container py-5'>
            <div class='alert alert-danger'>
              Discipline non précisée.
            </div>
          </main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$disc = trim($_GET['discipline']);

// 6) Préparer et exécuter la requête pour récupérer les coachs de cette discipline
$stmt = $pdo->prepare("
  SELECT
    c.ID_Coach,
    c.Name_Coach,
    c.LName_Coach,
    c.Image_Coach,
    c.EMail_Coach,
    c.Chat_Coach,
    c.Video_Chat_CoACH,
    c.Audio_Chat_CoACH,
    c.CV_Coach,
    c.Specialty_Coach
  FROM `personel/coach` AS c
  WHERE c.Specialty_Coach = ?
  ORDER BY c.Name_Coach, c.LName_Coach
");
$stmt->execute([$disc]);
$coachs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 7) Fonction pour récupérer les disponibilités d’un coach
function getDispo(int $coachId, PDO $pdo): array {
    $s = $pdo->prepare("
      SELECT DayOfWeek, StartTime, EndTime
        FROM `planning_coach`
       WHERE ID_Coach = ?
       ORDER BY DayOfWeek, StartTime
    ");
    $s->execute([$coachId]);
    return $s->fetchAll(PDO::FETCH_ASSOC);
}

// 8) Fonction pour convertir le chiffre du jour en texte
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
?>

<main class="container py-5">
  <h1 class="mb-4"><?= htmlspecialchars($disc) ?></h1>

  <?php if (empty($coachs)): ?>
    <div class="alert alert-info">
      Aucun coach trouvé pour « <?= htmlspecialchars($disc) ?> ».
    </div>
  <?php else: ?>
    <div class="row gy-4">
      <?php foreach ($coachs as $coach):
        // 7.a) Récupérer les créneaux pour ce coach
        $dispos = getDispo((int)$coach['ID_Coach'], $pdo);

        // 7.b) Déterminer l’URL de la photo (fallback si vide)
        $urlImageCoach = '/assets/images/default_coach.png';
        if (!empty($coach['Image_Coach'])) {
            $cheminPhysique = __DIR__ . '/' . $coach['Image_Coach'];
            if (file_exists($cheminPhysique)) {
                // On ajoute un slash devant pour que l'URL soit absolue depuis public/
                $urlImageCoach = '/' . ltrim($coach['Image_Coach'], '/');
            }
        }

        // 7.c) Construire l’URL “Prendre un RDV” en passant coach_id + discipline
        $coachId       = (int)$coach['ID_Coach'];
        $disciplineUrl = urlencode($disc);
        $urlPrendreRdv = "prendreRDV.php?coach_id={$coachId}&discipline={$disciplineUrl}";

        // 7.d) Construire l’URL “Télécharger le CV XML”
        $cvURL = '';
        if (!empty($coach['CV_Coach'])) {
            // En base, CV_Coach = "xml/Coach_15_CV.xml" par exemple
            $cvPathBDD       = $coach['CV_Coach'];
            $cheminPhysiqueCV = __DIR__ . '/' . $cvPathBDD;
            if (file_exists($cheminPhysiqueCV)) {
                $cvURL = '/' . ltrim($cvPathBDD, '/');
            }
        }
      ?>

        <div class="col-md-6">
          <div class="card h-100 shadow-sm">
            <div class="row g-0">
              <div class="col-4">
                <img
                  src="<?= htmlspecialchars($urlImageCoach) ?>"
                  class="img-fluid rounded-start"
                  alt="Photo de <?= htmlspecialchars($coach['Name_Coach'] . ' ' . $coach['LName_Coach']) ?>"
                  style="object-fit: cover; height: 100%; width: 100%;"
                >
              </div>
              <div class="col-8">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title">
                    <?= htmlspecialchars($coach['Name_Coach'] . ' ' . $coach['LName_Coach']) ?>
                  </h5>
                  <p class="mb-1">
                    <strong>E-mail :</strong>
                    <?= htmlspecialchars($coach['EMail_Coach']) ?>
                  </p>

                  <div class="mb-2">
                    <strong>Disponibilités :</strong>
                    <?php if (empty($dispos)): ?>
                      <p class="mb-0">Aucun créneau défini.</p>
                    <?php else: ?>
                      <ul class="ps-3 mb-0">
                        <?php foreach ($dispos as $d): ?>
                          <li>
                            <?= jourTexte((int)$d['DayOfWeek']) ?> :
                            <?= htmlspecialchars(substr($d['StartTime'], 0, 5)) ?> –
                            <?= htmlspecialchars(substr($d['EndTime'],   0, 5)) ?>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </div>

                  <!-- 7.e) Boutons “Télécharger le CV”, “Prendre un RDV” & “Contacter” -->
                  <div class="mt-auto d-flex justify-content-start align-items-center gap-2">
                    <?php if (!empty($cvURL)): ?>
                      <a
                        href="<?= htmlspecialchars($cvURL) ?>"
                        class="btn btn-sm btn-outline-secondary"
                        target="_blank"
                      >
                        Télécharger le CV (XML)
                      </a>
                    <?php endif; ?>

                    <a
                      href="<?= htmlspecialchars($urlPrendreRdv) ?>"
                      class="btn btn-sm btn-primary"
                    >
                      Prendre un RDV
                    </a>

                    <a
                      href="mailto:<?= htmlspecialchars($coach['EMail_Coach']) ?>"
                      class="btn btn-sm btn-outline-success"
                    >
                      <span class="me-1">📧</span> Mail
                    </a>
		   <?php if (!empty($videoLink)): ?>
                     <!-- 🎥 lien vers visioconférence -->
                     <a 
                      href="<?= htmlspecialchars($videoLink) ?>" 
                      class="btn btn-sm btn-outline-warning"
                      target="_blank"
                     >
                       🎥 Vidéo
                     </a>
                   <?php endif; ?>

                   <?php if (!empty($audioLink)): ?>
                     <!-- 🎙️ lien vers audio -->
                     <a 
                       href="<?= htmlspecialchars($audioLink) ?>" 
                       class="btn btn-sm btn-outline-dark"
                       target="_blank"
                     >
                       🎙️ Audio
                     </a>
                    <?php endif; ?>
                  </div>
                </div><!-- .card-body -->
              </div><!-- .col-8 -->
            </div><!-- .row -->
          </div><!-- .card -->
        </div><!-- .col-md-6 -->

      <?php endforeach; ?>
    </div><!-- .row -->
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
