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
    $_SESSION['error_message'] = "Korisnik nije pronađen.";
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
}

try {
    // Započni transakciju
    $pdo->beginTransaction();
    
    $uloga = $korisnik['uloga'];
    
    // PACIJENT - obriši sve vezano za pacijenta
    if ($uloga === 'pacijent') {
        // Obriši kartone
        $stmt = $pdo->prepare("DELETE FROM kartoni WHERE pacijent_id = ?");
        $stmt->execute([$id]);
        
        // Obriši tretmane (preko karton_id koji je vezan sa pacijentom)
        // Ne treba posebno brisati jer kartoni su već obrisani (CASCADE ili manual)
        // ALI ako nema CASCADE, onda:
        $stmt = $pdo->prepare("
            DELETE t FROM tretmani t
            JOIN kartoni k ON t.karton_id = k.id
            WHERE k.pacijent_id = ?
        ");
        // OVO NEĆE RADITI jer smo već obrisali kartone!
        // Ispravno: prvo obriši tretmane, PA ONDA kartone
        
        // ISPRAVNO:
        // 1. Prvo nađi sve kartone pacijenta
        $stmtKartoni = $pdo->prepare("SELECT id FROM kartoni WHERE pacijent_id = ?");
        $stmtKartoni->execute([$id]);
        $kartoni = $stmtKartoni->fetchAll(PDO::FETCH_COLUMN);
        
        // 2. Obriši tretmane za te kartone
        if (!empty($kartoni)) {
            $placeholders = implode(',', array_fill(0, count($kartoni), '?'));
            $stmt = $pdo->prepare("DELETE FROM tretmani WHERE karton_id IN ($placeholders)");
            $stmt->execute($kartoni);
        }
        
        // 3. Obriši kartone
        $stmt = $pdo->prepare("DELETE FROM kartoni WHERE pacijent_id = ?");
        $stmt->execute([$id]);
        
        // 4. Obriši termine
        $stmt = $pdo->prepare("DELETE FROM termini WHERE pacijent_id = ?");
        $stmt->execute([$id]);
    }
    
    // TERAPEUT - zamrzni podatke svugdje, čuvaj historiju
    if ($uloga === 'terapeut') {
        // 1. Zamrzni ime i prezime u rasporedu
        $stmtRasporedi = $pdo->prepare("SELECT id FROM rasporedi_sedmicni WHERE terapeut_id = ?");
        $stmtRasporedi->execute([$id]);
        $rasporedi = $stmtRasporedi->fetchAll(PDO::FETCH_ASSOC);

        if ($rasporedi) {
            $stmtZamrzni = $pdo->prepare("
                UPDATE rasporedi_sedmicni 
                SET terapeut_ime = ?, terapeut_prezime = ?, terapeut_id = NULL 
                WHERE id = ?
            ");
            foreach ($rasporedi as $r) {
                $stmtZamrzni->execute([$korisnik['ime'], $korisnik['prezime'], $r['id']]);
            }
        }
        
        // 2. Zamrzni ime i prezime u tretmanima
        $stmtTretmani = $pdo->prepare("
            UPDATE tretmani 
            SET terapeut_ime = ?, terapeut_prezime = ?, terapeut_id = NULL 
            WHERE terapeut_id = ?
        ");
        $stmtTretmani->execute([$korisnik['ime'], $korisnik['prezime'], $id]);
        
        // 3. Zamrzni ime i prezime u terminima (historia termina)
        $stmtTermini = $pdo->prepare("
            UPDATE termini 
            SET terapeut_ime = ?, terapeut_prezime = ?, terapeut_id = NULL 
            WHERE terapeut_id = ?
        ");
        $stmtTermini->execute([$korisnik['ime'], $korisnik['prezime'], $id]);
    }
    
    // Obriši korisnika
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    // Potvrdi transakciju
    $pdo->commit();
    
    $_SESSION['success_message'] = "Korisnik " . htmlspecialchars($korisnik['ime'] . " " . $korisnik['prezime']) . " je uspješno obrisan.";
    
} catch (Exception $e) {
    // Poništi transakciju u slučaju greške
    $pdo->rollBack();
    $_SESSION['error_message'] = "Greška pri brisanju: " . $e->getMessage();
}

// Redirect na ispravnu stranicu ovisno o ulozi
$redirect_map = [
    'pacijent' => '/profil/pregled/pacijent',
    'terapeut' => '/profil/pregled/terapeut',
    'recepcioner' => '/profil/pregled/recepcioner',
    'admin' => '/profil/pregled/admin'
];

$redirect = $redirect_map[$uloga] ?? '/profil/pregled/pacijent';
header("Location: $redirect");
exit;