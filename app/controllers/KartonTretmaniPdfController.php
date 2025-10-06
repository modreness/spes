<?php
require_once __DIR__ . '/../helpers/load.php';
require_once __DIR__ . '/../helpers/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_login();

$pdo = db();

$karton_id = $_GET['id'] ?? null;
if (!$karton_id || !is_numeric($karton_id)) {
    exit('Neispravan ID.');
}

// Dohvati podatke o pacijentu
$stmt = $pdo->prepare("
    SELECT k.*, u.ime, u.prezime, k.jmbg 
    FROM kartoni k 
    JOIN users u ON k.pacijent_id = u.id 
    WHERE k.id = ?
");
$stmt->execute([$karton_id]);
$karton = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$karton) {
    exit('Karton nije pronađen.');
}

// Dohvati sve tretmane
$stmt = $pdo->prepare("
    SELECT t.*, u.ime AS unio_ime, u.prezime AS unio_prezime, ter.ime AS terapeut_ime, ter.prezime  AS terapeut_prezime,
    FROM tretmani t
    LEFT JOIN users u ON t.unio_id = u.id
    LEFT JOIN users AS ter  ON t.terapeut_id = ter.id
    WHERE t.karton_id = ?
    ORDER BY t.datum DESC
");
$stmt->execute([$karton_id]);
$tretmani = $stmt->fetchAll(PDO::FETCH_ASSOC);

// HTML sadrzaj za PDF
ob_start();
require __DIR__ . '/../views/pdf/tretmani.php';
$html = ob_get_clean();

// DOMPDF konfiguracija
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'landscape');
$dompdf->loadHtml($html);
$dompdf->render();

// Naziv fajla
$filename = 'tretmani_' . $karton['ime'] . '_' . $karton['prezime'] . '_' . $karton['jmbg'] . '.pdf';

$dompdf->stream($filename, ['Attachment' => false]);
exit;




