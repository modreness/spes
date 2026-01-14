<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

// Samo terapeut može videti svoje tretmane
if ($user['uloga'] !== 'terapeut') {
    header('Location: /dashboard');
    exit;
}

// Parametri za paginaciju i filtriranje
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$datum_od = $_GET['datum_od'] ?? '';
$datum_do = $_GET['datum_do'] ?? '';
$pacijent_id = $_GET['pacijent_id'] ?? '';

try {
    // Osnovni upit
    $where_conditions = ["tr.terapeut_id = ?"];
    $params = [$user['id']];
    
    // Dodaj filtere
    if ($datum_od) {
        $where_conditions[] = "tr.datum >= ?";
        $params[] = $datum_od;
    }
    
    if ($datum_do) {
        $where_conditions[] = "tr.datum <= ?";
        $params[] = $datum_do;
    }
    
    if ($pacijent_id) {
        $where_conditions[] = "k.pacijent_id = ?";
        $params[] = $pacijent_id;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Ukupan broj tretmana za paginaciju
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM tretmani tr
        JOIN kartoni k ON tr.karton_id = k.id
        WHERE $where_clause
    ");
    $stmt->execute($params);
    $ukupno_tretmana = $stmt->fetchColumn();
    $ukupno_stranica = ceil($ukupno_tretmana / $per_page);
    
    // Dohvati tretmane sa paginacijom
    $stmt = $pdo->prepare("
        SELECT tr.*, 
               CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
               k.broj_upisa,
               k.dijagnoza,
               DATE_FORMAT(tr.datum, '%d.%m.%Y') as datum_format,
               DATE_FORMAT(tr.datum_tretmana, '%d.%m.%Y') as datum_tretmana_format
        FROM tretmani tr
        JOIN kartoni k ON tr.karton_id = k.id
        JOIN users p ON k.pacijent_id = p.id
        WHERE $where_clause
        ORDER BY COALESCE(tr.datum_tretmana, tr.datum) DESC, tr.id DESC
        LIMIT $per_page OFFSET $offset
    ");
    $stmt->execute($params);
    $moji_tretmani = $stmt->fetchAll();
    
    // Statistike
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tretmani WHERE terapeut_id = ?");
    $stmt->execute([$user['id']]);
    $ukupno_svih_tretmana = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM tretmani 
        WHERE terapeut_id = ? AND MONTH(datum) = MONTH(CURDATE()) AND YEAR(datum) = YEAR(CURDATE())
    ");
    $stmt->execute([$user['id']]);
    $tretmani_ovaj_mesec = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT karton_id) FROM tretmani 
        WHERE terapeut_id = ?
    ");
    $stmt->execute([$user['id']]);
    $broj_pacijenata = $stmt->fetchColumn();
    
    // Lista pacijenata za filter dropdown
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.id, CONCAT(p.ime, ' ', p.prezime) as ime_prezime
        FROM tretmani tr
        JOIN kartoni k ON tr.karton_id = k.id
        JOIN users p ON k.pacijent_id = p.id
        WHERE tr.terapeut_id = ?
        ORDER BY p.ime, p.prezime
    ");
    $stmt->execute([$user['id']]);
    $pacijenti_lista = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju tretmana: " . $e->getMessage());
    $moji_tretmani = [];
    $ukupno_tretmana = 0;
    $ukupno_stranica = 0;
    $ukupno_svih_tretmana = 0;
    $tretmani_ovaj_mesec = 0;
    $broj_pacijenata = 0;
    $pacijenti_lista = [];
}

$title = "Moji tretmani";

ob_start();
require_once __DIR__ . '/../views/kartoni/tretmani-moji.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>