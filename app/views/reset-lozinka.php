<?php
$token = $_GET['token'] ?? '';
?>

<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SPES APP - Zaboravljena lozinka</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  
  
</head>
<body>

<div class="login-container">
  <img src="/assets/images/spes-logo-slogan.svg" alt="Logo" class="logo">

  <div class="lost-password">
    <h2>Unesite novu lozinku</h2>
    <?php if (!isset($_GET['token'])): ?>
      <p>Unesite svoju email adresu. Poslat ćemo vam link za reset lozinke.</p>
    <?php endif; ?>
  </div>

  <!--PORUKE-->
  <?php if (isset($_GET['msg'])):
    $msg = trim($_GET['msg']); ?>

    <?php if ($msg === 'reset-sent'): ?>
      <p style="background: #e0f7e9; color: #2e7d32; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
        ✅ Link za reset lozinke je poslan na email.
      </p>
    <?php elseif ($msg === 'reset-invalid'): ?>
      <p style="background: #fdecea; color: #c62828; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
        ❌ Email nije pronađen.
      </p>
    <?php elseif ($msg === 'expired'): ?>
      <p style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
        ❌ Link za reset lozinke je istekao.
      </p>
      <?php elseif ($msg === 'not-matching'): ?>
      <p style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
        Lozinke se ne podudaraju.
      </p>
    <?php endif; ?>
  <?php endif; ?>

  <form action="/spasi-novu-lozinku" method="post">
    <div class="form-group">
      <div class="password-wrapper">
        <div class="lozinka-wrap">
          <input type="password" name="lozinka" id="lozinka" required placeholder="Nova lozinka">
          <button type="button" class="toggle-password" onclick="toggleLozinke()" aria-label="Prikaži/Sakrij lozinke">
            <i id="eye-icon" class="fa-solid fa-eye"></i>
          </button>
        </div>
        <button type="button" onclick="generisiLozinku()" class="generator">Generiši</button>
      </div>
    </div>

    <div class="form-group">
      <input type="password" name="ponovi_lozinku" id="ponovi_lozinku" required placeholder="Ponovi lozinku">
    </div>

    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <div class="form-links">
      <a href="/login">Nazad na prijavu</a>
    </div>

    <button type="submit" class="login-button">Spremi lozinku</button>
    <div id="error-msg" style="display:none; margin-top:15px; background:#fdecea; color:#c62828; padding:10px; border-radius:6px;">
  ❌ Lozinke se ne poklapaju.
</div>

  </form>
</div>

<script>
function toggleLozinke() {
  const inputs = [document.getElementById('lozinka'), document.getElementById('ponovi_lozinku')];
  const icon = document.getElementById('eye-icon');
  const trenutno = inputs[0].type === 'password';

  inputs.forEach(el => el.type = trenutno ? 'text' : 'password');
  icon.classList.toggle('fa-eye');
  icon.classList.toggle('fa-eye-slash');
}

function generisiLozinku() {
  const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
  let lozinka = "";
  for (let i = 0; i < 12; i++) {
    lozinka += charset[Math.floor(Math.random() * charset.length)];
  }

  const nova = document.getElementById('lozinka');
  const ponovi = document.getElementById('ponovi_lozinku');
  nova.value = lozinka;
  ponovi.value = lozinka;

  nova.select();
  nova.setSelectionRange(0, 99999);

  try {
    document.execCommand('copy');
    prikaziNotifikaciju("Lozinka je kopirana u clipboard");
  } catch (err) {
    prikaziNotifikaciju("Greška pri kopiranju lozinke");
  }
}

function prikaziNotifikaciju(poruka) {
  let notifikacija = document.getElementById('notifikacija');
  if (!notifikacija) {
    notifikacija = document.createElement('div');
    notifikacija.id = 'notifikacija';
    notifikacija.style.position = 'fixed';
    notifikacija.style.bottom = '20px';
    notifikacija.style.right = '20px';
    notifikacija.style.background = '#2ecc71';
    notifikacija.style.color = 'white';
    notifikacija.style.padding = '10px 15px';
    notifikacija.style.borderRadius = '5px';
    notifikacija.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    document.body.appendChild(notifikacija);
  }

  notifikacija.textContent = poruka;
  notifikacija.style.display = 'block';

  setTimeout(() => {
    notifikacija.style.display = 'none';
  }, 3000);
}

</script>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
  const lozinka = document.getElementById('lozinka').value;
  const ponovi = document.getElementById('ponovi_lozinku').value;
  const errorBox = document.getElementById('error-msg');

  if (lozinka !== ponovi) {
    e.preventDefault();
    errorBox.style.display = 'block';

    setTimeout(() => {
      errorBox.style.display = 'none';
    }, 4000);
  }
});
</script>

</body>
</html>
