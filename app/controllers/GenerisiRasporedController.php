<?php
require_once __DIR__ . '/../helpers/load.php';

require_login();

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$pdo = db();

// AJAX: Dohvati zadnju smjenu terapeuta
if (isset($_GET['ajax']) && $_GET['ajax'] === 'zadnja_smjena') {
    header('Content-Type: application/json');
    $terapeut_id = (int)($_GET['terapeut_id'] ?? 0);
    
    if (!$terapeut_id) {
        echo json_encode(['success' => false]);
        exit;
    }
    
    try {
        // Pronađi zadnju smjenu terapeuta
        $stmt = $pdo->prepare("
            SELECT smjena, datum_od, dan 
            FROM rasporedi_sedmicni 
            WHERE terapeut_id = ? 
            ORDER BY datum_od DESC, FIELD(dan, 'ned','sub','pet','cet','sri','uto','pon') 
            LIMIT 1
        ");
        $stmt->execute([$terapeut_id]);
        $zadnja = $stmt->fetch();
        
        if ($zadnja) {
            // Predloži suprotnu smjenu
            $predlozena = ($zadnja['smjena'] === 'jutro') ? 'vecer' : 'jutro';
            echo json_encode([
                'success' => true,
                'zadnja_smjena' => $zadnja['smjena'],
                'predlozena_smjena' => $predlozena,
                'zadnji_datum' => $zadnja['datum_od']
            ]);
        } else {
            // Nema prethodnih rasporeda
            echo json_encode([
                'success' => true,
                'zadnja_smjena' => null,
                'predlozena_smjena' => 'jutro',
                'zadnji_datum' => null
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// POST: Generiranje rasporeda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $terapeut_id = (int)($_POST['terapeut_id'] ?? 0);
    $datum_od = $_POST['datum_od'] ?? '';
    $broj_sedmica = (int)($_POST['broj_sedmica'] ?? 26);
    $radni_dani = $_POST['radni_dani'] ?? [];
    $pocetna_smjena = $_POST['pocetna_smjena'] ?? 'jutro';
    
    $errors = [];
    
    // Validacija
    if (!$terapeut_id) {
        $errors[] = 'Terapeut je obavezan.';
    }
    if (empty($datum_od)) {
        $errors[] = 'Početni datum je obavezan.';
    }
    if ($broj_sedmica < 1 || $broj_sedmica > 52) {
        $errors[] = 'Broj sedmica mora biti između 1 i 52.';
    }
    if (empty($radni_dani)) {
        $errors[] = 'Odaberite barem jedan radni dan.';
    }
    if (!in_array($pocetna_smjena, ['jutro', 'vecer'])) {
        $errors[] = 'Nevažeća smjena.';
    }
    
    // Provjeri da li je datum ponedjeljak
    if (!empty($datum_od) && date('N', strtotime($datum_od)) != 1) {
        $errors[] = 'Početni datum mora biti ponedjeljak.';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Dohvati podatke terapeuta za zamrzavanje
            $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
            $stmt->execute([$terapeut_id]);
            $terapeut = $stmt->fetch();
            
            if (!$terapeut) {
                throw new Exception('Terapeut nije pronađen.');
            }
            
            $unosio_id = $user['id'];
            $unosio_ime = $user['ime'];
            $unosio_prezime = $user['prezime'];
            $datum_unosa = date('Y-m-d H:i:s');
            
            $dodano = 0;
            $preskoceno = 0;
            $trenutna_smjena = $pocetna_smjena;
            
            // Loop kroz sedmice
            for ($sedmica = 0; $sedmica < $broj_sedmica; $sedmica++) {
                $sedmica_pocetak = date('Y-m-d', strtotime("+$sedmica weeks", strtotime($datum_od)));
                $sedmica_kraj = date('Y-m-d', strtotime('sunday', strtotime($sedmica_pocetak)));
                
                // Dohvati vremena za smjenu
                $stmt_smjena = $pdo->prepare("SELECT pocetak, kraj FROM smjene_vremena WHERE smjena = ? AND aktivan = 1");
                $stmt_smjena->execute([$trenutna_smjena]);
                $smjena_vremena = $stmt_smjena->fetch();
                
                $pocetak = $smjena_vremena ? $smjena_vremena['pocetak'] : null;
                $kraj = $smjena_vremena ? $smjena_vremena['kraj'] : null;
                
                // Loop kroz odabrane dane
                foreach ($radni_dani as $dan) {
                    // Izračunaj datum za taj dan
                    $dan_offset = array_search($dan, array_keys(dani()));
                    $datum_dan = date('Y-m-d', strtotime("+$dan_offset days", strtotime($sedmica_pocetak)));
                    
                    // Provjeri da li već postoji
                    $check_stmt = $pdo->prepare("
                        SELECT COUNT(*) FROM rasporedi_sedmicni 
                        WHERE terapeut_id = ? 
                        AND datum_od = ?
                        AND dan = ?
                    ");
                    $check_stmt->execute([$terapeut_id, $sedmica_pocetak, $dan]);
                    
                    if ($check_stmt->fetchColumn() > 0) {
                        $preskoceno++;
                        continue;
                    }
                    
                    // Ubaci raspored
                    $stmt = $pdo->prepare("
                        INSERT INTO rasporedi_sedmicni 
                        (terapeut_id, datum_od, datum_do, dan, smjena, pocetak, kraj, 
                         unosio_id, datum_unosa, terapeut_ime, terapeut_prezime, unosio_ime, unosio_prezime)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $terapeut_id,
                        $sedmica_pocetak,
                        $sedmica_kraj,
                        $dan,
                        $trenutna_smjena,
                        $pocetak,
                        $kraj,
                        $unosio_id,
                        $datum_unosa,
                        $terapeut['ime'],
                        $terapeut['prezime'],
                        $unosio_ime,
                        $unosio_prezime
                    ]);
                    $dodano++;
                }
                
                // Rotiraj smjenu za sljedeću sedmicu
                $trenutna_smjena = ($trenutna_smjena === 'jutro') ? 'vecer' : 'jutro';
            }
            
            $pdo->commit();
            
            // Redirect sa porukom
            if ($dodano > 0 && $preskoceno > 0) {
                $msg = "generisano_djelimicno&dodano=$dodano&preskoceno=$preskoceno";
            } elseif ($dodano > 0) {
                $msg = "generisano&dodano=$dodano&sedmica=$broj_sedmica";
            } else {
                $msg = "vec_postoji&preskoceno=$preskoceno";
            }
            
            header("Location: /raspored?msg=$msg");
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Greška pri generiranju rasporeda: " . $e->getMessage());
            $errors[] = 'Greška pri generiranju rasporeda: ' . $e->getMessage();
        }
    }
}

// Dohvati terapeute za dropdown
try {
    $stmt = $pdo->prepare("SELECT id, ime, prezime FROM users WHERE uloga = 'terapeut' AND aktivan = 1 ORDER BY ime, prezime");
    $stmt->execute();
    $terapeuti = $stmt->fetchAll();
} catch (PDOException $e) {
    $terapeuti = [];
}

$title = "Generiši raspored automatski";

ob_start();
require_once __DIR__ . '/../views/raspored/generisi.php';
$content = ob_get_clean();

require_once __DIR__ . '/../views/layout.php';
?>