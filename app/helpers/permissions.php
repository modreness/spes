<?php
/**
 * Proverava da li korisnik ima određenu dozvolu
 * 
 * @param array $user Korisnik iz sesije
 * @param string $permission_name Naziv dozvole
 * @return bool True ako ima dozvolu, false inače
 */
function hasPermission($user, $permission_name) {
    global $pdo;
    
    // Admin uvek ima sve dozvole
    if ($user['uloga'] === 'admin') {
        return true;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT enabled FROM role_permissions 
            WHERE uloga = ? AND permission_name = ?
        ");
        $stmt->execute([$user['uloga'], $permission_name]);
        $result = $stmt->fetch();
        
        // Ako ne postoji dozvola u tabeli, podrazumevano je false
        return $result ? (bool)$result['enabled'] : false;
        
    } catch (PDOException $e) {
        error_log("Greška pri proveri dozvole: " . $e->getMessage());
        return false;
    }
}

/**
 * Dohvata sve dozvole za određenu ulogu
 * 
 * @param string $uloga Uloga korisnika
 * @return array Asocijativni niz dozvola
 */
function getRolePermissions($uloga) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT permission_name, enabled 
            FROM role_permissions 
            WHERE uloga = ?
            ORDER BY permission_name
        ");
        $stmt->execute([$uloga]);
        
        $permissions = [];
        while ($row = $stmt->fetch()) {
            $permissions[$row['permission_name']] = (bool)$row['enabled'];
        }
        
        return $permissions;
        
    } catch (PDOException $e) {
        error_log("Greška pri dohvaćanju dozvola: " . $e->getMessage());
        return [];
    }
}

/**
 * Ažurira dozvolu za ulogu
 * 
 * @param string $uloga Uloga korisnika
 * @param string $permission_name Naziv dozvole
 * @param bool $enabled Da li je dozvola omogućena
 * @return bool True ako je uspešno, false inače
 */
function updatePermission($uloga, $permission_name, $enabled) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO role_permissions (uloga, permission_name, enabled) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE enabled = VALUES(enabled)
        ");
        
        return $stmt->execute([$uloga, $permission_name, $enabled ? 1 : 0]);
        
    } catch (PDOException $e) {
        error_log("Greška pri ažuriranju dozvole: " . $e->getMessage());
        return false;
    }
}

/**
 * Lista svih dostupnih dozvola
 * 
 * @return array Lista naziva dozvola sa opisima
 */
function getAvailablePermissions() {
    return [
        'unos_tretmana' => 'Unošenje tretmana pacijenata',
        'upload_nalazi' => 'Upload nalaza i dokumenata',
        'brisanje_podataka' => 'Brisanje kartona, tretmana i nalaza',
        'kreiranje_korisnika' => 'Kreiranje novih korisnika',
        'pristup_izvjestajima' => 'Pristup finansijskim i medicinskim izvještajima',
        'upravljanje_terminima' => 'Zakazivanje i otkazivanje termina',
        'pregled_svih_kartona' => 'Pristup svim kartonima pacijenata'
    ];
}

/**
 * Proverava da li korisnik može pristupiti određenoj ruti
 * 
 * @param array $user Korisnik
 * @param string $route Ruta (npr. "/kartoni/uredi")
 * @return bool True ako može pristupiti
 */
function canAccessRoute($user, $route) {
    // Mapiranje ruta na dozvole
    $route_permissions = [
        '/kartoni/dodaj-tretman' => 'unos_tretmana',
        '/kartoni/uredi-tretman' => 'unos_tretmana',
        '/kartoni/obrisitretman' => 'brisanje_podataka',
        '/kartoni/obrisi' => 'brisanje_podataka',
        '/kartoni/nalazi' => 'upload_nalazi',
        '/profil/kreiraj' => 'kreiranje_korisnika',
        '/izvjestaji' => 'pristup_izvjestajima',
        '/termini/novi' => 'upravljanje_terminima',
        '/termini/uredi' => 'upravljanje_terminima'
    ];
    
    // Admini mogu pristupiti svemu
    if ($user['uloga'] === 'admin') {
        return true;
    }
    
    // Proveri da li ruta zahteva posebnu dozvolu
    foreach ($route_permissions as $pattern => $permission) {
        if (strpos($route, $pattern) === 0) {
            return hasPermission($user, $permission);
        }
    }
    
    // Podrazumevano dozvolja pristup
    return true;
}

/**
 * Middleware funkcija za proveru dozvola sa lepom error stranicom
 * Koristi se na vrhu kontrolera
 */
function requirePermission($permission_name) {
    if (!is_logged_in()) {
        header('Location: /login');
        exit;
    }
    
    $user = current_user();
    if (!hasPermission($user, $permission_name)) {
        http_response_code(403);
        
        // Proslijedi podatke za error stranicu
        $required_permission = $permission_name;
        
        // Prikaži lepu error stranicu
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
}

/**
 * Kratka funkcija za brzu proveru sa redirect-om
 */
function denyAccessWithMessage($message = null, $permission_name = null) {
    if (!is_logged_in()) {
        header('Location: /login');
        exit;
    }
    
    $user = current_user();
    $required_permission = $permission_name;
    
    http_response_code(403);
    require __DIR__ . '/../views/errors/403.php';
    exit;
}