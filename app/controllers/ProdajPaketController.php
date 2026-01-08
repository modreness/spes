<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();
$errors = [];

// POST - Prodaja paketa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pacijent_id = (int)($_POST['pacijent_id'] ?? 0);
    $usluga_id = (int)($_POST['usluga_id'] ?? 0);
    $datum_pocetka = $_POST['datum_pocetka'] ?? null;
    $datum_kraja = $_POST['datum_kraja'] ?? null;
    $napomena = trim($_POST['napomena'] ?? '');
    $placeno = isset($_POST['placeno']) ? 1 : 0;
    
    // Validacija
    if (empty($pacijent_id)) {
        $errors[] = 'Pacijent je obavezan.';
    }
    
    if (empty($usluga_id)) {
        $errors[] = 'Paket je obavezan.';
    }
    
    // Provjeri da li je odabrana usluga stvarno paket
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT tip_usluge, broj_termina FROM cjenovnik WHERE id = ? AND aktivan = 1");
            $stmt->execute([$usluga_id]);
            $usluga = $stmt->fetch();
            
            if (!$usluga || $usluga['tip_usluge'] !== 'paket') {
                $errors[] = 'Odabrana usluga nije paket.';
            } else {
                $ukupno_termina = $usluga['broj_termina'];
            }
        } catch (PDOException $e) {
            error_log("Greška: " . $e->getMessage());
            $errors[] = 'Greška pri provjeri paketa.';
        }
    }
    
    // Spremi u bazu
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO kupljeni_paketi 
                (pacijent_id, usluga_id, ukupno_termina, datum_pocetka, datum_kraja, napomena, placeno, kreirao_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aktivan')
            ");

            $stmt->execute([
                $pacijent_id,
                $usluga_id,
                $ukupno_termina,
                !empty($datum_pocetka) ? $datum_pocetka : null,
                !empty($datum_kraja) ? $datum_kraja : null,
                $napomena,
                $placeno,
                $user['id']
            ]);
            
            header('Location: /paketi?msg=prodat');
            exit;
            
        } catch (PDOException $e) {
            error_log("Greška pri prodaji paketa: " . $e->getMessage());
            $errors[] = 'Greška pri spremanju paketa.';
        }
    }
}

// GET - Prikaz forme
try {
    // Dohvati pacijente
    $stmt = $pdo->prepare("
        SELECT id, ime, prezime, email 
        FROM users 
        WHERE uloga = 'pacijent' AND aktivan = 1 
        ORDER BY ime, prezime
    ");
    $stmt->execute();
    $pacijenti = $stmt->fetchAll();
    
    // Dohvati pakete iz cjenovnika
    $stmt = $pdo->prepare("
        SELECT c.*, k.naziv as kategorija_naziv
        FROM cjenovnik c
        LEFT JOIN kategorije_usluga k ON c.kategorija_id = k.id
        WHERE c.tip_usluge = 'paket' AND c.aktivan = 1
        ORDER BY k.naziv, c.naziv
    ");
    $stmt->execute();
    $paketi = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška: " . $e->getMessage());
    $pacijenti = [];
    $paketi = [];
}

$title = "Prodaj paket";

ob_start();
require_once __DIR__ . '/../views/paketi/prodaj.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>