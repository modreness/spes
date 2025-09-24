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
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM kartoni WHERE DATE(datum_otvaranja) = CURDATE()");
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
        
        // Termini danas - SVI termini (ne samo zakazani)
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
                   c.naziv as usluga,
                   TIME(t.datum_vrijeme) as vrijeme,
                   k.id as karton_id
            FROM termini t
            JOIN users p ON t.pacijent_id = p.id
            JOIN users te ON t.terapeut_id = te.id
            JOIN cjenovnik c ON t.usluga_id = c.id
            LEFT JOIN kartoni k ON k.pacijent_id = t.pacijent_id
            WHERE DATE(t.datum_vrijeme) = CURDATE()
            ORDER BY t.datum_vrijeme ASC
            LIMIT 10
        ");
        $stmt->execute();
        $dashboard_data['predstojeci_termini'] = $stmt->fetchAll();
        
        // Nedavni kartoni (popravljeno)
        $stmt = $pdo->prepare("
            SELECT k.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   CONCAT(o.ime, ' ', o.prezime) as otvorio_ime
            FROM kartoni k
            JOIN users p ON k.pacijent_id = p.id
            JOIN users o ON k.otvorio_id = o.id
            ORDER BY k.datum_otvaranja DESC
            LIMIT 5
        ");
        $stmt->execute();
        $dashboard_data['nedavni_kartoni'] = $stmt->fetchAll();
        
        // Poslednji uploadovani nalazi
        $stmt = $pdo->prepare("
            SELECT n.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   CONCAT(d.ime, ' ', d.prezime) as dodao_ime
            FROM nalazi n
            JOIN users p ON n.pacijent_id = p.id
            JOIN users d ON n.dodao_id = d.id
            ORDER BY n.datum_upload DESC
            LIMIT 5
        ");
        $stmt->execute();
        $dashboard_data['poslednji_nalazi'] = $stmt->fetchAll();
        
    } elseif ($user['uloga'] === 'recepcioner') {
        // Recepcioner podaci - pristup gotovo svim podacima kao admin
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE uloga = 'pacijent'");
        $stmt->execute();
        $dashboard_data['ukupno_pacijenata'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE DATE(datum_vrijeme) = CURDATE()");
        $stmt->execute();
        $dashboard_data['broj_termina_danas'] = $stmt->fetchColumn();
        
        // Termini danas - isti kao admin
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
                   c.naziv as usluga,
                   TIME(t.datum_vrijeme) as vrijeme,
                   k.id as karton_id
            FROM termini t
            JOIN users p ON t.pacijent_id = p.id
            JOIN users te ON t.terapeut_id = te.id
            JOIN cjenovnik c ON t.usluga_id = c.id
            LEFT JOIN kartoni k ON k.pacijent_id = t.pacijent_id
            WHERE DATE(t.datum_vrijeme) = CURDATE()
            ORDER BY t.datum_vrijeme ASC
        ");
        $stmt->execute();
        $dashboard_data['termini_danas'] = $stmt->fetchAll();
        
        // Nedavni kartoni - isti kao admin
        $stmt = $pdo->prepare("
            SELECT k.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   CONCAT(o.ime, ' ', o.prezime) as otvorio_ime
            FROM kartoni k
            JOIN users p ON k.pacijent_id = p.id
            JOIN users o ON k.otvorio_id = o.id
            ORDER BY k.datum_otvaranja DESC
            LIMIT 5
        ");
        $stmt->execute();
        $dashboard_data['nedavni_kartoni'] = $stmt->fetchAll();
        
        // Poslednji uploadovani nalazi
        $stmt = $pdo->prepare("
            SELECT n.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   CONCAT(d.ime, ' ', d.prezime) as dodao_ime
            FROM nalazi n
            JOIN users p ON n.pacijent_id = p.id
            JOIN users d ON n.dodao_id = d.id
            ORDER BY n.datum_upload DESC
            LIMIT 5
        ");
        $stmt->execute();
        $dashboard_data['poslednji_nalazi'] = $stmt->fetchAll();
        
    } elseif ($user['uloga'] === 'terapeut') {
        // Terapeut podaci - moji termini, moji pacijenti
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   c.naziv as usluga,
                   TIME(t.datum_vrijeme) as vrijeme,
                   DATE(t.datum_vrijeme) as datum,
                   k.id as karton_id
            FROM termini t
            JOIN users p ON t.pacijent_id = p.id
            JOIN cjenovnik c ON t.usluga_id = c.id
            LEFT JOIN kartoni k ON k.pacijent_id = t.pacijent_id
            WHERE t.terapeut_id = ? AND DATE(t.datum_vrijeme) = CURDATE()
            ORDER BY t.datum_vrijeme ASC
        ");
        $stmt->execute([$user['id']]);
        $dashboard_data['moji_termini_danas'] = $stmt->fetchAll();
        
        // Termini ove sedmice
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   c.naziv as usluga,
                   DATE(t.datum_vrijeme) as datum,
                   TIME(t.datum_vrijeme) as vrijeme
            FROM termini t
            JOIN users p ON t.pacijent_id = p.id
            JOIN cjenovnik c ON t.usluga_id = c.id
            WHERE t.terapeut_id = ? 
            AND YEARWEEK(t.datum_vrijeme, 1) = YEARWEEK(CURDATE(), 1)
            AND t.status IN ('zakazan', 'obavljen')
            ORDER BY t.datum_vrijeme ASC
            LIMIT 10
        ");
        $stmt->execute([$user['id']]);
        $dashboard_data['termini_sedmica'] = $stmt->fetchAll();
        
        // Moji pacijenti - oni sa kojima imam aktivne kartone
        $stmt = $pdo->prepare("
            SELECT DISTINCT k.*, 
                   CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
                   p.id as pacijent_id,
                   COUNT(tr.id) as broj_tretmana,
                   MAX(tr.datum) as poslednji_tretman
            FROM kartoni k
            JOIN users p ON k.pacijent_id = p.id
            LEFT JOIN tretmani tr ON tr.karton_id = k.id AND tr.terapeut_id = ?
            WHERE EXISTS (
                SELECT 1 FROM termini t 
                WHERE t.pacijent_id = p.id AND t.terapeut_id = ?
            )
            GROUP BY k.id, p.id
            ORDER BY MAX(tr.datum) DESC, k.datum_otvaranja DESC
            LIMIT 8
        ");
        $stmt->execute([$user['id'], $user['id']]);
        $dashboard_data['moji_pacijenti'] = $stmt->fetchAll();
        
        // Statistike
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE terapeut_id = ? AND DATE(datum_vrijeme) = CURDATE()");
        $stmt->execute([$user['id']]);
        $dashboard_data['broj_termina_danas'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT k.pacijent_id) 
            FROM kartoni k 
            WHERE EXISTS (
                SELECT 1 FROM termini t 
                WHERE t.pacijent_id = k.pacijent_id AND t.terapeut_id = ?
            )
        ");
        $stmt->execute([$user['id']]);
        $dashboard_data['broj_mojih_pacijenata'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE terapeut_id = ? AND YEARWEEK(datum_vrijeme, 1) = YEARWEEK(CURDATE(), 1)");
        $stmt->execute([$user['id']]);
        $dashboard_data['termini_ova_sedmica'] = $stmt->fetchColumn();
        
    } elseif ($user['uloga'] === 'pacijent') {
        // Pacijent podaci - moji termini, moj karton, moji nalazi
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
        
        // Moj aktivan karton
        $stmt = $pdo->prepare("SELECT * FROM kartoni WHERE pacijent_id = ? ORDER BY datum_otvaranja DESC LIMIT 1");
        $stmt->execute([$user['id']]);
        $dashboard_data['aktivan_karton'] = $stmt->fetch();
        
        // Poslednji tretmani
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   CONCAT(ter.ime, ' ', ter.prezime) as terapeut_ime
            FROM tretmani t
            LEFT JOIN users ter ON t.terapeut_id = ter.id
            WHERE EXISTS (
                SELECT 1 FROM kartoni k 
                WHERE k.id = t.karton_id AND k.pacijent_id = ?
            )
            ORDER BY t.datum DESC
            LIMIT 3
        ");
        $stmt->execute([$user['id']]);
        $dashboard_data['poslednji_tretmani'] = $stmt->fetchAll();
        
        // Moji nalazi
        $stmt = $pdo->prepare("
            SELECT n.*, 
                   CONCAT(d.ime, ' ', d.prezime) as dodao_ime
            FROM nalazi n
            LEFT JOIN users d ON n.dodao_id = d.id
            WHERE n.pacijent_id = ?
            ORDER BY n.datum_upload DESC
            LIMIT 3
        ");
        $stmt->execute([$user['id']]);
        $dashboard_data['moji_nalazi'] = $stmt->fetchAll();
        
        // Statistike
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE pacijent_id = ? AND status = 'obavljen'");
        $stmt->execute([$user['id']]);
        $dashboard_data['ukupno_tretmana'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE pacijent_id = ? AND datum_vrijeme >= CURDATE()");
        $stmt->execute([$user['id']]);
        $dashboard_data['predstojeci_termini_broj'] = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM nalazi WHERE pacijent_id = ?");
        $stmt->execute([$user['id']]);
        $dashboard_data['broj_nalaza'] = $stmt->fetchColumn();
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