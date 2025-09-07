<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();

$pdo = db();
$user = current_user();

$poruka = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trenutna = $_POST['trenutna'] ?? '';
    $nova = $_POST['nova'] ?? '';
    $ponovi = $_POST['ponovi'] ?? '';

    if (empty($trenutna) || empty($nova) || empty($ponovi)) {
        $poruka = 'Sva polja su obavezna.';
    } elseif (!password_verify($trenutna, $user['lozinka'])) {
        $poruka = 'Trenutna lozinka nije tačna.';
    } elseif ($nova !== $ponovi) {
        $poruka = 'Nove lozinke se ne podudaraju.';
    } else {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET lozinka = :lozinka WHERE id = :id");
        $stmt->execute([
            'lozinka' => $hash,
            'id' => $user['id']
        ]);
        session_destroy();
        header('Location: /login?msg=Lozinka+uspje%C5%A1no+promijenjena.+Prijavite+se+ponovo.');
        exit;

    }
}

$title = "Promijeni lozinku";
ob_start();
?>
<h2>Promjena lozinke</h2>
<?php if ($poruka): ?>
  <div class="greska">
    <?= $poruka ?>
  </div>
<?php endif; ?>
<div class="main-content">
    <div class="right-content">
<form method="post">
  <div class="form-group icon-input">
    <label>Trenutna lozinka</label>
    
    <input type="password" name="trenutna" placeholder="Unesite trenutnu lozinku">
  </div>

  <div class="form-group icon-input">
    <label>Nova lozinka</label>
    
    <input type="password" name="nova" placeholder="Nova lozinka">
  </div>

  <div class="form-group icon-input">
    <label>Ponovi novu lozinku</label>
    <input type="password" name="ponovi" placeholder="Ponovi lozinku">
  </div>

  <button type="submit" class="submit-button">Promijeni lozinku</button>
</form>
</div>
<div class="left-content">
    <?php include __DIR__ . '/partials/help-box.php'; ?>
</div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';
