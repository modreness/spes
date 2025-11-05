<?php
require_once __DIR__ . '/../helpers/load.php';
require_once __DIR__ . '/../helpers/permissions.php';
require_login();

require_once __DIR__ . '/../helpers/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$pdo = db();
$user = current_user();

$karton_id = $_GET['id'] ?? null;

if (!$karton_id) {
    http_response_code(400);
    echo "Nedostaje ID kartona";
    exit;
}

// Dohvati karton
$stmt = $pdo->prepare("
    SELECT k.*, u.ime, u.prezime, u.email
    FROM kartoni k
    JOIN users u ON k.pacijent_id = u.id
    WHERE k.id = ?
");
$stmt->execute([$karton_id]);
$karton = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$karton) {
    echo "Karton nije pronađen";
    exit;
}

// **PROVJERI PRISTUP OVISNO O ULOZI**
if ($user['uloga'] === 'pacijent') {
    // Pacijent može printirati samo svoj karton
    if (!hasPermission($user, 'print_vlastiti_podaci')) {
        http_response_code(403);
        echo "Nemate dozvolu za printiranje ovog kartona";
        exit;
    }
    
    if ($karton['pacijent_id'] != $user['id']) {
        http_response_code(403);
        echo "Ne možete printirati tuji karton";
        exit;
    }
} elseif ($user['uloga'] === 'terapeut') {
    // Terapeut može printirati kartone svojih pacijenata
    $stmt = $pdo->prepare("
        SELECT 1 FROM termini 
        WHERE pacijent_id = ? AND terapeut_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$karton['pacijent_id'], $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo "Ne možete printirati karton koji nije vaš pacijent";
        exit;
    }
} elseif (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    // Ostale uloge nemaju pristup
    http_response_code(403);
    echo "Nemate dozvolu za printiranje kartona";
    exit;
}

// Priprema HTML-a
ob_start();
require __DIR__ . '/../views/pdf/karton.php';
$html = ob_get_clean();

// PDF opcije
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Karton_' . $karton['ime'] . '_' . $karton['prezime'] . '_' . $karton['jmbg'] . '.pdf';

// Postavi title unutar PDF-a
$dompdf->addInfo('Title', $filename);
$dompdf->stream($filename, ["Attachment" => false]);