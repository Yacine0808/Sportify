<?php
// public/chatroom.php

// 1) Mode développement : afficher toutes les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session
session_start();

// 3) Vérifier qu’on est bien un coach connecté
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'coach') {
    header('Location: login.php');
    exit;
}

// 4) Inclusion du header (navbar + CSS Bootstrap)
include __DIR__ . '/../includes/header.php';

// 5) Connexion à la base de données (PDO)
require __DIR__ . '/../includes/db.php';

// 6) Récupérer l’ID du coach depuis la session
//    (dans votre code de login, assurez-vous d’avoir fait : $_SESSION['coach_id'] = <ID_Coach>)
$coachId = (int) ($_SESSION['coach_id'] ?? 0);
if ($coachId <= 0) {
    echo "<main class='container py-5'>
            <div class='alert alert-danger'>Coach non identifié.</div>
          </main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// 7) Requête : lister tous les clients ayant déjà eu au moins un RDV avec ce coach
$sqlClients = "
    SELECT DISTINCT
      u.ID_User,
      u.Name_User,
      u.LName_User,
      u.EMail_User,
      u.Telephone_User
    FROM `client` AS u
    INNER JOIN `rdv` AS r
      ON u.ID_User = r.User_id
    WHERE r.ID_Coach = :cid
    ORDER BY u.Name_User, u.LName_User
";
$stmt = $pdo->prepare($sqlClients);
$stmt->execute([':cid' => $coachId]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 8) Requête pour récupérer, si besoin, les liens vidéo/audio du coach
$stmt2 = $pdo->prepare("
    SELECT Video_Chat_CoACH, Audio_Chat_CoACH
    FROM `personel/coach`
    WHERE ID_Coach = ?
");
$stmt2->execute([$coachId]);
$coachInfo = $stmt2->fetch(PDO::FETCH_ASSOC);

// 9) Pour la barre de recherche “JavaScript” côté client, on passera le tableau des noms
//    dans un simple data-attribute, ou on utilisera un champs `<input>` pour filtrer au fur et à mesure.
//    Ici, on fait le nécessaire côté HTML + un petit script JS simple à la fin.
?>
<main class="container py-5">
  <h1 class="mb-4">Chatroom</h1>

  <?php if (empty($clients)): ?>
    <div class="alert alert-info">
      Vous n’avez aucun client à afficher pour le moment.
    </div>
  <?php else: ?>
    <!-- Barre de recherche pour filtrer les clients -->
    <div class="row mb-4">
      <div class="col-md-6">
        <input
          type="text"
          id="searchClient"
          class="form-control"
          placeholder="Rechercher un client..."
          autofocus
        >
      </div>
    </div>

    <div class="row gy-4" id="clientsContainer">
      <?php foreach ($clients as $c): ?>
        <!-- Chaque carte client aura une classe .client-card et un attribut data-name pour la recherche -->
        <div
          class="col-sm-6 col-md-4 client-card"
          data-name="<?= htmlspecialchars(strtolower($c['Name_User'] . ' ' . $c['LName_User'])) ?>"
        >
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title mb-2">
                <?= htmlspecialchars($c['Name_User'] . ' ' . $c['LName_User']) ?>
              </h5>
              <p class="mb-1">
                <strong>E-mail :</strong>
                <a href="mailto:<?= htmlspecialchars($c['EMail_User']) ?>">
                  <?= htmlspecialchars($c['EMail_User']) ?>
                </a>
              </p>
              <p class="mb-3">
                <strong>Téléphone :</strong>
                <a href="tel:<?= htmlspecialchars($c['Telephone_User']) ?>">
                  <?= htmlspecialchars($c['Telephone_User']) ?>
                </a>
              </p>

              <div class="mt-auto">
                <!-- Boutons d’action, stylisés par un icône, pas seulement du texte -->
                <div class="btn-group" role="group">
                  <!-- Mail icon (SVG inline ou utiliser des FontAwesome si installé) -->
                  <a
                    href="mailto:<?= htmlspecialchars($c['EMail_User']) ?>"
                    class="btn btn-outline-primary btn-sm"
                    title="Envoyer un e-mail"
                  >
                    ✉️
                  </a>

                  <!-- Appel téléphonique -->
                  <a
                    href="tel:<?= htmlspecialchars($c['Telephone_User']) ?>"
                    class="btn btn-outline-success btn-sm"
                    title="Appeler"
                  >
                    📞
                  </a>

                  <!-- Si le coach a défini un lien vidéo, le proposer -->
                  <?php if (!empty($coachInfo['Video_Chat_CoACH'])): ?>
                    <a
                      href="<?= htmlspecialchars($coachInfo['Video_Chat_CoACH']) ?>"
                      class="btn btn-outline-warning btn-sm"
                      target="_blank"
                      title="Chat vidéo"
                    >
                      🎥
                    </a>
                  <?php endif; ?>

                  <!-- Si le coach a défini un lien audio, le proposer -->
                  <?php if (!empty($coachInfo['Audio_Chat_CoACH'])): ?>
                    <a
                      href="<?= htmlspecialchars($coachInfo['Audio_Chat_CoACH']) ?>"
                      class="btn btn-outline-secondary btn-sm"
                      target="_blank"
                      title="Chat audio"
                    >
                      🎙️
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div><!-- .card-body -->
          </div><!-- .card -->
        </div><!-- .col-* -->
      <?php endforeach; ?>
    </div><!-- .row -->
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Petit script JS pour filtrer en direct -->
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const searchInput = document.getElementById('searchClient');
    const clientCards  = document.querySelectorAll('.client-card');

    searchInput.addEventListener('keyup', function(){
      const query = this.value.trim().toLowerCase();
      clientCards.forEach(card => {
        const name = card.getAttribute('data-name');
        if (name.includes(query)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
</script>
