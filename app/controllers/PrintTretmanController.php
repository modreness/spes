<?php
require_once __DIR__ . '/../helpers/load.php';
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

// Dohvati tretman + ime korisnika
$stmt = $pdo->prepare("
    SELECT t.*, u.ime AS unio_ime, u.prezime AS unio_prezime, k.broj_upisa, k.jmbg AS jmbg, p.ime AS pacijent_ime, p.prezime AS pacijent_prezime
    FROM tretmani t
    LEFT JOIN users u ON t.unio_id = u.id
    LEFT JOIN kartoni k ON t.karton_id = k.id
    LEFT JOIN users p ON k.pacijent_id = p.id
    WHERE t.id = ?
");
$stmt->execute([$tretman_id]);
$tretman = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tretman) {
    echo "Tretman nije pronađen";
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

