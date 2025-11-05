<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$user = current_user();
$pdo = db();

// Dozvoli samo adminu i recepcioneru
if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    http_response_code(403);
    echo "Nemate pristup.";
    exit;
}

$poruka = '';

// Obrada forme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime = $_POST['ime'] ?? '';
    $prezime = $_POST['prezime'] ?? '';
    $email = $_POST['email'] ?: null;
    $username = $_POST['username'] ?? '';
    $rola = $_POST['uloga'] ?? '';
    $lozinka = $_POST['lozinka'] ?? '';

    if (!$ime || !$prezime || !$rola || !$lozinka) {
        $poruka = "Sva polja su osim e-mail adrese obavezna.";
    } else {
        $hash = password_hash($lozinka, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (ime, prezime, email, username, lozinka, uloga) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ime, $prezime, $email, $username, $hash, $rola]);

        header('Location: /profil/' . $rola . '?msg=created');
        exit;
    }
}

$title = "Kreiraj profil";
ob_start();
require __DIR__ . '/../views/profil/kreiraj.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';
