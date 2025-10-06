<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava - SPES</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>
<body>

<div class="login-container">
    <img src="/assets/images/spes-logo-slogan.svg" alt="Logo" class="logo">
    <!--<h2 class="subtitle">PRIVATNA PRAKSA</h2>-->
    <?php if (isset($_GET['msg'])): ?>
  <p style="background: #e0f7e9; color: #2e7d32; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
    <?= htmlspecialchars($_GET['msg']) ?>
  </p>
<?php endif; ?>

<!--PORUKE RESET-->
    <?php if (isset($_GET['reset'])):
  $reset = trim($_GET['reset']); ?>

  <?php if ($reset === 'uspjesan'): ?>
    <p style="background: #e0f7e9; color: #2e7d32; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
      ✅ Uspješno ste resetovali lozinku. Prijavite se u aplikaciju sa novom lozinkom.
    </p>

  <?php elseif ($reset === 'invalid'): ?>
    <p style="background: #fdecea; color: #c62828; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
      ❌ Link za reset lozinke je istekao.
    </p>

  <?php endif; ?>
<?php endif; ?>


    <!--END PORUKE-->

    <form action="/login" method="post">
        <div class="form-group icon-input">
            <span class="icon"><i class="fa-solid fa-user"></i></span>
            <input type="text" name="username" id="email" placeholder="Korisničko ime" required autofocus>
        </div>
        
        <div class="form-group icon-input">
            <span class="icon"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="lozinka" id="lozinka" placeholder="Password" required>
            <button type="button" class="toggle-password" onclick="toggleLozinka()" aria-label="Prikaži/Sakrij lozinku"><i id="eye-icon" class="fa-solid fa-eye"></i>️</button>
        </div>

        <div class="form-links">
            <a href="/zaboravljena-lozinka">Zaboravili ste password?</a>
            <!--<a href="#">Nemate korisnički račun?</a>-->
        </div>

        <button type="submit" class="login-button">Prijavi se</button>
        
        <?php if (!empty($error)): ?>
            <p class="login-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

    </form>
</div>

</body>
<script>
function toggleLozinka() {
  const input = document.getElementById('lozinka');
  const icon = document.getElementById('eye-icon');

  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
}
</script>
</html>
