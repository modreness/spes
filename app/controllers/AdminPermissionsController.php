<?php
require_once __DIR__ . '/../helpers/load.php';
require_once __DIR__ . '/../helpers/permissions.php';

// Proveri da li je korisnik admin
require_login();
$user = current_user();

if ($user['uloga'] !== 'admin') {
    header('HTTP/1.0 403 Forbidden');
    die('Nemate dozvolu za pristup ovoj stranici.');
}

$pdo = db();

// Handle POST requests za ažuriranje dozvola
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_permissions') {
        $success_count = 0;
        $error_count = 0;
        
        foreach (['recepcioner', 'terapeut', 'pacijent'] as $uloga) {
            $permissions = getAvailablePermissions();
            
            foreach (array_keys($permissions) as $permission_name) {
                $enabled = isset($_POST[$uloga][$permission_name]) ? 1 : 0;
                
                if (updatePermission($uloga, $permission_name, $enabled)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        
        if ($error_count === 0) {
            header('Location: /admin/dozvole?msg=success');
        } else {
            header('Location: /admin/dozvole?msg=partial_error');
        }
        exit;
    }
}

// Dohvati trenutne dozvole za sve uloge
$current_permissions = [];
foreach (['recepcioner', 'terapeut', 'pacijent'] as $uloga) {
    $current_permissions[$uloga] = getRolePermissions($uloga);
}

$available_permissions = getAvailablePermissions();
$title = "Upravljanje dozvolama";

ob_start();
require __DIR__ . '/../views/admin/dozvole.php';
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';