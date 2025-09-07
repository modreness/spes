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

// Provjera da li korisnik postoji
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$korisnik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$korisnik) {
    http_response_code(404);
    echo "Korisnik nije pronađen.";
    exit;
}

// Ako je korisnik terapeut — zamrzni ime i prezime u rasporedu
if ($korisnik['uloga'] === 'terapeut') {
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


// Brisanje korisnika
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

// Vrati korisnika na prethodnu stranicu
header("Location: {$_SERVER['HTTP_REFERER']}");
exit;
