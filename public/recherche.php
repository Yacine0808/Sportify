<?php
// public/recherche.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session (pour adapter le menu si besoin et vérifier le rôle)
session_start();

// 3) Inclusion du header (navbar + CSS Bootstrap)
//    Attention : header.php ne doit PAS effectuer de redirection ni de meta-refresh
include __DIR__ . '/../includes/header.php';

// 4) Connexion à la base de données (PDO)
require __DIR__ . '/../includes/db.php';

// 5) Récupération du mot‐clé
$query    = trim($_GET['q'] ?? '');    // le paramètre “q” contient le mot‐clé
$coachs   = [];                        // tableau pour stocker les coachs trouvés
$salles   = [];                        // tableau pour stocker les salles trouvées (si “salle” ou “gym” dans la recherche)
$hasQuery = ($query !== '');

if ($hasQuery) {
    // On prépare le mot‐clé pour un LIKE SQL
    $like = '%' . $query . '%';

    // --- a) Recherche de coachs (nom, prénom ou spécialité) ---
    // Utilisation de 3 placeholders positionnels
    $sqlCoach = "
      SELECT 
        c.ID_Coach,
        c.Name_Coach,
        c.LName_Coach,
        c.Specialty_Coach,
        c.EMail_Coach
      FROM `personel/coach` AS c
      WHERE c.Name_Coach      LIKE ?
         OR c.LName_Coach     LIKE ?
         OR c.Specialty_Coach LIKE ?
      ORDER BY c.Name_Coach, c.LName_Coach
    ";
    $stmtC = $pdo->prepare($sqlCoach);
    $stmtC->execute([$like, $like, $like]);
    $coachs = $stmtC->fetchAll(PDO::FETCH_ASSOC);

    // --- b) Recherche de salle de sport (service) ---
    // Si le mot tapé contient « salle » ou « gym », on récupère toutes les salles
    $qLower = mb_strtolower($query, 'UTF-8');
    if (strpos($qLower, 'salle') !== false || strpos($qLower, 'gym') !== false) {
        $sqlSalle = "
          SELECT 
            s.ID_Salle,
            s.Numero_Salle,
            s.EMail_Salle,
            s.Telephone_Salle
          FROM `salle de sport` AS s
        ";
        $stmtS = $pdo->query($sqlSalle);
        $salles = $stmtS->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<main class="container py-5">
  <h1 class="mb-4">Recherche</h1>

  <!-- Formulaire de recherche -->
  <form method="get" action="recherche.php" class="mb-5">
    <div class="input-group">
      <input
        type="text"
        name="q"
        class="form-control"
        placeholder="Rechercher un coach, une spécialité ou « salle »..."
        value="<?= htmlspecialchars($query) ?>"
        required
      >
      <button type="submit" class="btn btn-primary">Rechercher</button>
    </div>
  </form>

  <?php if (!$hasQuery): ?>
    <div class="alert alert-info">
      Entrez un mot‐clé pour rechercher un coach (nom, spécialité) ou la salle de sport.
    </div>

  <?php else: ?>

    <!-- Résultats – Coachs (sans photo) -->
    <section class="mb-5">
      <h2 class="h4 mb-3">Résultats – Coachs</h2>
      <?php if (empty($coachs)): ?>
        <div class="alert alert-warning">
          Aucun coach ne correspond à « <?= htmlspecialchars($query) ?> ».
        </div>
      <?php else: ?>
        <div class="row gy-4">
          <?php foreach ($coachs as $coach): ?>
            <?php
              // Lien vers sportDetail.php pour voir les disponibilités
              $disciplineUrl = urlencode($coach['Specialty_Coach']);
              $urlDetail     = "sportDetail.php?discipline={$disciplineUrl}";
            ?>
            <div class="col-sm-6 col-md-4">
              <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-2">
                    <?= htmlspecialchars($coach['Name_Coach'] . ' ' . $coach['LName_Coach']) ?>
                  </h5>
                  <p class="mb-1">
                    <strong>Spécialité :</strong>
                    <?= htmlspecialchars($coach['Specialty_Coach']) ?>
                  </p>
                  <p class="mb-3">
                    <strong>E-mail :</strong>
                    <?php if (!empty($coach['EMail_Coach'])): ?>
                      <a href="mailto:<?= htmlspecialchars($coach['EMail_Coach']) ?>">
                        <?= htmlspecialchars($coach['EMail_Coach']) ?>
                      </a>
                    <?php else: ?>
                      <em>Non renseigné</em>
                    <?php endif; ?>
                  </p>
                  <div class="mt-auto">
                    <a
                      href="<?= htmlspecialchars($urlDetail) ?>"
                      class="btn btn-sm btn-outline-primary"
                    >Voir les disponibilités</a>
                  </div>
                </div><!-- .card-body -->
              </div><!-- .card -->
            </div><!-- .col-* -->
          <?php endforeach; ?>
        </div><!-- .row -->
      <?php endif; ?>
    </section>

    <!-- Résultats – Salle de sport (si « salle » ou « gym » dans la requête) -->
    <?php if (!empty($salles)): ?>
      <hr>
      <section class="mt-5">
        <h2 class="h4 mb-3">Résultats – Salle de sport</h2>
        <?php foreach ($salles as $s): ?>
          <div class="card mb-3">
            <div class="card-body">
              <h5 class="card-title">
                Salle de sport Omnes (n° <?= (int)$s['Numero_Salle'] ?>)
              </h5>
              <p class="mb-1">
                <strong>E-mail :</strong>
                <a href="mailto:<?= htmlspecialchars($s['EMail_Salle']) ?>">
                  <?= htmlspecialchars($s['EMail_Salle']) ?>
                </a>
              </p>
              <p class="mb-3">
                <strong>Téléphone :</strong>
                <a href="tel:<?= htmlspecialchars($s['Telephone_Salle']) ?>">
                  <?= htmlspecialchars($s['Telephone_Salle']) ?>
                </a>
              </p>
              <a href="prendreRDVSalle.php" class="btn btn-sm btn-outline-success">
                Prendre un RDV Salle
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
