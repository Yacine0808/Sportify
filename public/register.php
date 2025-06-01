<?php
// public/register.php

// 1) Affichage des erreurs (pour la phase de dev seulement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session (au cas où vous l’utilisez)
session_start();

// 3) Connexion à la base (db.php doit instancier un objet PDO $pdo)
require __DIR__ . '/../includes/db.php';

// 4) Inclusion du header (menu, Bootstrap, etc.)
include __DIR__ . '/../includes/header.php';

$error   = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 5.a) Récupération et sanitisation des champs
    $name        = trim($_POST['name'] ?? '');
    $lname       = trim($_POST['lname'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $rawPwd      = trim($_POST['password'] ?? '');
    $address1    = trim($_POST['address1'] ?? '');
    $address2    = trim($_POST['address2'] ?? '');
    $city        = trim($_POST['city'] ?? '');
    $postal      = trim($_POST['postal'] ?? '');
    $country     = trim($_POST['country'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $studentId   = trim($_POST['student_id'] ?? '');

    $cardType    = trim($_POST['card_type'] ?? '');
    $cardNumber  = trim($_POST['card_number'] ?? '');
    $cardName    = trim($_POST['card_name'] ?? '');
    $expDate     = trim($_POST['exp_date'] ?? '');   // on attend "YYYY-MM"
    $secCode     = trim($_POST['sec_code'] ?? '');

    // 5.b) Validation minimale
    if (
        $name === '' ||
        $lname === '' ||
        $email === '' ||
        $rawPwd === '' ||
        $address1 === '' ||
        $city === '' ||
        $postal === '' ||
        $country === '' ||
        $phone === '' ||
        $studentId === '' ||
        $cardType === '' ||
        $cardNumber === '' ||
        $cardName === '' ||
        $expDate === '' ||
        $secCode === ''
    ) {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Le format de l’email est invalide.';
    }
    elseif (!preg_match('/^\d{5}$/', $postal)) {
        $error = 'Le code postal doit comporter exactement 5 chiffres.';
    }
    elseif (!preg_match('/^\d{8}$/', $studentId)) {
        $error = 'Le numéro de carte étudiante doit comporter exactement 8 chiffres.';
    }
    elseif (!preg_match('/^\d{12,16}$/', $cardNumber)) {
        $error = 'Le numéro de carte bancaire doit contenir uniquement des chiffres, entre 12 et 16 au total.';
    }
    elseif (!preg_match('/^\d{3,4}$/', $secCode)) {
        $error = 'Le code de sécurité doit comporter 3 ou 4 chiffres.';
    }
    elseif (!preg_match('/^\d{4}-\d{2}$/', $expDate)) {
        $error = 'Le format de la date d’expiration est invalide (doit être "YYYY-MM").';
    }

    // 5.c) Si pas d’erreur détectée, on stocke le mot de passe tel quel (sans hash)
    if ($error === '') {
        $password = $rawPwd; // Stockage en clair
    }

    // 5.d) Si toujours pas d’erreur, on insère en base
    if ($error === '') {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO client
                  (Name_User, LName_User,
                   AdressLigne1_User, AdressLigne2_User, Ville_User,
                   CodePostal_User, Pays_User, Telephone_User, EMail_User, Password_User,
                   Carte_Etudiant_User, Carte_Bleu_User, Numero_Carte_User, Nom_Carte_User,
                   Date_Expiration_User, Code_Securite_User)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $name,
                $lname,
                $address1,
                $address2,
                $city,
                $postal,
                $country,
                $phone,
                $email,
                $password,         
                $studentId,
                $cardType,
                $cardNumber,
                $cardName,
                $expDate,           
                $secCode
            ]);

            $message = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
        }
        catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'Cette adresse e-mail est déjà utilisée.';
            } else {
                $error = 'Erreur lors de l’inscription : ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>

<main class="container py-5" style="max-width:600px;">
  <h2 class="mb-4">Créer votre compte client</h2>

  <?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="post" action="" autocomplete="off" novalidate>
    <h3>Informations personnelles</h3>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <input
          type="text"
          name="name"
          class="form-control"
          placeholder="Nom"
          required
          value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
        >
      </div>
      <div class="col-md-6">
        <input
          type="text"
          name="lname"
          class="form-control"
          placeholder="Prénom"
          required
          value="<?= htmlspecialchars($_POST['lname'] ?? '') ?>"
        >
      </div>
    </div>

    <div class="mb-3">
      <input
        type="email"
        name="email"
        class="form-control"
        placeholder="Email"
        required
        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
      >
    </div>
    <div class="mb-3">
      <input
        type="password"
        name="password"
        class="form-control"
        placeholder="Mot de passe"
        required
        autocomplete="new-password"
        value=""
      >
    </div>

    <div class="mb-3">
      <input
        type="text"
        name="address1"
        class="form-control"
        placeholder="Adresse ligne 1"
        required
        value="<?= htmlspecialchars($_POST['address1'] ?? '') ?>"
      >
    </div>
    <div class="mb-3">
      <input
        type="text"
        name="address2"
        class="form-control"
        placeholder="Adresse ligne 2"
        value="<?= htmlspecialchars($_POST['address2'] ?? '') ?>"
      >
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <input
          type="text"
          name="city"
          class="form-control"
          placeholder="Ville"
          required
          value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
        >
      </div>
      <div class="col-md-3">
        <input
          type="text"
          name="postal"
          class="form-control"
          placeholder="Code postal (5 chiffres)"
          required
          maxlength="5"
          pattern="\d{5}"
          title="Entrez exactement 5 chiffres"
          value="<?= htmlspecialchars($_POST['postal'] ?? '') ?>"
        >
      </div>
      <div class="col-md-3">
        <input
          type="text"
          name="country"
          class="form-control"
          placeholder="Pays"
          required
          value="<?= htmlspecialchars($_POST['country'] ?? '') ?>"
        >
      </div>
    </div>
    <div class="mb-3">
      <input
        type="text"
        name="phone"
        class="form-control"
        placeholder="Téléphone"
        required
        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
      >
    </div>
    <div class="mb-3">
      <input
        type="text"
        name="student_id"
        class="form-control"
        placeholder="Numéro carte étudiante (8 chiffres)"
        required
        maxlength="8"
        pattern="\d{8}"
        title="Entrez exactement 8 chiffres"
        value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>"
      >
    </div>

    <h3>Informations bancaires</h3>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <select name="card_type" class="form-select" required>
          <option value="">Type de carte</option>
          <option value="VISA" <?= (($_POST['card_type'] ?? '') === 'VISA') ? 'selected' : '' ?>>VISA</option>
          <option value="MasterCard" <?= (($_POST['card_type'] ?? '') === 'MasterCard') ? 'selected' : '' ?>>MasterCard</option>
          <option value="Amex" <?= (($_POST['card_type'] ?? '') === 'Amex') ? 'selected' : '' ?>>American Express</option>
          <option value="PayPal" <?= (($_POST['card_type'] ?? '') === 'PayPal') ? 'selected' : '' ?>>PayPal</option>
        </select>
      </div>
      <div class="col-md-6">
        <input
          type="text"
          name="card_number"
          class="form-control"
          placeholder="Numéro de carte (12–16 chiffres)"
          required
          maxlength="16"
          pattern="\d{12,16}"
          title="Entrez entre 12 et 16 chiffres"
          value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>"
        >
      </div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <input
          type="text"
          name="card_name"
          class="form-control"
          placeholder="Nom sur la carte"
          required
          value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>"
        >
      </div>
      <div class="col-md-3">
        <input
          type="month"
          name="exp_date"
          class="form-control"
          placeholder="Date d’expiration (YYYY-MM)"
          required
          value="<?= htmlspecialchars($_POST['exp_date'] ?? '') ?>"
        >
      </div>
      <div class="col-md-3">
        <input
          type="text"
          name="sec_code"
          class="form-control"
          placeholder="CVV"
          required
          maxlength="4"
          pattern="\d{3,4}"
          title="Entrez 3 ou 4 chiffres"
          value="<?= htmlspecialchars($_POST['sec_code'] ?? '') ?>"
        >
      </div>
    </div>

    <button type="submit" class="btn btn-success w-100 mt-3">S’enregistrer</button>
  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
