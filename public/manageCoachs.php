<?php
// public/manageCoachs.php

// 1) Affichage des erreurs (développement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Démarrage de la session et restriction aux admins
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// 3) Connexion PDO et inclusion du header
require __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';

// 4) Initialisation des messages
$message      = '';
$error        = '';
$editingCoach = false;
$coachToEdit  = null;
$existingSlots = [];  // contiendra les créneaux indexés par DayOfWeek

// 5) Si on édite (paramètre ?edit=ID), charger l’existant
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $idEdit = (int) $_GET['edit'];

    // Récupérer les infos du coach
    $stmt1 = $pdo->prepare("SELECT * FROM `personel/coach` WHERE ID_Coach = ?");
    $stmt1->execute([$idEdit]);
    $coachToEdit = $stmt1->fetch(PDO::FETCH_ASSOC);
    if ($coachToEdit) {
        $editingCoach = true;

        // Récupérer tous les créneaux existants pour ce coach
        $stmt2 = $pdo->prepare("
            SELECT DayOfWeek, StartTime, EndTime 
              FROM `planning_coach`
             WHERE ID_Coach = ?
        ");
        $stmt2->execute([$idEdit]);
        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        // Indexer par DayOfWeek pour préremplir facilement
        foreach ($rows as $r) {
            $dow = intval($r['DayOfWeek']);
            $existingSlots[$dow] = [
                'start' => substr($r['StartTime'], 0, 5),
                'end'   => substr($r['EndTime'],   0, 5)
            ];
        }
    }
}

// 6) Traitement du formulaire (ajout ou édition)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 6.a) Récupérer et sanitiser les champs coach
    $coachId        = trim($_POST['coach_id'] ?? '');
    $firstName      = trim($_POST['first_name'] ?? '');
    $lastName       = trim($_POST['last_name'] ?? '');
    $specialty      = trim($_POST['specialty'] ?? '');
    $videoCoachURL  = trim($_POST['video_coach'] ?? '');
    $chatEnabled    = isset($_POST['chat_coach']) ? 1 : 0;
    $videoChatURL   = trim($_POST['video_chat_coach'] ?? '');
    $audioChatURL   = trim($_POST['audio_chat_coach'] ?? '');
    $emailCoach     = trim($_POST['email_coach'] ?? '');
    $codeCoach      = trim($_POST['code_coach'] ?? '');

    // 6.b) Récupérer les créneaux pour les 7 jours (0 à 6)
    $slots = [];
    for ($day = 0; $day < 7; $day++) {
        $hDeb = trim($_POST['startTime'][$day] ?? '');
        $hFin = trim($_POST['endTime'][$day]   ?? '');
        if ($hDeb !== '' && $hFin !== '') {
            if (preg_match('/^\d{2}:\d{2}$/', $hDeb) && preg_match('/^\d{2}:\d{2}$/', $hFin)) {
                $slots[] = [
                    'day'   => $day,
                    'start' => $hDeb . ':00',
                    'end'   => $hFin . ':00'
                ];
            }
        }
    }

    // 6.c) Validation minimale du coach
    if ($firstName === '' || $lastName === '' || $specialty === '' || $emailCoach === '' || $codeCoach === '') {
        $error = 'Les champs « Prénom », « Nom », « Spécialité », « E-mail » et « Code coach » sont obligatoires.';
    } elseif (!filter_var($emailCoach, FILTER_VALIDATE_EMAIL)) {
        $error = 'Le format de l’adresse e-mail est invalide.';
    }

    // 6.d) Upload de l’image du coach
    $imagePath = '';
    if ($error === '' && isset($_FILES['image_coach']) && $_FILES['image_coach']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image_coach']['error'] === UPLOAD_ERR_OK) {
            $tmpName      = $_FILES['image_coach']['tmp_name'];
            $originalName = basename($_FILES['image_coach']['name']);
            $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) {
                $error = 'Format d’image non valide (jpg, jpeg, png, gif uniquement).';
            } else {
                $uniqueName   = uniqid('coach_img_') . '.' . $ext;
                $targetFolder = __DIR__ . '/../uploads/coach_photos/';
                if (!is_dir($targetFolder)) {
                    mkdir($targetFolder, 0755, true);
                }
                $dest = $targetFolder . $uniqueName;
                if (move_uploaded_file($tmpName, $dest)) {
                    $imagePath = 'uploads/coach_photos/' . $uniqueName;
                } else {
                    $error = 'Échec de l’upload de l’image.';
                }
            }
        } else {
            $error = 'Erreur lors de l’upload de l’image.';
        }
    }

    // 6.e) Si en édition et pas de nouvelle image, conserver l’ancienne
    if ($error === '' && $coachId !== '' && empty($imagePath) && !empty($coachToEdit['Image_Coach'])) {
        $imagePath = $coachToEdit['Image_Coach'];
    }

    // 6.f) Insertion ou mise à jour du coach
    if ($error === '') {
        try {
            if ($coachId === '') {
                // Nouvel enregistrement
                $stmt = $pdo->prepare("
                  INSERT INTO `personel/coach`
                    (Name_Coach, LName_Coach, Image_Coach, Specialty_Coach, Video_Coach, CV_Coach,
                     Chat_Coach, Video_Chat_CoACH, Audio_Chat_CoACH, EMail_Coach, Code_Coach)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $firstName,
                    $lastName,
                    $imagePath,
                    $specialty,
                    $videoCoachURL,      // chaîne vide si pas de vidéo
                    '',
                    $chatEnabled,
                    $videoChatURL,
                    $audioChatURL,
                    $emailCoach,
                    $codeCoach
                ]);
                $newCoachId = (int) $pdo->lastInsertId();
            } else {
                // Mise à jour existante
                $upd = $pdo->prepare("
                  UPDATE `personel/coach` SET
                    Name_Coach       = ?,
                    LName_Coach      = ?,
                    Image_Coach      = ?,
                    Specialty_Coach  = ?,
                    Video_CoACH      = ?,
                    Chat_Coach       = ?,
                    Video_Chat_CoACH = ?,
                    Audio_Chat_CoACH = ?,
                    EMail_Coach      = ?,
                    Code_Coach       = ?
                  WHERE ID_Coach = ?
                ");
                $upd->execute([
                    $firstName,
                    $lastName,
                    $imagePath,
                    $specialty,
                    $videoCoachURL,
                    $chatEnabled,
                    $videoChatURL,
                    $audioChatURL,
                    $emailCoach,
                    $codeCoach,
                    (int)$coachId
                ]);
                $newCoachId = (int) $coachId;
            }

            // 6.g) Génération du fichier CV XML
            $cvFileName = 'Coach_' . $newCoachId . '_CV.xml';
            $cvFolder   = __DIR__ . '/xml/';
            $cvPath     = $cvFolder . $cvFileName;

            if (!is_dir($cvFolder)) {
                mkdir($cvFolder, 0755, true);
            }

            // Si l’utilisateur n’a pas fourni d’URL vidéo, “none”
            $videoValue = ($videoCoachURL === '' ? 'none' : htmlspecialchars($videoCoachURL));

            $cvContent  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $cvContent .= "<coach>\n";
            $cvContent .= "  <id>{$newCoachId}</id>\n";
            $cvContent .= "  <nom>" . htmlspecialchars("{$firstName} {$lastName}") . "</nom>\n";
            $cvContent .= "  <specialite>" . htmlspecialchars($specialty) . "</specialite>\n";
            $cvContent .= "  <email>" . htmlspecialchars($emailCoach) . "</email>\n";
            $cvContent .= "  <video>{$videoValue}</video>\n";
            $cvContent .= "</coach>\n";

            file_put_contents($cvPath, $cvContent);

            // Mettre à jour le chemin du CV dans la base
            $updCv = $pdo->prepare("UPDATE `personel/coach` SET CV_Coach = ? WHERE ID_Coach = ?");
            $updCv->execute(['xml/' . $cvFileName, $newCoachId]);

            // 6.h) Gestion des créneaux multiples dans planning_coach
            // 6.h.1) Supprimer tous les anciens créneaux
            $del = $pdo->prepare("DELETE FROM `planning_coach` WHERE ID_Coach = ?");
            $del->execute([$newCoachId]);

            // 6.h.2) Insérer chacun des créneaux valides
            foreach ($slots as $slot) {
                $ins = $pdo->prepare("
                  INSERT INTO `planning_coach`
                    (ID_Coach, DayOfWeek, StartTime, EndTime)
                  VALUES (?, ?, ?, ?)
                ");
                $ins->execute([
                    $newCoachId,
                    $slot['day'],
                    $slot['start'],
                    $slot['end']
                ]);
            }

            $message = "Coach enregistré (ID={$newCoachId}), créneaux mis à jour et CV XML généré.";
        } catch (PDOException $e) {
            $error = "Erreur BDD : " . htmlspecialchars($e->getMessage());
        }
    }

    // Pour préremplir en cas de nouvel enregistrement (mode édition)
    if (!empty($newCoachId)) {
        $editingCoach = true;
        $stmtAgain = $pdo->prepare("SELECT * FROM `personel/coach` WHERE ID_Coach = ?");
        $stmtAgain->execute([$newCoachId]);
        $coachToEdit = $stmtAgain->fetch(PDO::FETCH_ASSOC);

        // Récupérer à nouveau les créneaux pour recharger $existingSlots
        $stmtSlots = $pdo->prepare("SELECT DayOfWeek, StartTime, EndTime FROM `planning_coach` WHERE ID_Coach = ?");
        $stmtSlots->execute([$newCoachId]);
        $rowsSlots = $stmtSlots->fetchAll(PDO::FETCH_ASSOC);
        $existingSlots = [];
        foreach ($rowsSlots as $r) {
            $dow = intval($r['DayOfWeek']);
            $existingSlots[$dow] = [
                'start' => substr($r['StartTime'], 0, 5),
                'end'   => substr($r['EndTime'],   0, 5)
            ];
        }
    }
}

// 7) Récupérer la liste des coachs existants pour affichage
$listCoachs = $pdo->query("
  SELECT ID_Coach, Name_Coach, LName_Coach, Specialty_Coach, CV_Coach
  FROM `personel/coach`
  ORDER BY Name_Coach, LName_CoACH
")->fetchAll(PDO::FETCH_ASSOC);

// 8) Fonction pour préremplir un champ (en mode édition)
//     Retourne $_POST[$field] si soumis, sinon $coachToEdit[$field] si en édition, sinon ''
function old($field, $coachToEdit) {
    return $_POST[$field]
         ?? ($coachToEdit[$field] ?? '');
}

// 9) Fonction pour traduire DayOfWeek en texte
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

<main class="container py-5" style="max-width:900px;">
  <h2 class="mb-4">Gestion des coachs</h2>

  <!-- Affichage des messages -->
  <?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <!-- FORMULAIRE d’ajout / édition du coach -->
  <form method="post" enctype="multipart/form-data" class="mb-5">
    <!-- Champ caché pour l’ID en mode édition -->
    <input type="hidden" name="coach_id" value="<?= $editingCoach ? intval($coachToEdit['ID_Coach']) : '' ?>">

    <div class="row g-3">
      <div class="col-md-2">
        <input
          type="text"
          class="form-control"
          placeholder="ID"
          disabled
          value="<?= $editingCoach ? intval($coachToEdit['ID_Coach']) : '' ?>"
        >
      </div>
      <div class="col-md-5">
        <input
          type="text"
          name="first_name"
          class="form-control"
          placeholder="Prénom"
          required
          value="<?= htmlspecialchars(old('first_name', $coachToEdit)) ?>"
        >
      </div>
      <div class="col-md-5">
        <input
          type="text"
          name="last_name"
          class="form-control"
          placeholder="Nom"
          required
          value="<?= htmlspecialchars(old('last_name', $coachToEdit)) ?>"
        >
      </div>
    </div>

    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <input
          type="text"
          name="specialty"
          class="form-control"
          placeholder="Spécialité"
          required
          value="<?= htmlspecialchars(old('specialty', $coachToEdit)) ?>"
        >
      </div>
      <div class="col-md-6">
        <input
          type="url"
          name="video_coach"
          class="form-control"
          placeholder="URL vidéo (optionnel)"
          value="<?= htmlspecialchars(old('video_coach', $coachToEdit)) ?>"
        >
      </div>
    </div>

    <div class="mt-3">
      <label class="form-label">Image du coach</label>
      <input
        type="file"
        name="image_coach"
        class="form-control"
        accept="image/*"
      >
      <?php if ($editingCoach && !empty($coachToEdit['Image_Coach'])): ?>
        <small class="form-text text-muted">
          (Actuellement : <code><?= htmlspecialchars($coachToEdit['Image_Coach']) ?></code>)
        </small>
      <?php endif; ?>
      <small class="form-text text-muted">Formats acceptés : jpg, jpeg, png, gif.</small>
    </div>

    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <div class="form-check">
          <input
            class="form-check-input"
            type="checkbox"
            name="chat_coach"
            id="chat_coach"
            <?= (isset($_POST['chat_coach']) || ($coachToEdit['Chat_CoACH'] ?? 0)) ? 'checked' : '' ?>
          >
          <label class="form-check-label" for="chat_coach">
            Activer le chat (texto/audio/vidéo)
          </label>
        </div>
      </div>
      <div class="col-md-6">
        <input
          type="url"
          name="video_chat_coach"
          class="form-control"
          placeholder="Lien Chat Vidéo (optionnel)"
          value="<?= htmlspecialchars(old('video_chat_coach', $coachToEdit)) ?>"
        >
      </div>
    </div>

    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <input
          type="url"
          name="audio_chat_coach"
          class="form-control"
          placeholder="Lien Chat Audio (optionnel)"
          value="<?= htmlspecialchars(old('audio_chat_coach', $coachToEdit)) ?>"
        >
      </div>
      <div class="col-md-6">
        <input
          type="email"
          name="email_coach"
          class="form-control"
          placeholder="E-mail coach"
          required
          value="<?= htmlspecialchars(old('email_coACH', $coachToEdit)) ?>"
        >
      </div>
    </div>

    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <input
          type="text"
          name="code_coach"
          class="form-control"
          placeholder="Code coach (4 à 10 chiffres)"
          required
          pattern="\d{4,10}"
          maxlength="10"
          title="Au moins 4 chiffres"
          value="<?= htmlspecialchars(old('code_coACH', $coachToEdit)) ?>"
        >
      </div>
    </div>

    <hr class="my-4">
    <h4>Disponibilités hebdomadaires</h4>
    <p class="text-muted mb-3">
      Pour chaque jour, spécifiez l’heure de début et de fin. Laissez vide si indisponible.
    </p>

    <!-- Tableau fixe pour 7 jours (0=Dimanche … 6=Samedi) -->
    <table class="table table-bordered mb-4" style="max-width:900px;">
      <thead class="table-light">
        <tr>
          <th style="width:20%;">Jour</th>
          <th style="width:20%;">Heure début</th>
          <th style="width:20%;">Heure fin</th>
        </tr>
      </thead>
      <tbody>
        <?php
          for ($day = 0; $day < 7; $day++):
            $dayName     = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'][$day];
            $prefillStart = $existingSlots[$day]['start'] ?? '';
            $prefillEnd   = $existingSlots[$day]['end'] ?? '';
        ?>
        <tr>
          <td><?= $dayName ?></td>
          <td>
            <input
              type="time"
              name="startTime[<?= $day ?>]"
              class="form-control"
              value="<?= htmlspecialchars($prefillStart) ?>"
            >
          </td>
          <td>
            <input
              type="time"
              name="endTime[<?= $day ?>]"
              class="form-control"
              value="<?= htmlspecialchars($prefillEnd) ?>"
            >
          </td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>

    <div class="row">
      <div class="col">
        <button type="submit" class="btn btn-success">Enregistrer le coach</button>
      </div>
    </div>
  </form>

  <!-- 7) Liste des coachs existants -->
  <h3>Liste des coachs</h3>
  <?php if (empty($listCoachs)): ?>
    <p>Aucun coach enregistré pour le moment.</p>
  <?php else: ?>
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Nom complet</th>
          <th>Spécialité</th>
          <th>CV XML</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($listCoachs as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['ID_Coach']) ?></td>
            <td><?= htmlspecialchars($c['Name_Coach'] . ' ' . $c['LName_Coach']) ?></td>
            <td><?= htmlspecialchars($c['Specialty_Coach']) ?></td>
            <td>
              <?php if (!empty($c['CV_Coach'])): ?>
                <a href="<?= htmlspecialchars($c['CV_Coach']) ?>" target="_blank">Télécharger</a>
              <?php else: ?>
                (pas généré)
              <?php endif; ?>
            </td>
            <td>
              <a href="manageCoachs.php?edit=<?= $c['ID_Coach'] ?>"
                 class="btn btn-sm btn-primary">Éditer</a>
              <a href="deleteCoach.php?id=<?= $c['ID_Coach'] ?>"
                 class="btn btn-sm btn-danger"
                 onclick="return confirm('Confirmer la suppression ?')">
                Supprimer
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
