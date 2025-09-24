<?php


require_once __DIR__ . '/../app/helpers/load.php'; 

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?');     // skida query string
$uri = rtrim($uri, '/');      // skida završni slash

// Debug privremeno
// echo "<pre>URI: $uri</pre>"; exit;

if ($uri === '' || $uri === '/') {
    if (is_logged_in()) {
        $user = current_user();
        header('Location: /dashboard');
        exit;
    } else {
        header('Location: /login');
        exit;
    }


} elseif ($uri === '/login') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';

} 

elseif ($uri === '/dashboard') {
    require_once __DIR__ . '/../app/controllers/DashboardController.php';
}
elseif ($uri === '/profil/lozinka') {
    require_once __DIR__ . '/../app/views/lozinka.php';
}
elseif ($uri === '/logout') {
    session_destroy();
    header('Location: /login');
    exit;
}
elseif ($uri === '/zaboravljena-lozinka') {
    require_once __DIR__ . '/../app/views/zaboravljena-lozinka.php';
}
elseif ($uri === '/posalji-reset-link') {
    require_once __DIR__ . '/../app/controllers/PasswordResetController.php';
}
elseif ($uri === '/reset-lozinke') {
    require_once __DIR__ . '/../app/views/reset-lozinka.php';
}
elseif ($uri === '/spasi-novu-lozinku') {
    require_once __DIR__ . '/../app/controllers/ResetSaveController.php';
}
elseif ($uri === '/profil/uredi') {
    require_once __DIR__ . '/../app/controllers/UrediProfilController.php';
}
elseif ($uri === '/profil/obrisi') {
    require_once __DIR__ . '/../app/controllers/ProfilObrisiController.php';
}
elseif (
    $uri === '/profil/pacijent' ||
    $uri === '/profil/terapeut' ||
    $uri === '/profil/recepcioner' ||
    $uri === '/profil/admin'
) {
    require_once __DIR__ . '/../app/controllers/ProfilPregledController.php';
}
elseif ($uri === '/profil/kreiraj') {
    require_once __DIR__ . '/../app/controllers/KreirajProfilController.php';
}
elseif ($uri === '/provjeri-username') {
    require_once __DIR__ . '/../app/controllers/ProvjeriUsernameController.php';
}
elseif ($uri === '/kartoni/kreiraj') {
    require_once __DIR__ . '/../app/controllers/KreirajKartonController.php';
}
elseif ($uri === '/kartoni/lista') {
    require_once __DIR__ . '/../app/controllers/KartoniController.php';
}
elseif ($uri === '/kartoni/obrisi') {
    require_once __DIR__ . '/../app/controllers/ObrisiKartonController.php';
}
elseif ($uri === '/kartoni/dodaj-tretman') {
    require_once __DIR__ . '/../app/controllers/DodajTretmanController.php';
}
elseif ($uri === '/kartoni/pregled' && isset($_GET['id'])) {
    require_once __DIR__ . '/../app/controllers/PregledKartonController.php';
}
elseif ($uri === '/kartoni/uredi') {
    require_once __DIR__ . '/../app/controllers/KartonUrediController.php';
}
elseif ($uri === '/kartoni/update') {
    require_once __DIR__ . '/../app/controllers/UpdateKartonController.php';
}
elseif ($uri === '/kartoni/tretmani') {
    require_once __DIR__ . '/../app/controllers/TretmaniKartonaController.php';
}
elseif ($uri === '/kartoni/obrisitretman') {
    require_once __DIR__ . '/../app/controllers/ObrisiTretmanController.php';
}
elseif ($uri === '/kartoni/uredi-tretman') {
    require_once __DIR__ . '/../app/controllers/UrediTretmanController.php';
}
elseif ($uri === '/kartoni/print-tretman' && isset($_GET['id'])) {
    require_once __DIR__ . '/../app/controllers/PrintTretmanController.php';
}
elseif ($uri === '/kartoni/print-karton' && isset($_GET['id'])) {
    require_once __DIR__ . '/../app/controllers/PrintKartonController.php';
}
elseif ($uri === '/kartoni/print-tretmani' && isset($_GET['id'])) {
    require_once __DIR__ . '/../app/controllers/KartonTretmaniPdfController.php';
}
elseif ($uri === '/kategorije') {
    require_once __DIR__ . '/../app/controllers/KategorijeController.php';
}
elseif ($uri === '/kategorije/kreiraj') {
    require_once __DIR__ . '/../app/controllers/KreirajKategorijuController.php';
}
elseif ($uri === '/kategorije/uredi') {
    require_once __DIR__ . '/../app/controllers/UrediKategorijuController.php';
}
elseif ($uri === '/kategorije/obrisi') {
    require_once __DIR__ . '/../app/controllers/ObrisiKategorijuController.php';
}
elseif ($uri === '/cjenovnik') {
    require_once __DIR__ . '/../app/controllers/CjenovnikController.php';
}
elseif ($uri === '/cjenovnik/kreiraj') {
    require_once __DIR__ . '/../app/controllers/KreirajUsluguController.php';
}
elseif ($uri === '/cjenovnik/uredi') {
    require_once __DIR__ . '/../app/controllers/UrediUsluguController.php';
}
elseif ($uri === '/cjenovnik/obrisi') {
    require_once __DIR__ . '/../app/controllers/ObrisiUsluguController.php';
}
elseif ($uri === '/raspored') {
    require_once __DIR__ . '/../app/controllers/RasporedController.php';
}
elseif ($uri === '/raspored/dodaj') {
    require_once __DIR__ . '/../app/controllers/DodajRasporedController.php';
}
elseif ($uri === '/raspored/pregled') {
    require_once __DIR__ . '/../app/controllers/PregledRasporedaController.php';
}
elseif ($uri === '/raspored/uredi') {
    require __DIR__ . '/../app/controllers/RasporedUrediController.php';
}
elseif ($uri === '/timetable') {
    require_once __DIR__ . '/../app/controllers/TimetableController.php';
}
elseif ($uri === '/timetable/uredi') {
    require_once __DIR__ . '/../app/controllers/UrediTimetableController.php';
}
elseif ($uri === '/termini') {
    require_once __DIR__ . '/../app/controllers/TerminiController.php';
}
elseif ($uri === '/termini/kalendar') {
    require_once __DIR__ . '/../app/controllers/KalendarController.php';
}
elseif ($uri === '/termini/kreiraj') {
    require_once __DIR__ . '/../app/controllers/KreirajTerminController.php';
}
elseif ($uri === '/termini/uredi') {
    require_once __DIR__ . '/../app/controllers/UrediTerminController.php';
}
elseif ($uri === '/termini/lista') {
    require_once __DIR__ . '/../app/controllers/ListaTerminaController.php';
}
elseif ($uri === '/termini/status') {
    require_once __DIR__ . '/../app/controllers/StatusTerminaController.php';
}
elseif ($uri === '/termini/obrisi') {
    require_once __DIR__ . '/../app/controllers/ObrisiTerminController.php';
}
elseif ($uri === '/pretraga') {
    require_once __DIR__ . '/../app/controllers/PretragaController.php';
}
elseif ($uri === '/izvjestaji') {
    require_once __DIR__ . '/../app/controllers/IzvjestajiController.php';
}
elseif ($uri === '/izvjestaji/finansijski') {
    require_once __DIR__ . '/../app/controllers/FinansijskiIzvjestajController.php';
}
elseif ($uri === '/izvjestaji/operativni') {
    require_once __DIR__ . '/../app/controllers/OperativniIzvjestajController.php';
}
elseif ($uri === '/izvjestaji/medicinski') {
    require_once __DIR__ . '/../app/controllers/MedicinskiIzvjestajController.php';
}
elseif ($uri === '/kartoni/nalazi') {
    require_once __DIR__ . '/../app/controllers/KartoniNalaziController.php';
}
elseif ($uri === '/admin/dozvole') {
    require_once __DIR__ . '/../app/controllers/AdminPermissionsController.php';
}
/*
else {
    http_response_code(404);
    echo "Stranica nije pronađena.";
}
*/
else {
    http_response_code(404);
    require_once __DIR__ . '/../app/views/404.php';
}


