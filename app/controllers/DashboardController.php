<?php 
require_once __DIR__ . '/../helpers/load.php'; 
require_login();  

$user = current_user(); 
$title = "Dashboard";  

// Dohvati podatke specifične po ulogama
$dashboard_data = [];

try {
    if ($user['uloga'] === 'admin') {
        // Admin statistike
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE uloga = 'pacijent'");
        $stmt->execute();
        $dashboard_data['ukupno_pacijenata'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE uloga = 'terapeut'");
        $stmt->execute();
        $dashboard_data['ukupno_terapeuta'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE DATE(datum_vrijeme) = CURDATE()");
        $stmt->execute();
        $dashboard_data['termini_danas'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM kartoni WHERE DATE(datum_kreiranja) = CURDATE()");
        $stmt->execute();
        $dashboard_data['novi_kartoni_danas'] = $stmt->fetchColumn();
        
        // Finansijski podaci
        $stmt = $pdo->prepare("
            SELECT SUM(c.cijena) 
            FROM termini t 
            JOIN cjenovnik c ON t.usluga_id = c.id 
            WHERE DATE(t.datum_vrijeme) = CURDATE() AND t.status = 'obavljen'
        ");
        $stmt->execute();
        $dashboard_data['prihod_danas'] = $stmt->fetchColumn() ?: 0;
        
        $stmt = $pdo->prepare("
            SELECT SUM(c.cijena) 
            FROM termini t 
            JOIN cjenovnik c ON t.usluga_id = c.id 
            WHERE MONTH(t.datum_vrijeme) = MONTH(CURDATE()) 
            AND YEAR(t.datum_vrijeme) = YEAR(CURDATE()) 
            AND t.status = 'obavljen'
        ");
        $stmt->execute();
        $dashboard_data['prihod_mesec'] = $stmt->fetchColumn() ?: 0;
        
        // Najaktivniji terapeut ovaj mesec
        $stmt = $pdo->prepare("
            SELECT CONCAT(u.ime, ' ', u.prezime) as ime, COUNT(*) as broj_termina
            FROM termini t
            JOIN users u ON t.terapeut_id = u.id
            WHERE MONTH(t.datum_vrijeme) = MONTH(CURDATE()) 
            AND YEAR(t.datum_vrijeme) = YEAR(CURDATE())
            AND t.status = 'obavljen'
            GROUP BY t.terapeut_id
            ORDER BY broj_termina DESC
            LIMIT 1
        ");
        $stmt->execute();
        $dashboard_data['top_terapeut'] = $stmt->fetch();
        
        // Predstojeci termini danas
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
                   c.naziv as usluga,
                   TIME(t.datum_vrijeme) as vrijeme
            FROM termini t
            JOIN users p ON t.pacijent_id = p.id
            JOIN users te ON t.terapeut_id = te.id
            JOIN cjenovnik c ON t.usluga_id = c.id
            WHERE DATE(t.datum_vrijeme) = CURDATE()
            AND t.status = 'zakazan'
            ORDER BY t.datum_vrijeme ASC
            LIMIT 10
        ");
        $stmt->execute();
        $dashboard_data['predstojeci_termini'] = $stmt->fetchAll();
        
        // Nedavni kartoni
        $stmt = $pdo->prepare("
            SELECT k.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   CONCAT(t.ime, ' ', t.prezime) as terapeut_ime
            FROM kartoni k
            JOIN users p ON k.pacijent_id = p.id
            JOIN users t ON k.terapeut_id = t.id
            ORDER BY k.datum_kreiranja DESC
            LIMIT 5
        ");
        $stmt->execute();
        $dashboard_data['nedavni_kartoni'] = $stmt->fetchAll();
        
    } elseif ($user['uloga'] === 'recepcioner') {
        // Recepcioner podaci - termini danas, zakazivanje, pacijenti
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
                   c.naziv as usluga,
                   TIME(t.datum_vrijeme) as vrijeme
            FROM termini t
            JOIN users p ON t.pacijent_id = p.id
            JOIN users te ON t.terapeut_id = te.id
            JOIN cjenovnik c ON t.usluga_id = c.id
            WHERE DATE(t.datum_vrijeme) = CURDATE()
            ORDER BY t.datum_vrijeme ASC
        ");
        $stmt->execute();
        $dashboard_data['termini_danas'] = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE DATE(datum_vrijeme) = CURDATE() AND status = 'zakazan'");
        $stmt->execute();
        $dashboard_data['broj_termina_danas'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE uloga = 'pacijent'");
        $stmt->execute();
        $dashboard_data['ukupno_pacijenata'] = $stmt->fetchColumn();
        
    } elseif ($user['uloga'] === 'terapeut') {
        // Terapeut podaci - moji termini, moji pacijenti
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   c.naziv as usluga,
                   TIME(t.datum_vrijeme) as vrijeme
            FROM termini t
            JOIN users p ON t.pacijent_id = p.id
            JOIN cjenovnik c ON t.usluga_id = c.id
            WHERE t.terapeut_id = ? AND DATE(t.datum_vrijeme) = CURDATE()
            ORDER BY t.datum_vrijeme ASC
        ");
        $stmt->execute([$user['id']]);
        $dashboard_data['moji_termini_danas'] = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT k.pacijent_id) 
            FROM kartoni k 
            WHERE k.terapeut_id = ?
        ");
        $stmt->execute([$user['id']]);
        $dashboard_data['broj_mojih_pacijenata'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE terapeut_id = ? AND DATE(datum_vrijeme) = CURDATE()");
        $stmt->execute([$user['id']]);
        $dashboard_data['broj_termina_danas'] = $stmt->fetchColumn();
        
    } elseif ($user['uloga'] === 'pacijent') {
        // Pacijent podaci - moji termini, moj karton
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
                   c.naziv as usluga,
                   DATE(t.datum_vrijeme) as datum,
                   TIME(t.datum_vrijeme) as vrijeme
            FROM termini t
            JOIN users te ON t.terapeut_id = te.id
            JOIN cjenovnik c ON t.usluga_id = c.id
            WHERE t.pacijent_id = ? AND t.datum_vrijeme >= CURDATE()
            ORDER BY t.datum_vrijeme ASC
            LIMIT 5
        ");
        $stmt->execute([$user['id']]);
        $dashboard_data['predstojeci_termini'] = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT * FROM kartoni WHERE pacijent_id = ? ORDER BY datum_kreiranja DESC LIMIT 1");
        $stmt->execute([$user['id']]);
        $dashboard_data['aktivan_karton'] = $stmt->fetch();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE pacijent_id = ? AND status = 'obavljen'");
        $stmt->execute([$user['id']]);
        $dashboard_data['ukupno_tretmana'] = $stmt->fetchColumn();
    }
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju dashboard podataka: " . $e->getMessage());
    $dashboard_data = [];
}

ob_start();  

switch ($user['uloga']) {   
    case 'admin':     
        require __DIR__ . '/../views/dashboard/admin.php';     
        break;   
    case 'recepcioner':     
        require __DIR__ . '/../views/dashboard/recepcioner.php';     
        break;   
    case 'terapeut':     
        require __DIR__ . '/../views/dashboard/terapeut.php';     
        break;   
    case 'pacijent':     
        require __DIR__ . '/../views/dashboard/pacijent.php';     
        break;   
    default:     
        echo "Nepoznata uloga korisnika.";     
        break; 
}  

$content = ob_get_clean(); 
require __DIR__ . '/../views/layout.php';