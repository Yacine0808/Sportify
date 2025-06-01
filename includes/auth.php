<?php
/**
 * Vérifie si les identifiants correspondent à un admin fixe.
 *
 * @param string $email     L’adresse e-mail de l’admin.
 * @param string $code      Le code de l’admin.
 * @param PDO    $pdo       Instance PDO pour la base de données.
 * @param array  &$adminData Tableau passé par référence.
 * @return bool             True si l’admin existe et que le couple (email, code) est correct.
 */
function is_admin(string $email, string $code, PDO $pdo, array &$adminData = []): bool
{
    $stmt = $pdo->prepare("
      SELECT 
        ID_Admin   AS id,
        Name_Admin
      FROM `admin`
      WHERE EMail_Admin = :email
        AND Code_Admin  = :code
      LIMIT 1
    ");
    $stmt->execute([
        'email' => $email,
        'code'  => $code
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $adminData = $row;
        return true;
    }
    return false;
}

/**
 * Vérifie si les identifiants correspondent à un coach.
 *
 * @param string $email      L’adresse e-mail du coach.
 * @param string $code       Le code (non-hashé) du coach.
 * @param PDO    $pdo        Instance PDO pour la base de données.
 * @param array  &$coachData Tableau passé par référence.
 *                           
 * @return bool              True si le coach existe et que le couple (email, code) est correct.
 */
function is_coach(string $email, string $code, PDO $pdo, array &$coachData = []): bool
{
    $stmt = $pdo->prepare("
      SELECT 
        ID_Coach   AS id,
        Name_Coach,
        LName_Coach,
        Image_Coach
      FROM `personel/coach`
      WHERE EMail_Coach = :email
        AND Code_Coach  = :code
      LIMIT 1
    ");
    $stmt->execute([
        'email' => $email,
        'code'  => $code
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $coachData = $row;
        return true;
    }
    return false;
}
