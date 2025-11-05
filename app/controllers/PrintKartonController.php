<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

require_once __DIR__ . '/../helpers/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$pdo = db();
$user = current_user();

$karton_id = $_GET['id'] ?? null;

if (!$karton_id) {
    http_response_code(400);
    echo "Nedostaje ID tretmana";
    exit;
}

// Dohvati tretman + ime korisnika
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

