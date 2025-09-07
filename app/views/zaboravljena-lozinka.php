<?php require_once __DIR__ . '/../helpers/load.php'; ?>

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
    <!--<h2 class="subtitle">PRIVATNA PRAKSA</h2>-->
    <div class="lost-password">
        <h2>Zaboravljena lozinka</h2>
        <p>Unesite svoju email adresu. Poslat ćemo vam link za reset lozinke.</p>
    </div>
    
    <!--PORUKE-->
    <?php if (isset($_GET['msg'])):
  $msg = trim($_GET['msg']); ?>

  <?php if ($msg === 'reset-sent'): ?>
    <p style="background: #e0f7e9; color: #2e7d32; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
      ✅ Link za reset lozinke je poslan na email. Pratite upute iz email-a.
    </p>

  <?php elseif ($msg === 'reset-invalid'): ?>
    <p style="background: #fdecea; color: #c62828; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
      ❌ Email nije pronađen u sistemu.
    </p>

  <?php elseif ($msg === 'expired'): ?>
    <p style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
      ❌ Link za reset lozinke je istekao.
    </p>

  <?php endif; ?>
<?php endif; ?>


    <!--END PORUKE-->
    
    <form action="/posalji-reset-link" method="post">
        <div class="form-group icon-input">
            <span class="icon"><i class="fa-solid fa-user"></i></span>
            <input type="email" name="email" id="email" placeholder="Email" required autofocus>
        </div>
        

        <div class="form-links">
            <a href="/login">Nazad na prijavu</a>
            <!--<a href="#">Nemate korisnički račun?</a>-->
        </div>

        <button type="submit" class="login-button">Pošalji link za reset</button>

    </form>
</div>

</body>
</html>
