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
    
    $dodano = 0;
    $preskoceno = 0;
    $greske = [];

    try {
        // Započni transakciju
        $pdo->beginTransaction();

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

            // PROVERI DA LI VEĆ POSTOJI - sprečavanje duplikata
            $check_stmt = $pdo->prepare("
                SELECT COUNT(*) FROM rasporedi_sedmicni 
                WHERE terapeut_id = ? AND datum_od = ? AND dan = ? AND smjena = ?
            ");
            $check_stmt->execute([$terapeut_id, $datum_dan, $dan_key, $smjena]);
            
            if ($check_stmt->fetchColumn() > 0) {
                $preskoceno++;
                $greske[] = "Terapeut već ima raspored za " . ucfirst($dan_key) . " (" . ucfirst($smjena) . ")";
                continue; // Preskoči ako već postoji
            }

            // Ubaci u bazu samo ako ne postoji
            $stmt = $pdo->prepare("INSERT INTO rasporedi_sedmicni 
                (terapeut_id, datum_od, datum_do, dan, smjena, pocetak, kraj, unosio_id, datum_unosa, aktivan)
                VALUES 
                (:terapeut_id, :datum_od, :datum_do, :dan, :smjena, :pocetak, :kraj, :unosio_id, :datum_unosa, 1)");

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
            
            $dodano++;
        }

        // Potvrdi transakciju
        $pdo->commit();
        
        // Pripremi poruku
        if ($dodano > 0 && $preskoceno > 0) {
            $msg = "dodano_delimicno&dodano=$dodano&preskoceno=$preskoceno";
        } elseif ($dodano > 0) {
            $msg = "dodan&dodano=$dodano";
        } elseif ($preskoceno > 0) {
            $msg = "duplikat&preskoceno=$preskoceno";
        } else {
            $msg = "nista";
        }
        
        header("Location: /raspored?msg=$msg");
        exit;
        
    } catch (PDOException $e) {
        // Vrati transakciju
        $pdo->rollBack();
        error_log("Greška pri dodavanju rasporeda: " . $e->getMessage());
        header("Location: /raspored/dodaj?msg=greska");
        exit;
    }
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