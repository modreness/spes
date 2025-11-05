<?php
require_once __DIR__ . '/../helpers/load.php';
require_once __DIR__ . '/../helpers/permissions.php';
require_login();

require_once __DIR__ . '/../helpers/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$pdo = db();
$user = current_user();

$tretman_id = $_GET['id'] ?? null;

if (!$tretman_id) {
    http_response_code(400);
    echo "Nedostaje ID tretmana";
    exit;
}

// Dohvati tretman + karton + pacijent podatke
$stmt = $pdo->prepare("
    SELECT t.*, 
           u.ime AS unio_ime, u.prezime AS unio_prezime, 
           k.broj_upisa, k.jmbg AS jmbg, k.pacijent_id,
           p.ime AS pacijent_ime, p.prezime AS pacijent_prezime, 
           ter.ime AS terapeut_ime, ter.prezime AS terapeut_prezime
    FROM tretmani t
    LEFT JOIN users u ON t.unio_id = u.id
    LEFT JOIN kartoni k ON t.karton_id = k.id
    LEFT JOIN users p ON k.pacijent_id = p.id
    LEFT JOIN users ter ON t.terapeut_id = ter.id
    WHERE t.id = ?
");
$stmt->execute([$tretman_id]);
$tretman = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tretman) {
    echo "Tretman nije pronađen";
    exit;
}

// **PROVJERI PRISTUP OVISNO O ULOZI**
if ($user['uloga'] === 'pacijent') {
    // Pacijent može printirati samo svoje tretmane
    if (!hasPermission($user, 'print_vlastiti_podaci')) {
        http_response_code(403);
        echo "Nemate dozvolu za printiranje tretmana";
        exit;
    }
    
    if ($tretman['pacijent_id'] != $user['id']) {
        http_response_code(403);
        echo "Ne možete printirati tuji tretman";
        exit;
    }
} elseif ($user['uloga'] === 'terapeut') {
    // Terapeut može printirati tretmane svojih pacijenata
    $stmt = $pdo->prepare("
        SELECT 1 FROM termini 
        WHERE pacijent_id = ? AND terapeut_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$tretman['pacijent_id'], $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo "Ne možete printirati tretman koji nije vašeg pacijenta";
        exit;
    }
} elseif (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    // Ostale uloge nemaju pristup
    http_response_code(403);
    echo "Nemate dozvolu za printiranje tretmana";
    exit;
}

// Priprema HTML-a
ob_start();
require __DIR__ . '/../views/pdf/tretman.php';
$html = ob_get_clean();

// PDF opcije
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$ime = $tretman['pacijent_ime'] ?? '';
$prezime = $tretman['pacijent_prezime'] ?? '';
$jmbg = $tretman['jmbg'] ?? '';
$datum = date('d-m-Y', strtotime($tretman['datum']));

// Postavi title unutar PDF-a
$dompdf->addInfo('Title', "Tretman - $ime $prezime - $jmbg - $datum");
$dompdf->stream("Tretman-$ime-$prezime-$jmbg-$datum.pdf", ["Attachment" => false]);