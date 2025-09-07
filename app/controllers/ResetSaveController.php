<?php
require_once __DIR__ . '/../helpers/load.php';

$token = $_POST['token'] ?? '';
$lozinka = $_POST['lozinka'] ?? '';
$ponovi_lozinku = $_POST['ponovi_lozinku'] ?? '';

if (empty($lozinka) || empty($ponovi_lozinku)) {
    // Ako nije upisano
    header('Location: /reset-lozinke?token=' . urlencode($_POST['token']) . '&msg=empty-fields');
    exit;
}

if ($lozinka !== $ponovi_lozinku) {
    // Ako se ne podudaraju
    header('Location: /reset-lozinke?token=' . urlencode($_POST['token']) . '&msg=not-matching');
    exit;
}

$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $email = $row['email'];
    $hashed = password_hash($lozinka, PASSWORD_BCRYPT);

    $update = $pdo->prepare("UPDATE users SET lozinka = ? WHERE email = ?");
    $update->execute([$hashed, $email]);

    $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

    header('Location: /login?reset=uspjesan');
        exit;
} else {
    header('Location: /login?reset=invalid');
        exit;
}
