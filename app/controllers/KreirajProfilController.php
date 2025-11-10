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

        // ✉️ SLANJE EMAIL NOTIFIKACIJE ZA NOVI PROFIL
        if (!empty($email)) {
            require_once __DIR__ . '/../helpers/mailer.php';
            
            // Uloge za prikaz
            $uloge_labels = [
                'pacijent' => 'Pacijent',
                'terapeut' => 'Terapeut', 
                'recepcioner' => 'Recepcioner',
                'admin' => 'Administrator'
            ];
            
            $uloga_label = $uloge_labels[$rola] ?? ucfirst($rola);
            
            $subject = "Vaš profil je kreiran - SPES aplikacija";
            $body = "
            <h3>Poštovani/a {$ime} {$prezime},</h3>
            
            <p>Vaš profil je uspješno kreiran u SPES aplikaciji.</p>
            
            <ul>
                <li><strong>Ime:</strong> {$ime} {$prezime}</li>
                <li><strong>Username:</strong> {$username}</li>
                <li><strong>Uloga:</strong> {$uloga_label}</li>
                <li><strong>Email:</strong> {$email}</li>
            </ul>
            
            <p><strong>Lozinka će vam biti dostavljena na recepciji.</strong></p>
            
            <p>Nakon što dobijete lozinku, možete se prijaviti na aplikaciju (app.spes.ba)</p>
            
        <a href=\"https://app.spes.ba\" target=\"_blank\" 
           style=\"display: inline-block; background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;\">
           Prijava u aplikaciju
        </a>
    
            
            <hr>
            <small>Ova poruka je automatski generirana iz SPES aplikacije.</small>
            ";
            
            $mail_sent = send_mail($email, $subject, $body);
            if (!$mail_sent) {
                error_log("Greška pri slanju email-a novom korisniku: " . $email);
            }
        }

        header('Location: /profil/' . $rola . '?msg=created');
        exit;
    }
}

$title = "Kreiraj profil";
ob_start();
require __DIR__ . '/../views/profil/kreiraj.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';