<?php
require_once __DIR__ . '/../helpers/load.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');


        // Pohrani token
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);

        $link = "https://app.spes.ba/reset-lozinke?token=$token";
        $subject = "Reset lozinke";
        $body = "Kliknite na link da resetujete lozinku: <a href='$link'>$link</a>";

        // Pošalji email (koristi PHPMailer ili mail())
        send_mail($email, $subject, $body);
        header('Location: /zaboravljena-lozinka?msg=reset-sent');
        exit;
        
    } else {
        header('Location: /zaboravljena-lozinka?msg=reset-invalid');
        exit;
    }
}
