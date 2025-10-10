<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$pdo = db();
$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    http_response_code(403);
    echo "Nemate pristup ovoj stranici.";
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo "Neispravan ID.";
    exit;
}

// Spriječi brisanje samog sebe
if ((int)$id === (int)$user['id']) {
    http_response_code(403);
    echo "Ne možete obrisati sami sebe.";
    exit;
}

// Provjera da li korisnik postoji i aktivan je
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND aktivan = 1");
$stmt->execute([$id]);
$korisnik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!korisnik) {
    http_response_code(404);
    echo "Korisnik nije pronađen ili je već obrisan.";
    exit;
}

// **SOFT DELETE** - označavamo korisnika kao neaktivnog
$uloga = $korisnik['uloga'];
$razlog_brisanja = "[" . date('Y-m-d H:i:s') . "] Obrisan od strane: " . $user['ime'] . " " . $user['prezime'];

// Ako je korisnik terapeut — zamrzni ime i prezime u rasporedu
if ($uloga === 'terapeut') {
    // Prvo pronađi sve rasporede gdje je on terapeut
    $stmtRasporedi = $pdo->prepare("SELECT id FROM rasporedi_sedmicni WHERE terapeut_id = ?");
    $stmtRasporedi->execute([$id]);
    $rasporedi = $stmtRasporedi->fetchAll(PDO::FETCH_ASSOC);

    if ($rasporedi) {
        // Ažuriraj svaki red pojedinačno
        $stmtZamrzni = $pdo->prepare("
            UPDATE rasporedi_sedmicni 
            SET terapeut_ime = ?, terapeut_prezime = ?, terapeut_id = NULL 
            WHERE id = ?
        ");
        foreach ($rasporedi as $r) {
            $stmtZamrzni->execute([$korisnik['ime'], $korisnik['prezime'], $r['id']]);
        }
    }
}

// **SOFT DELETE** - postavi deleted_at na trenutno vrijeme
$stmt = $pdo->prepare("
    UPDATE users 
    SET deleted_at = NOW(), 
        napomena = CONCAT(COALESCE(napomena, ''), '\n\n[", NOW(), "] ', ?)
    WHERE id = ?
");
$stmt->execute([$razlog_brisanja, $id]);

// Postavimo flash poruku
$_SESSION['success_message'] = "Korisnik " . htmlspecialchars($korisnik['ime'] . " " . $korisnik['prezime']) . " je uspješno obrisan.";

// Vrati korisnika na prethodnu stranicu
header("Location: {$_SERVER['HTTP_REFERER']}");
exit;