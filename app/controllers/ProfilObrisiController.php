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
    
    // Helper funkcija za sigurno brisanje fajla
    function sigurnoBrisanje($file_path) {
        if (empty($file_path)) return false;
        
        // Provjeri da li je path apsolutan ili relativan
        if (!is_file($file_path)) {
            // Pokušaj relativni path od root-a
            $file_path = __DIR__ . '/../../' . ltrim($file_path, '/');
        }
        
        if (file_exists($file_path) && is_file($file_path)) {
            $success = unlink($file_path);
            if ($success) {
                error_log("Uspješno obrisan fajl: $file_path");
            } else {
                error_log("Greška pri brisanju fajla: $file_path");
            }
            return $success;
        }
        return false;
    }
    
    // PACIJENT - obriši sve vezano za pacijenta
    if ($uloga === 'pacijent') {
        
        // 1. PRVO dohvati sve fajlove nalaza da ih fizički obrišeš
        $stmt = $pdo->prepare("SELECT file_path FROM nalazi WHERE pacijent_id = ? AND file_path IS NOT NULL");
        $stmt->execute([$id]);
        $fajlovi_nalaza = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Obriši fizičke fajlove
        foreach ($fajlovi_nalaza as $file_path) {
            sigurnoBrisanje($file_path);
        }
        
        // 2. Sada obriši nalaze iz baze
        $stmt = $pdo->prepare("DELETE FROM nalazi WHERE pacijent_id = ?");
        $stmt->execute([$id]);
        
        // 3. Nađi sve kartone pacijenta
        $stmtKartoni = $pdo->prepare("SELECT id FROM kartoni WHERE pacijent_id = ?");
        $stmtKartoni->execute([$id]);
        $kartoni = $stmtKartoni->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($kartoni)) {
            $placeholders = implode(',', array_fill(0, count($kartoni), '?'));
            
            // 4. Obriši karton_dijagnoze za sve kartone
            $stmt = $pdo->prepare("DELETE FROM karton_dijagnoze WHERE karton_id IN ($placeholders)");
            $stmt->execute($kartoni);
            
            // 5. Obriši tretmane za te kartone
            $stmt = $pdo->prepare("DELETE FROM tretmani WHERE karton_id IN ($placeholders)");
            $stmt->execute($kartoni);
        }
        
        // 6. Obriši termine direktno vezane za pacijenta
        $stmt = $pdo->prepare("DELETE FROM termini WHERE pacijent_id = ?");
        $stmt->execute([$id]);
        
        // 7. Obriši kupljene pakete (ako postoje)
        try {
            $stmt = $pdo->prepare("DELETE FROM kupljeni_paketi WHERE pacijent_id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Tabela možda ne postoji, ignoriši grešku
        }
        
        // 8. Obriši kartone
        $stmt = $pdo->prepare("DELETE FROM kartoni WHERE pacijent_id = ?");
        $stmt->execute([$id]);
    }
    
    // OBRIŠI PROFILNU SLIKU ZA SVE ULOGE
    if (!empty($korisnik['slika']) && $korisnik['slika'] !== 'default.jpg') {
        $slika_path = 'uploads/profilne/' . $korisnik['slika'];
        sigurnoBrisanje($slika_path);
    }
    
    // RECEPCIONER/ADMIN - obriši/nullificiraj povezane zapise
    if (in_array($uloga, ['recepcioner', 'admin'])) {
        
    // RECEPCIONER/ADMIN - zamrzni imena u rasporedima i nullificiraj druge reference
    if (in_array($uloga, ['recepcioner', 'admin'])) {
        
        // Zamrzni ime i prezime u rasporedima (isti princip kao za terapeute)
        try {
            $stmt = $pdo->prepare("
                UPDATE rasporedi_sedmicni 
                SET unosio_ime = ?, unosio_prezime = ?, unosio_id = NULL 
                WHERE unosio_id = ?
            ");
            $stmt->execute([$korisnik['ime'], $korisnik['prezime'], $id]);
            error_log("Zamrznuo imena u rasporedima za korisnika: " . $korisnik['ime'] . " " . $korisnik['prezime']);
        } catch (PDOException $e) {
            error_log("Greška pri zamrzavanju imena u rasporedima: " . $e->getMessage());
            
            // Ako nema unosio_ime/unosio_prezime kolone, samo nullificiraj
            try {
                $stmt = $pdo->prepare("UPDATE rasporedi_sedmicni SET unosio_id = NULL WHERE unosio_id = ?");
                $stmt->execute([$id]);
                error_log("Fallback: nullificiran unosio_id u rasporedima");
            } catch (PDOException $e2) {
                error_log("Kritična greška s rasporedima: " . $e2->getMessage());
            }
        }
        
        // Nullificiraj otvorio_id u kartonima (ovo možda ne treba zamrzavati jer se prikazuje "otvorio_ime")
        try {
            $stmt = $pdo->prepare("UPDATE kartoni SET otvorio_id = NULL WHERE otvorio_id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Greška pri nullificiranju kartoni.otvorio_id: " . $e->getMessage());
        }
        
        // Nullificiraj dodao_id u nalazima (ovo možda ne treba zamrzavati jer se prikazuje "dodao_ime")
        try {
            $stmt = $pdo->prepare("UPDATE nalazi SET dodao_id = NULL WHERE dodao_id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Greška pri nullificiranju nalazi.dodao_id: " . $e->getMessage());
        }
        
        // Nullificiraj u drugim tabelama ako postoje
        try {
            $stmt = $pdo->prepare("UPDATE raspored SET unosio_id = NULL WHERE unosio_id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Tabela možda ne postoji
        }
    }
    }
    
    // TERAPEUT - zamrzni podatke svugdje, čuvaj historiju
    if ($uloga === 'terapeut') {
        
        // 1. Zamrzni ime i prezime u tretmanima
        $stmt = $pdo->prepare("
            UPDATE tretmani 
            SET terapeut_ime = ?, terapeut_prezime = ?
            WHERE terapeut_id = ? AND (terapeut_ime IS NULL OR terapeut_ime = '')
        ");
        $stmt->execute([$korisnik['ime'], $korisnik['prezime'], $id]);
        
        // 2. Zamrzni ime i prezime u terminima
        $stmt = $pdo->prepare("
            UPDATE termini 
            SET terapeut_ime = ?, terapeut_prezime = ?
            WHERE terapeut_id = ? AND (terapeut_ime IS NULL OR terapeut_ime = '')
        ");
        $stmt->execute([$korisnik['ime'], $korisnik['prezime'], $id]);
        
        // 3. Obriši rasporede (ne čuvaju se istorijski)
        $stmt = $pdo->prepare("DELETE FROM rasporedi_sedmicni WHERE terapeut_id = ?");
        $stmt->execute([$id]);
        
        // 4. Obriši iz dodatnih raspored tabela ako postoje
        try {
            $stmt = $pdo->prepare("DELETE FROM raspored WHERE terapeut_id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Tabela možda ne postoji
        }
    }
    
    // ADMIN/RECEPCIONER - samo obriši iz users tabele
    // (nema dodatnih podataka za brisanje)
    
    // Obriši korisnika na kraju
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    // Potvrdi transakciju
    $pdo->commit();
    
    $_SESSION['success_message'] = "Korisnik " . htmlspecialchars($korisnik['ime'] . " " . $korisnik['prezime']) . " je uspješno obrisan.";
    
} catch (Exception $e) {
    // Poništi transakciju u slučaju greške
    $pdo->rollBack();
    
    // Logiraj detaljnu grešku
    error_log("Greška pri brisanju korisnika ID $id: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $_SESSION['error_message'] = "Greška pri brisanju: " . $e->getMessage();
}

// Redirect na ispravnu stranicu ovisno o ulozi
$redirect_map = [
    'pacijent' => '/profil/pacijent',
    'terapeut' => '/profil/terapeut',
    'recepcioner' => '/profil/recepcioner',
    'admin' => '/profil/admin'
];

$redirect = $redirect_map[$uloga] ?? '/profil/pacijent';
header("Location: $redirect");
exit;