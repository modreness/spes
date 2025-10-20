<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$logovani = current_user();

// Dozvola samo za admina i recepcionera
if (!in_array($logovani['uloga'], ['admin', 'recepcioner'])) {
    header('Location: /dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tretman_id'])) {
    $tretman_id = intval($_POST['tretman_id']);
    $karton_id = intval($_POST['karton_id'] ?? 0);
    $stanje_prije = trim($_POST['stanje_prije'] ?? '');
    $terapija = trim($_POST['terapija'] ?? '');
    $stanje_poslije = trim($_POST['stanje_poslije'] ?? '');
    $terapeut_id = intval($_POST['terapeut_id'] ?? 0);

    // Validacija (osnovna)
    if ($karton_id <= 0 || empty($terapija)) {
        header('Location: /kartoni/tretmani?id=' . $karton_id . '&msg=tretman-greska');
        exit;
    }

    $pdo = db();

    // 👉 Učitaj podatke o PACIJENTU iz kartona
    $stmt = $pdo->prepare("
        SELECT k.pacijent_id, u.ime as pacijent_ime, u.prezime as pacijent_prezime
        FROM kartoni k
        JOIN users u ON k.pacijent_id = u.id
        WHERE k.id = ?
    ");
    $stmt->execute([$karton_id]);
    $karton_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dohvati terapeuta ako postoji
    $terapeut_ime = null;
    $terapeut_prezime = null;
    if ($terapeut_id > 0) {
        $stmt = $pdo->prepare("SELECT ime, prezime FROM users WHERE id = ?");
        $stmt->execute([$terapeut_id]);
        $terapeut = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($terapeut) {
            $terapeut_ime = $terapeut['ime'];
            $terapeut_prezime = $terapeut['prezime'];
        }
    }

    // 👉 Ažuriraj tretman SA ZAMRZNUTIM PODACIMA (bez pacijent_id)
    $stmt = $pdo->prepare("
        UPDATE tretmani
        SET stanje_prije = ?, 
            terapija = ?, 
            stanje_poslije = ?,
            pacijent_ime = ?,
            pacijent_prezime = ?,
            terapeut_id = ?, 
            terapeut_ime = ?, 
            terapeut_prezime = ?
        WHERE id = ?
    ");

    $success = $stmt->execute([
        $stanje_prije,
        $terapija,
        $stanje_poslije,
        $karton_data['pacijent_ime'],      // 👈 Ažuriraj zamrznuto ime pacijenta
        $karton_data['pacijent_prezime'],  // 👈 Ažuriraj zamrznuto prezime pacijenta
        $terapeut_id,
        $terapeut_ime,                     // 👈 Ažuriraj zamrznuto ime terapeuta
        $terapeut_prezime,                 // 👈 Ažuriraj zamrznuto prezime terapeuta
        $tretman_id
    ]);

    if ($success) {
        header('Location: /kartoni/tretmani?id=' . $karton_id . '&msg=tretman-ureden');
    } else {
        header('Location: /kartoni/tretmani?id=' . $karton_id . '&msg=tretman-greska');
    }

    exit;
}

// Ako nije POST
http_response_code(400);
echo "Neispravan zahtjev.";