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
    
    <input type="password" name="trenutna" id="stara_lozinka" placeholder="Unesite trenutnu lozinku" style="flex:1;">
    <button type="button" class="toggle-password" onclick="toggleStareLozinke()" aria-label="Prikaži/Sakrij lozinke">
      <i id="eye-icon-old" class="fa-solid fa-eye"></i>
    </button>
  </div>

  <div class="form-group icon-input">
    <label>Nova lozinka</label>
    
    <input type="password" name="nova" id="nova_lozinka" placeholder="Nova lozinka" style="flex:1;">
    <button type="button" class="toggle-password" onclick="toggleLozinke()" aria-label="Prikaži/Sakrij lozinke">
      <i id="eye-icon" class="fa-solid fa-eye"></i>
    </button>
  </div>

  <div class="form-group icon-input">
    <label>Ponovi novu lozinku</label>
    <input type="password" name="ponovi" id="ponovi_lozinku" placeholder="Ponovi lozinku">
  </div>

  <button type="submit" class="submit-button">Promijeni lozinku</button>
  <script>
    function toggleLozinke() {
      const inputs = [document.getElementById('nova_lozinka'), document.getElementById('ponovi_lozinku')];
      const icon = document.getElementById('eye-icon');
      const currentlyPassword = inputs[0].type === 'password';

      inputs.forEach(input => input.type = currentlyPassword ? 'text' : 'password');

      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    }

    function toggleStareLozinke() {
      const inputs = document.getElementById('stara_lozinka');
      const icon = document.getElementById('eye-icon-old');
      const currentlyPassword = inputs[0].type === 'password';

      inputs.forEach(input => input.type = currentlyPassword ? 'text' : 'password');

      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');

    }
  </script>
</form>
</div>
<div class="left-content">
    <?php include __DIR__ . '/partials/help-box.php'; ?>
</div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';
