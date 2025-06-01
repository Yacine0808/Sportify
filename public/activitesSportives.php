<?php
// public/activitesSportives.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session
session_start();

// 3) Inclusion du header (navbar + CSS Bootstrap)
include __DIR__ . '/../includes/header.php';

// 4) Connexion à la BDD (PDO)
require __DIR__ . '/../includes/db.php';

// 5) Liste des activités “non compétitives”
$activites = [
  'Musculation',
  'Fitness',
  'Biking',
  'Cardio-Training',
  'Cours Collectifs'
];

// 6) Récupérer les coachs par activité
$coachsByActivite = [];
foreach ($activites as $act) {
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
        c.CV_Coach
      FROM `personel/coach` AS c
      WHERE c.Specialty_Coach = ?
      ORDER BY c.Name_Coach, c.LName_Coach
    ");
    $stmt->execute([$act]);
    $coachsByActivite[$act] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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

// 8) Fonction pour convertir un index de jour en texte
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
  <h1 class="mb-4">Activités sportives</h1>

  <?php foreach ($activites as $activite): ?>
    <section class="mb-5">
      <h2 class="mb-3"><?= htmlspecialchars($activite) ?></h2>

      <?php if (empty($coachsByActivite[$activite])): ?>
        <p>
          Aucun coach n’est actuellement rattaché à « <?= htmlspecialchars($activite) ?> ».
        </p>
      <?php else: ?>
        <div class="row gy-4">
          <?php foreach ($coachsByActivite[$activite] as $coach):
            // 8.a) Disponibilités du coach
            $dispos = getDispo((int)$coach['ID_Coach'], $pdo);

            // 8.b) URL de la photo du coach
            $photoPathBDD = $coach['Image_Coach'];
            if (empty($photoPathBDD) || ! file_exists(__DIR__ . '/../' . $photoPathBDD)) {
                // Si pas d'image, fallback vers une image par défaut
                $photoURL = 'assets/images/default_coach.png';
            } else {
                // Chemin relatif depuis public/
                $photoURL = '../' . $photoPathBDD;
            }

            // 8.c) URL “Prendre un RDV” (redirige vers prendreRDV.php?coach_id=…)
            $coachId       = (int)$coach['ID_Coach'];
            $urlPrendreRdv = "prendreRDV.php?coach_id={$coachId}";

            // 8.d) URL “Télécharger le CV XML” 
            $cvURL = '';
            if (!empty($coach['CV_Coach'])) {
                $cvPathBDD = $coach['CV_Coach'];
                if (file_exists(__DIR__ . '/' . $cvPathBDD)) {
                    $cvURL = $cvPathBDD;
                }
            }

            // 8.e) Adresse mail du coach pour le bouton "Contacter"
            $mailCoach = $coach['EMail_Coach'];

            // 8.f) Lien audio/vidéo Chat : si les champs existent et ne sont pas vides
            $videoLink = !empty($coach['Video_Chat_CoACH']) ? $coach['Video_Chat_CoACH'] : '';
            $audioLink = !empty($coach['Audio_Chat_CoACH']) ? $coach['Audio_Chat_CoACH'] : '';
            // 8.g) Numéro de téléphone ou lien d'appel si Chat_Coach = 1
            // (on suppose que Chat_Coach=1 signifie "numéro de téléphone" ou un lien).  
            // Si ce n’est pas un numéro mais un booléen, retirez cette partie ou adaptez-la.
            $appelCoach = ($coach['Chat_CoACH'] ?? 0) ? 'tel:+33123456789' : '';
          ?>
            <div class="col-md-6">
              <div class="card h-100">
                <div class="row g-0">
                  <div class="col-4">
                    <img 
                      src="<?= htmlspecialchars($photoURL) ?>" 
                      class="img-fluid rounded-start" 
                      alt="Photo de <?= htmlspecialchars($coach['Name_Coach'] . ' ' . $coach['LName_Coach']) ?>"
                      style="object-fit: cover; height: 100%;"
                      onerror="this.src='assets/images/default_coach.png';"
                    >
                  </div>
                  <div class="col-8">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title">
                        <?= htmlspecialchars($coach['Name_Coach'] . ' ' . $coach['LName_Coach']) ?>
                      </h5>

                      <p class="mb-1">
                      
                        <strong>E-mail :</strong> <?= htmlspecialchars($mailCoach) ?>
                      </p>

                      <!-- Disponibilités -->
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

                      <!-- Boutons “Télécharger le CV”, “Prendre un RDV”, “Contacter”, “Appel”, “Video”, “Audio” -->
                      <div class="mt-auto d-flex justify-content-start align-items-center gap-2">
                        <?php if (!empty($cvURL)): ?>
                          <a 
                            href="<?= htmlspecialchars($cvURL) ?>" 
                            class="btn btn-sm btn-outline-secondary"
                            target="_blank"
                          >
                            Télécharger le CV XML
                          </a>
                        <?php endif; ?>

                        <a 
                          href="<?= htmlspecialchars($urlPrendreRdv) ?>" 
                          class="btn btn-sm btn-primary"
                        >
                          Prendre un RDV
                        </a>

                        <?php if (!empty($mailCoach)): ?>
                          <a 
                            href="mailto:<?= htmlspecialchars($mailCoach) ?>" 
                            class="btn btn-sm btn-outline-success"
                          >
                            <span class="me-1">📧</span> Mail
                          </a>
                        <?php endif; ?>

                        <?php if (!empty($appelCoach)): ?>
                          <!-- 📞 devant un lien d'appel (ici un exemple de numéro) -->
                          <a 
                            href="<?= htmlspecialchars($appelCoach) ?>" 
                            class="btn btn-sm btn-outline-info"
                          >
                            📞 Appel
                          </a>
                        <?php endif; ?>

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
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
    <hr>
  <?php endforeach; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
