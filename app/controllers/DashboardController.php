<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$user = current_user();
$title = "Dashboard";

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
