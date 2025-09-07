<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$logovani = current_user();

// Dozvola samo za admina i recepcionera
if (!in_array($logovani['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $tretman_id = intval($_POST['id']);
    $karton_id = intval($_POST['id_kartona']);

    $pdo = db();

    // Prvo obriši sve tretmane vezane za taj karton
    $stmt = $pdo->prepare("DELETE FROM tretmani WHERE id = ?");
    $stmt->execute([$tretman_id]);

    

    // Redirekcija sa porukom
    header('Location: /kartoni/tretmani?id=' . $karton_id . '&msg=obrisan');

    exit;
}

// Ako nije POST
http_response_code(400);
echo "Neispravan zahtjev.";
