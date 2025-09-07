
<section class="section-box">
  <h2>Uredi profil</h2>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="alert alert-success">
      ✅ Profil je uspješno ažuriran.
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'reset-sent'): ?>
    <div class="alert alert-success">Mail za reset lozinke je poslan korisniku.</div>
<?php endif; ?>

<div class="main-content">
    <div class="right-content">
  <form action="/profil/uredi?id=<?= $korisnik['id'] ?>" method="post" enctype="multipart/form-data" class="form-standard">

      <input type="hidden" name="id" value="<?= $korisnik['id'] ?>">

    <div class="form-group">
      <label for="ime">Ime</label>
      <input type="text" name="ime" id="ime" value="<?= htmlspecialchars($korisnik['ime'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label for="prezime">Prezime</label>
      <input type="text" name="prezime" id="prezime" value="<?= htmlspecialchars($korisnik['prezime'] ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" name="email" id="email" value="<?= htmlspecialchars($korisnik['email'] ?? '') ?>" required>
    </div>
    
    <?php if ($logovani['uloga'] === 'admin' && $logovani['id'] != $korisnik['id']): ?>
      <div class="form-group">
          <label>Nova lozinka</label>
          <div class="password-wrapper">
            <div class="lozinka-wrap">
              <input type="password" name="nova_lozinka" id="nova_lozinka" style="flex: 1;">
              <button type="button" class="toggle-password" onclick="toggleLozinke()" aria-label="Prikaži/Sakrij lozinke">
                <i id="eye-icon" class="fa-solid fa-eye"></i>
              </button>
            </div>
            <button type="button" onclick="generisiLozinku()" class="generator">Generiši</button>
          </div>
        </div>
        
        <div class="form-group">
          <label>Ponovi lozinku</label>
          <input type="password" name="ponovi_lozinku" id="ponovi_lozinku">
        </div>

    <?php endif; ?>

    
    <?php if (($korisnik['id'] !== $logovani['id']) && $logovani['uloga'] === 'admin'): ?>
      <div class="form-group">
        <label for="uloga">Uloga</label>
        <select name="uloga" id="uloga">
          <option value="pacijent" <?= $korisnik['uloga'] === 'pacijent' ? 'selected' : '' ?>>Pacijent</option>
          <option value="terapeut" <?= $korisnik['uloga'] === 'terapeut' ? 'selected' : '' ?>>Terapeut</option>
          <option value="recepcioner" <?= $korisnik['uloga'] === 'recepcioner' ? 'selected' : '' ?>>Recepcioner</option>
          <option value="admin" <?= $korisnik['uloga'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
        </select>
      </div>
    <?php endif; ?>





<div class="form-group">
    <?php if (!empty($korisnik['slika'])): ?>
  <div class="trenutna-slika">
    <p>Trenutna profilna slika:</p>
    <img src="/uploads/profilne/<?= htmlspecialchars($korisnik['slika']) ?>" alt="Profilna" style="max-width:100px; border-radius:50%;">
    <div class="form-group obrisi-trenutnu">
      <input type="checkbox" name="obrisi_sliku"><p>Obriši trenutnu sliku</p>
    </div>
  </div>
<?php endif; ?>

  <label>Nova profilna slika</label>

  <button type="button" id="uploadBtn" class="button-secondary">Odaberi sliku</button>
  <input type="file" name="profilna" id="profilna" accept="image/*" style="display:none;">

  <div id="filePreview" style="margin-top:10px; display:none;">
    <strong>Odabrana slika:</strong> <span id="fileName"></span><br>
    <img id="previewImage" src="#" alt="Preview" style="max-width:120px; margin-top:10px; border-radius:10px; display:none;">
  </div>

  <div id="progress-container" style="display: none; margin-top:10px;">
    <div class="progress-bar" style="background:#2e7d32; height:6px; width:0%; border-radius:4px;"></div>
    <div class="progress-text" style="font-size: 14px; margin-top: 5px;">0%</div>
    <div id="loading-indicator" style="display: none; text-align: center; margin-top: 10px;">
      <span>Molimo pričekajte još par trenutaka...</span>
      <div class="spinner" style="width:20px; height:20px; border:3px solid #ccc; border-top-color:#2e7d32; border-radius:50%; animation: spin 1s linear infinite; margin: 5px auto;"></div>
    </div>
  </div>

  <div id="notification" style="margin-top:10px;"></div>
</div>

    <button type="submit" class="submit-button">Snimi promjene</button>
  </form>
  <form method="post" action="/profil/uredi?id=<?= $korisnik['id'] ?>" style="margin-top: 15px;">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <button type="submit" name="posalji_reset" class="btn-reset-pw">Pošalji mail za reset lozinke</button>
    </form>
  </div>
  <div class="left-content">
      <?php include __DIR__ . '/../partials/help-box.php'; ?>
    </div>
    </div>
</section>
<script>
function posaljiResetLink() {
  if (confirm('Želite li poslati link za reset lozinke ovom korisniku?')) {
    fetch('/posalji-reset-link?id=<?= $korisnik['id'] ?>')
      .then(response => {
        if (response.ok) {
          alert('Link je poslan.');
        } else {
          alert('Greška pri slanju.');
        }
      });
  }
}
</script>

<script>
function toggleLozinke() {
  const inputs = [document.getElementById('nova_lozinka'), document.getElementById('ponovi_lozinku')];
  const icon = document.getElementById('eye-icon');
  const currentlyPassword = inputs[0].type === 'password';

  inputs.forEach(input => input.type = currentlyPassword ? 'text' : 'password');

  icon.classList.toggle('fa-eye');
  icon.classList.toggle('fa-eye-slash');
}

function generisiLozinku() {
  const duzina = 12;
  const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
  let lozinka = "";
  for (let i = 0; i < duzina; i++) {
    const randomIndex = Math.floor(Math.random() * charset.length);
    lozinka += charset[randomIndex];
  }

  const nova = document.getElementById('nova_lozinka');
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
