<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

$query = trim($_GET['q'] ?? '');
$tip = $_GET['tip'] ?? 'sve';

$pacijenti = [];
$kartoni = [];
$termini = [];

if (!empty($query) && strlen($query) >= 2) {
    try {
        // Pretraga pacijenata
        if ($tip === 'sve' || $tip === 'pacijenti') {
            $stmt = $pdo->prepare("
                SELECT id, ime, prezime, email, datum_kreiranja, aktivan
                FROM users 
                WHERE uloga = 'pacijent' 
                AND (
                    ime LIKE ? OR 
                    prezime LIKE ? OR 
                    email LIKE ? OR
                    CONCAT(ime, ' ', prezime) LIKE ?
                )
                ORDER BY ime, prezime
                LIMIT 20
            ");
            $search_term = "%$query%";
            $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
            $pacijenti = $stmt->fetchAll();
        }

        // Pretraga kartona
        if ($tip === 'sve' || $tip === 'kartoni') {
            $stmt = $pdo->prepare("
                SELECT k.*, CONCAT(u.ime, ' ', u.prezime) as pacijent_ime
                FROM kartoni k
                LEFT JOIN users u ON k.pacijent_id = u.id
                WHERE 
                    k.broj_upisa LIKE ? OR
                    k.jmbg LIKE ? OR
                    k.dijagnoza LIKE ? OR
                    k.anamneza LIKE ? OR
                    k.biljeske LIKE ? OR
                    CONCAT(u.ime, ' ', u.prezime) LIKE ?
                ORDER BY k.datum_otvaranja DESC
                LIMIT 20
            ");
            $search_term = "%$query%";
            $stmt->execute([$search_term, $search_term, $search_term, $search_term, $search_term, $search_term]);
            $kartoni = $stmt->fetchAll();
        }

        // Pretraga termina
        if ($tip === 'sve' || $tip === 'termini') {
            $stmt = $pdo->prepare("
                SELECT t.*,
                       CONCAT(u_pacijent.ime, ' ', u_pacijent.prezime) as pacijent_ime,
                       CONCAT(u_terapeut.ime, ' ', u_terapeut.prezime) as terapeut_ime,
                       c.naziv as usluga_naziv
                FROM termini t
                LEFT JOIN users u_pacijent ON t.pacijent_id = u_pacijent.id
                LEFT JOIN users u_terapeut ON t.terapeut_id = u_terapeut.id
                LEFT JOIN cjenovnik c ON t.usluga_id = c.id
                WHERE 
                    t.napomena LIKE ? OR
                    CONCAT(u_pacijent.ime, ' ', u_pacijent.prezime) LIKE ? OR
                    CONCAT(u_terapeut.ime, ' ', u_terapeut.prezime) LIKE ? OR
                    c.naziv LIKE ?
                ORDER BY t.datum_vrijeme DESC
                LIMIT 20
            ");
            $search_term = "%$query%";
            $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
            $termini = $stmt->fetchAll();
        }

    } catch (PDOException $e) {
        error_log("Greška pri pretraživanju: " . $e->getMessage());
    }
}

$title = "Pretraga";

ob_start();
require_once __DIR__ . '/../views/pretraga/rezultati.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>