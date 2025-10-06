<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/db.php';

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user = current_user();

if (!in_array($user['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

$action = $_GET['action'] ?? 'index';
$message = $_SESSION['message'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['message'], $_SESSION['error']);

// Lista dijagnoza
if ($action === 'index') {
    try {
        $search = $_GET['search'] ?? '';
        
        $sql = "SELECT * FROM dijagnoze WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (naziv LIKE ? OR opis LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY naziv ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $dijagnoze = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Greška pri dohvaćanju dijagnoza: " . $e->getMessage());
        $dijagnoze = [];
        $error = "Greška pri učitavanju dijagnoza.";
    }
    
    $title = "Dijagnoze";
    ob_start();
    require_once __DIR__ . '/../views/dijagnoze/lista.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../views/layout.php';
}

// Dodavanje nove dijagnoze
elseif ($action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $naziv = trim($_POST['naziv'] ?? '');
        $opis = trim($_POST['opis'] ?? '');
        
        if (empty($naziv)) {
            $_SESSION['error'] = "Naziv dijagnoze je obavezan.";
            header('Location: /dijagnoze?action=create');
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO dijagnoze (naziv, opis) VALUES (?, ?)");
            $stmt->execute([$naziv, $opis]);
            
            $_SESSION['message'] = "Dijagnoza uspješno dodana!";
            header('Location: /dijagnoze');
            exit;
            
        } catch (PDOException $e) {
            error_log("Greška pri dodavanju dijagnoze: " . $e->getMessage());
            $_SESSION['error'] = "Greška pri dodavanju dijagnoze.";
            header('Location: /dijagnoze?action=create');
            exit;
        }
    }
    
    $title = "Nova dijagnoza";
    ob_start();
    require_once __DIR__ . '/../views/dijagnoze/kreiraj.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../views/layout.php';
}

// Editovanje dijagnoze
elseif ($action === 'edit') {
    $id = $_GET['id'] ?? 0;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $naziv = trim($_POST['naziv'] ?? '');
        $opis = trim($_POST['opis'] ?? '');
        
        if (empty($naziv)) {
            $_SESSION['error'] = "Naziv dijagnoze je obavezan.";
            header("Location: /dijagnoze?action=edit&id=$id");
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE dijagnoze SET naziv = ?, opis = ? WHERE id = ?");
            $stmt->execute([$naziv, $opis, $id]);
            
            $_SESSION['message'] = "Dijagnoza uspješno ažurirana!";
            header('Location: /dijagnoze');
            exit;
            
        } catch (PDOException $e) {
            error_log("Greška pri ažuriranju dijagnoze: " . $e->getMessage());
            $_SESSION['error'] = "Greška pri ažuriranju dijagnoze.";
            header("Location: /dijagnoze?action=edit&id=$id");
            exit;
        }
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM dijagnoze WHERE id = ?");
        $stmt->execute([$id]);
        $dijagnoza = $stmt->fetch();
        
        if (!$dijagnoza) {
            $_SESSION['error'] = "Dijagnoza nije pronađena.";
            header('Location: /dijagnoze');
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("Greška pri dohvaćanju dijagnoze: " . $e->getMessage());
        $_SESSION['error'] = "Greška pri učitavanju dijagnoze.";
        header('Location: /dijagnoze');
        exit;
    }
    
    $title = "Uredi dijagnozu";
    ob_start();
    require_once __DIR__ . '/../views/dijagnoze/uredi.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../views/layout.php';
}

// Brisanje dijagnoze
elseif ($action === 'delete') {
    $id = $_GET['id'] ?? 0;
    
    try {
        // Provjeri da li se dijagnoza koristi u kartonima
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM karton_dijagnoze WHERE dijagnoza_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error'] = "Dijagnoza se ne može obrisati jer se koristi u $count kartona.";
            header('Location: /dijagnoze');
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM dijagnoze WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['message'] = "Dijagnoza uspješno obrisana!";
        
    } catch (PDOException $e) {
        error_log("Greška pri brisanju dijagnoze: " . $e->getMessage());
        $_SESSION['error'] = "Greška pri brisanju dijagnoze.";
    }
    
    header('Location: /dijagnoze');
    exit;
}

else {
    header('Location: /dijagnoze');
    exit;
}
?>