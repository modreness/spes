<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$pdo = db();
$logovani = current_user();
$poruka = '';

// Koga uređujemo
$id = $_POST['id'] ?? $_GET['id'] ?? $logovani['id'];


// Ako pokušava uređivati drugog bez ovlasti
if ($id != $logovani['id'] && !in_array($logovani['uloga'], ['admin', 'recepcioner'])) {
    require __DIR__ . '/../views/errors/403.php';
    exit;
}

// Dohvati korisnika koji se uređuje
$korisnik = get_user_by_id($id);
if (!$korisnik) {
    http_response_code(404);
    echo "Korisnik nije pronađen.";
    exit;
}

//RESET MAIL ZA LOZINKU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['posalji_reset']) && $logovani['uloga'] === 'admin') {
    $email = $korisnik['email'] ?? '';
    if ($email) {
        $token = bin2hex(random_bytes(32));
        $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);

        $link = "https://app.spes.ba/reset-lozinke?token=$token";
        $subject = "Reset lozinke";
        $body = "Kliknite na link da resetujete lozinku: <a href='$link'>$link</a>";

        send_mail($email, $subject, $body);

        header("Location: /profil/uredi?id=$id&msg=reset-sent");
        exit;
    }
}


// OBRADA POST ZA UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime = $_POST['ime'] ?? '';
    $prezime = $_POST['prezime'] ?? '';
    $email = $_POST['email'] ?? '';
    $uloga = $korisnik['uloga']; // zadrži staru

    // Ako admin uređuje druge, može mijenjati ulogu
    if ($id != $logovani['id'] && $logovani['uloga'] === 'admin') {
        $dozvoljene = ['pacijent', 'terapeut', 'recepcioner', 'admin'];
        $post_uloga = $_POST['uloga'] ?? '';
        if (in_array($post_uloga, $dozvoljene)) {
            $uloga = $post_uloga;
        }
    }

    $profilna = $korisnik['slika'];

    // Upload slike
    if (!empty($_FILES['profilna']['name'])) {
        $ext = strtolower(pathinfo($_FILES['profilna']['name'], PATHINFO_EXTENSION));
        $dozvoljene = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $velicina = $_FILES['profilna']['size'];

        if (!in_array($ext, $dozvoljene)) {
            $poruka = 'Nepodržan format slike.';
        } elseif ($velicina > 2 * 1024 * 1024) {
            $poruka = 'Slika prelazi 2MB.';
        } else {
            $naziv = date('Ymd_His') . '_' . str_replace(' ', '_', $ime_prezime) . '.' . $ext;
            $upload_dir = realpath(__DIR__ . '/../../uploads/profilne/') . '/';

            if (!empty($korisnik['slika']) && file_exists($upload_dir . $korisnik['slika'])) {
                @unlink($upload_dir . $korisnik['slika']);
            }

            if (move_uploaded_file($_FILES['profilna']['tmp_name'], $upload_dir . $naziv)) {
                $profilna = $naziv;
            } else {
                $poruka = 'Greška pri spremanju slike.';
            }
        }
    }

    // Brisanje slike
    if (isset($_POST['obrisi_sliku'])) {
        if (!empty($korisnik['slika'])) {
            $upload_dir = realpath(__DIR__ . '/../../uploads/profilne/') . '/';
            @unlink($upload_dir . $korisnik['slika']);
        }
        $profilna = null;
    }

    // Provjera duplikata emaila (ignoriši ako je isti korisnik)
    if (!$poruka) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $poruka = 'Email adresa je već u upotrebi kod drugog korisnika.';
        }
    }

    // Promjena lozinke ako admin uređuje drugog i lozinka je upisana
    if (!$poruka && $id != $logovani['id'] && $logovani['uloga'] === 'admin') {
        $nova_lozinka = $_POST['nova_lozinka'] ?? '';
        $ponovi_lozinku = $_POST['ponovi_lozinku'] ?? '';

        if (!empty($nova_lozinka) && !empty($ponovi_lozinku)) {
            if ($nova_lozinka !== $ponovi_lozinku) {
                $poruka = 'Lozinke se ne podudaraju.';
            } else {
                $hash = password_hash($nova_lozinka, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET lozinka = ? WHERE id = ?");
                $stmt->execute([$hash, $id]);
            }
        }
    }

    // Spremi ako sve ok
    if (!$poruka) {
        $stmt = $pdo->prepare("UPDATE users SET ime = ?, prezime = ?, email = ?, slika = ?, uloga = ? WHERE id = ?");
        $stmt->execute([$ime, $prezime, $email, $profilna, $uloga, $id]);

        if ($id == $logovani['id']) {
            $_SESSION['user'] = get_user_by_id($id);
        }

        header('Location: /profil/uredi?id=' . $id . '&msg=updated');
        exit;
    }
}

// Prikaz
$title = "Uredi profil";
ob_start();
require __DIR__ . '/../views/profil/uredi-profil.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';
