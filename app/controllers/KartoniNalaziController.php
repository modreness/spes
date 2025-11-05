<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$pdo = db();
$user = current_user();
$karton_id = $_GET['id'] ?? null;

if (!$karton_id) {
    header('Location: /kartoni/lista');
    exit;
}

// Dohvati podatke o kartonu i pacijentu
$stmt = $pdo->prepare("
    SELECT k.*, u.ime, u.prezime, u.email, u.id as pacijent_id
    FROM kartoni k 
    JOIN users u ON k.pacijent_id = u.id 
    WHERE k.id = ?
");
$stmt->execute([$karton_id]);
$karton = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$karton) {
    header('Location: /kartoni/lista?msg=nema');
    exit;
}

// Dohvati nalaze za ovog pacijenta
$stmt = $pdo->prepare("
    SELECT n.*, 
           CONCAT(d.ime, ' ', d.prezime) as dodao_ime,
           d.ime as dodao_ime_kratko,
           d.prezime as dodao_prezime
    FROM nalazi n
    LEFT JOIN users d ON n.dodao_id = d.id
    WHERE n.pacijent_id = ?
    ORDER BY n.datum_upload DESC
");
$stmt->execute([$karton['pacijent_id']]);
$nalazi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle POST requests za upload, edit, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'upload':
            handleUpload($pdo, $karton, $user);
            break;
        case 'delete':
            handleDelete($pdo, $_POST['nalaz_id'], $karton_id);
            break;
        case 'edit':
            handleEdit($pdo, $_POST, $karton_id);
            break;
    }
}

function handleUpload($pdo, $karton, $user) {
    if (!isset($_FILES['nalaz_file']) || $_FILES['nalaz_file']['error'] !== UPLOAD_ERR_OK) {
        header("Location: /kartoni/nalazi?id={$karton['id']}&msg=upload-greska");
        exit;
    }
    
    $file = $_FILES['nalaz_file'];
    $naziv = $_POST['naziv'] ?? '';
    $opis = $_POST['opis'] ?? '';
    
    // Validacija file type
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_types)) {
        header("Location: /kartoni/nalazi?id={$karton['id']}&msg=tip-greska");
        exit;
    }
    
    // Validacija veličine fajla (10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        header("Location: /kartoni/nalazi?id={$karton['id']}&msg=velicina-greska");
        exit;
    }
    
    // Kreiranje folder strukture: pacijent_id + kratki hash
    $patient_hash = substr(md5($karton['pacijent_id'] . $karton['jmbg']), 0, 8);
    $folder_name = $karton['pacijent_id'] . '_' . $patient_hash;
    
    $upload_dir = __DIR__ . '/../../uploads/nalazi/' . $folder_name . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generiranje sigurnog imena fajla
    $clean_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $naziv);
    $clean_filename = substr($clean_filename, 0, 30); // ograniči dužinu
    $new_filename = date('Ymd_His') . '_' . $clean_filename . '.' . $file_ext;
    $file_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Spašavanje u bazu - relativna putanja
        $relative_path = 'uploads/nalazi/' . $folder_name . '/' . $new_filename;
        
        $stmt = $pdo->prepare("
            INSERT INTO nalazi (pacijent_id, naziv, opis, file_path, dodao_id, datum_upload) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $karton['pacijent_id'],
            $naziv,
            $opis,
            $relative_path,
            $user['id']
        ]);
        
        header("Location: /kartoni/nalazi?id={$karton['id']}&msg=upload-ok");
    } else {
        header("Location: /kartoni/nalazi?id={$karton['id']}&msg=upload-greska");
    }
    exit;
}

function handleDelete($pdo, $nalaz_id, $karton_id) {
    // Dohvati file path pre brisanja
    $stmt = $pdo->prepare("SELECT file_path FROM nalazi WHERE id = ?");
    $stmt->execute([$nalaz_id]);
    $nalaz = $stmt->fetch();
    
    if ($nalaz) {
        // Obriši fajl sa disk-a
        $file_path = __DIR__ . '/../../' . $nalaz['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Obriši iz baze
        $stmt = $pdo->prepare("DELETE FROM nalazi WHERE id = ?");
        $stmt->execute([$nalaz_id]);
        
        header("Location: /kartoni/nalazi?id=$karton_id&msg=obrisan");
    } else {
        header("Location: /kartoni/nalazi?id=$karton_id&msg=greska");
    }
    exit;
}

function handleEdit($pdo, $data, $karton_id) {
    $stmt = $pdo->prepare("UPDATE nalazi SET naziv = ?, opis = ? WHERE id = ?");
    $stmt->execute([$data['naziv'], $data['opis'], $data['nalaz_id']]);
    
    header("Location: /kartoni/nalazi?id=$karton_id&msg=azuriran");
    exit;
}

$title = "Nalazi pacijenta - " . $karton['ime'] . " " . $karton['prezime'];

ob_start();
require __DIR__ . '/../views/kartoni/nalazi.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';