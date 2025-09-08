<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();

// Provjera POST podataka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['terapeut_id']) && isset($_POST['datum_od']) && isset($_POST['raspored'])) {

    $terapeut_id = (int) $_POST['terapeut_id'];
    $datum_od = $_POST['datum_od'];
    $unosio_id = current_user()['id'];
    $datum_unosa = date('Y-m-d H:i:s');

    // Pretvori datum_od u timestamp
    $start_date = strtotime($datum_od);

    // Loop kroz raspored po danima
    foreach ($_POST['raspored'] as $dan_key => $podatak) {

        $smjena = $podatak['smjena'];

        // Ako nije odabrana smjena (ne radi) preskoči dan
        if (empty($smjena)) {
            continue;
        }

        // Izračunaj datum za dan
        $dan_offset = array_search($dan_key, array_keys(dani()));
        $datum_dan = date('Y-m-d', strtotime("+$dan_offset days", $start_date));

        // Ubaci u bazu
        $stmt = $pdo->prepare("INSERT INTO rasporedi_sedmicni 
            (terapeut_id, datum_od, datum_do, dan, smjena, pocetak, kraj, unosio_id, datum_unosa)
            VALUES 
            (:terapeut_id, :datum_od, :datum_do, :dan, :smjena, :pocetak, :kraj, :unosio_id, :datum_unosa)");

        $stmt->execute([
            'terapeut_id' => $terapeut_id,
            'datum_od'    => $datum_dan,
            'datum_do'    => $datum_dan,
            'dan'         => $dan_key,
            'smjena'      => $smjena,
            'pocetak'     => null,
            'kraj'        => null,
            'unosio_id'   => $unosio_id,
            'datum_unosa' => $datum_unosa
        ]);
    }

    header("Location: /raspored?msg=dodan");
    exit;
}

// Dohvati terapeute za dropdown
try {
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $terapeuti = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju terapeuta: " . $e->getMessage());
    $terapeuti = [];
}

$title = "Dodaj raspored";

ob_start();
require_once __DIR__ . '/../views/raspored/dodaj.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>