<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$pdo = db();
$user = current_user();

// Dozvoljene role
$dozvoljene = ['admin', 'recepcioner', 'terapeut'];
if (!in_array($user['uloga'], $dozvoljene)) {
    http_response_code(403);
    echo "Nemate pristup ovoj stranici.";
    exit;
}
$stmt = $pdo->query("
    SELECT k.id, k.jmbg, k.broj_upisa, k.datum_rodjenja, u.ime, u.prezime, u.email
    FROM kartoni k
    JOIN users u ON k.pacijent_id = u.id
    ORDER BY k.id DESC
");
$kartoni = $stmt->fetchAll();

// Dohvati sve terapeute
$terapeuti = $pdo->query("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' ORDER BY ime ASC")->fetchAll(PDO::FETCH_ASSOC);

ob_start();
require __DIR__ . '/../views/kartoni/lista.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';