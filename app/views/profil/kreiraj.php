<section class="section-box">
<h2>Kreiraj novi profil</h2>

<?php if ($poruka): ?>
  <div class="greska"><?= htmlspecialchars($poruka) ?></div>
<?php endif; ?>
<div class="main-content">
    <div class="right-content">
        <form method="post" class="form-standard">
          <div class="form-group">
            <label>Ime</label>
            <input type="text" name="ime" id="ime" required>
          </div>
        <div class="form-group">
            <label>Prezime</label>
            <input type="text" name="prezime" id="prezime" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email">
          </div>
          <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; color: #856404;">
              <i class="fa-solid fa-info-circle"></i> 
              <strong>Napomena:</strong> Email adresa nije obavezna, ali je potrebna za notifikacije i resetovanje lozinke. 
          </div>
        
          <div class="form-group">
              <label for="username">Korisničko ime</label>
              <input type="text" name="username" id="username" required>
              <small id="username-status" style="color: red; display: none;">Korisničko ime je zauzeto.</small>
        </div>

        
          <div class="form-group">
              <label>Lozinka</label>
              <div class="password-wrapper">
              <div class="lozinka-wrap">
                <input type="password" name="lozinka" id="lozinka" required style="flex: 1;">
                <button type="button" class="toggle-password" onclick="toggleLozinka()" aria-label="Prikaži/Sakrij lozinku"><i id="eye-icon" class="fa-solid fa-eye"></i>️</button>
                </div>
                <button type="button" onclick="generisiLozinku()" class="generator">Generiši</button>
              </div>
            </div>

          <div class="form-group">
            <label>Uloga</label>
            <select name="uloga" required>
              <?php if ($user['uloga'] === 'admin'): ?>
                <option value="admin">Admin</option>
                <option value="recepcioner">Recepcioner</option>
                <option value="terapeut">Terapeut</option>
                <option value="pacijent">Pacijent</option>
              <?php elseif ($user['uloga'] === 'recepcioner'): ?>
                <option value="terapeut">Terapeut</option>
                <option value="pacijent">Pacijent</option>
              <?php endif; ?>
            </select>
          </div>
        
          <button type="submit" class="submit-button">Kreiraj korisnika</button>
        </form>
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



function generisiLozinku() {
  const duzina = 12;
  const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
  let lozinka = "";
  for (let i = 0; i < duzina; i++) {
    const randomIndex = Math.floor(Math.random() * charset.length);
    lozinka += charset[randomIndex];
  }

  const input = document.getElementById('lozinka');
  input.value = lozinka;

  // Automatski kopiraj u clipboard
  input.select();
  input.setSelectionRange(0, 99999); // Za mobilne

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
document.addEventListener('DOMContentLoaded', function () {
  const imeInput = document.getElementById('ime');
  const prezimeInput = document.getElementById('prezime');
  const usernameInput = document.getElementById('username');
  const usernameStatus = document.getElementById('username-status');

  function slugify(text) {
    const map = {
      'č': 'c', 'ć': 'c', 'š': 's', 'đ': 'dj', 'ž': 'z',
      'Č': 'C', 'Ć': 'C', 'Š': 'S', 'Đ': 'Dj', 'Ž': 'Z'
    };

    return text
      .split('')
      .map(char => map[char] || char)
      .join('')
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '.')        // space → dot
      .replace(/[^\w.]+/g, '')     // remove non-word (except .)
      .replace(/\.+/g, '.');       // collapse multiple dots
  }

  function checkUsernameAvailability(username) {
    fetch('/provjeri-username?username=' + encodeURIComponent(username))
      .then(response => response.json())
      .then(data => {
        if (data.postoji) {
          usernameInput.style.borderColor = 'red';
          usernameStatus.style.display = 'inline';
        } else {
          usernameInput.style.borderColor = '';
          usernameStatus.style.display = 'none';
        }
      });
  }

  function generateUsername() {
    const ime = imeInput.value.trim();
    const prezime = prezimeInput.value.trim();
    if (!ime && !prezime) return;

    const combined = `${ime}.${prezime}`;
    const generated = slugify(combined);
    usernameInput.value = generated;
    checkUsernameAvailability(generated);
  }

  // Event listeneri
  imeInput.addEventListener('input', generateUsername);
  prezimeInput.addEventListener('input', generateUsername);

  usernameInput.addEventListener('input', function () {
    checkUsernameAvailability(this.value);
  });
});
</script>



    </div>
    <div class="left-content">
      <?php include __DIR__ . '/../partials/help-box.php'; ?>
    </div>
</div>
</section>