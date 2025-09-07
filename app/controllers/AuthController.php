<?php

require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/auth.php';

$error = '';

// Ako je POST zahtjev – pokušaj login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $lozinka = $_POST['lozinka'] ?? '';

    if (empty($email) || empty($lozinka)) {
        $error = 'Molimo unesite email i lozinku.';
    } else {
        $pdo = db();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($lozinka, $user['lozinka'])) {
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$stmt->execute([$user['id']]);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_uloga'] = $user['uloga'];
            $_SESSION['user_ime'] = $user['ime'];
            $_SESSION['user_prezime'] = $user['prezime'];

            header('Location: /dashboard');
            exit;
        } else {
            $error = 'Pogrešan email ili lozinka.';
        }
    }
}

// GET ili greška → prikaži login formu sa porukom
require_once __DIR__ . '/../views/login.php';
