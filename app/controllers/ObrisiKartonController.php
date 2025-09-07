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
    $karton_id = intval($_POST['id']);

    $pdo = db();

    // Prvo obriši sve tretmane vezane za taj karton
    $stmt = $pdo->prepare("DELETE FROM tretmani WHERE karton_id = ?");
    $stmt->execute([$karton_id]);

    // Zatim obriši sam karton
    $stmt = $pdo->prepare("DELETE FROM kartoni WHERE id = ?");
    $stmt->execute([$karton_id]);

    // Redirekcija sa porukom
    header('Location: /kartoni/lista?msg=obrisan');
    exit;
}

// Ako nije POST
http_response_code(400);
echo "Neispravan zahtjev.";
