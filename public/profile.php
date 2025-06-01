<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: Login.php');
    exit;
}

require __DIR__ . '/../includes/db.php';

$stmt = $pdo->prepare("SELECT * FROM client WHERE ID_User = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Masquage des données bancaires
$maskedCard = str_repeat('*', strlen($user['Numero_Carte_User']) - 4) . substr($user['Numero_Carte_User'], -4);
$maskedSec  = str_repeat('*', strlen($user['Code_Securite_User']) - 1) . substr($user['Code_Securite_User'], -1);

include __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
  <h2>Mon profil</h2>
  <ul class="list-group mb-4">
    <li class="list-group-item"><strong>Nom :</strong> <?= htmlspecialchars($user['Name_User']) ?></li>
    <li class="list-group-item"><strong>Prénom :</strong> <?= htmlspecialchars($user['LName_User']) ?></li>
    <li class="list-group-item"><strong>Email :</strong> <?= htmlspecialchars($user['EMail_User']) ?></li>
    <li class="list-group-item">
      <strong>Adresse :</strong>
      <?= htmlspecialchars($user['AdressLigne1_User']) ?>,
      <?= htmlspecialchars($user['AdressLigne2_User']) ?>,
      <?= htmlspecialchars($user['Ville_User']) ?> <?= htmlspecialchars($user['CodePostal_User']) ?>,
      <?= htmlspecialchars($user['Pays_User']) ?>
    </li>
    <li class="list-group-item"><strong>Téléphone :</strong> <?= htmlspecialchars($user['Telephone_User']) ?></li>
  </ul>

  <h3>Informations bancaires</h3>
  <ul class="list-group mb-4">
    <li class="list-group-item"><strong>Type de carte :</strong> <?= htmlspecialchars($user['Carte_Bleu_User']) ?></li>
    <li class="list-group-item"><strong>Numéro :</strong> <?= $maskedCard ?></li>
    <li class="list-group-item"><strong>Code sécurité :</strong> <?= $maskedSec ?></li>
    <li class="list-group-item"><strong>Expiration :</strong> <?= htmlspecialchars($user['Date_Expiration_User']) ?></li>
  </ul>

  <a class="btn btn-secondary" href="Logout.php">Se déconnecter</a>
</main>

<?php
include __DIR__ . '/../includes/footer.php';
?>
