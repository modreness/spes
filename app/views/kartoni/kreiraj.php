<?php ob_start(); ?>
<section class="section-box">
  <h2>Kreiraj novi karton</h2>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'postoji'): ?>
    <div class="alert alert-warning">⚠️ Ovaj pacijent već ima karton.</div>
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'kreiran'): ?>
    <div class="alert alert-success">✅ Karton je uspješno kreiran.</div>
  <?php endif; ?>
<div class="main-content">
  <div class="right-content">
  <form action="/kartoni/kreiraj" method="post" class="form-standard">

    <div class="form-group">
      <label>Tip pacijenta</label><br>
      <label><input type="radio" name="tip_pacijenta" value="postojeci" checked> Postojeći pacijent</label>
      <label style="margin-left:20px;"><input type="radio" name="tip_pacijenta" value="novi"> Novi pacijent</label>
    </div>

    <div class="form-group">
      <label for="pacijent_id">Odaberi postojećeg pacijenta</label>
      <select name="pacijent_id" id="pacijent_id" class="select2">
        <option value="">-- Odaberi --</option>
        <?php foreach ($pacijenti as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['ime']) ?> <?= htmlspecialchars($p['prezime']) ?> (<?= htmlspecialchars($p['email']) ?>)</option>
        <?php endforeach; ?>
      </select>
      <span id="pacijent-status" style="font-size: 13px; display: none;"></span>

<input type="hidden" id="pacijent_id_hidden">
    </div>

    <div id="novi-pacijent-fields" style="display: none;">
      <hr>
      <h3>Novi pacijent – korisnički podaci</h3>

      <div class="form-group">
        <label for="ime">Ime</label>
        <input type="text" name="ime" id="ime">
      </div>

      <div class="form-group">
        <label for="prezime">Prezime</label>
        <input type="text" name="prezime" id="prezime">
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email">
      </div>

      <div class="form-group">
        <label for="username">Korisničko ime</label>
        <input type="text" name="username" id="username" required>
        <span id="username-status" style="font-size: 13px; display: none;"></span>
      </div>

      <div class="form-group">
        <label>Lozinka</label>
        <div class="password-wrapper">
          <div class="lozinka-wrap">
            <input type="password" name="lozinka" id="lozinka" style="flex:1;">
            <button type="button" class="toggle-password" onclick="toggleLozinka()" aria-label="Prikaži/Sakrij lozinku">
              <i id="eye-icon" class="fa-solid fa-eye"></i>
            </button>
          </div>
          <button type="button" onclick="generisiLozinku()" class="generator">Generiši</button>
        </div>
      </div>
    </div>

    <hr>
    <h3>Podaci o kartonu</h3>

    <div class="form-group">
      <label for="datum_rodjenja">Datum rođenja</label>
      <input type="date" name="datum_rodjenja" id="datum_rodjenja">
    </div>

    <div class="form-group">
      <label for="spol">Spol</label>
      <select name="spol" id="spol">
        <option value="">Odaberi</option>
        <option value="muški">Muški</option>
        <option value="ženski">Ženski</option>
        <option value="drugo">Drugo</option>
      </select>
    </div>

    <div class="form-group">
      <label for="adresa">Adresa</label>
      <textarea name="adresa" id="adresa" rows="2"></textarea>
    </div>

    <div class="form-group">
      <label for="telefon">Telefon</label>
      <input type="text" name="telefon" id="telefon">
    </div>

    <div class="form-group">
      <label for="jmbg">JMBG</label>
      <input type="text" name="jmbg" id="jmbg" maxlength="13">
      <span id="jmbg-status" style="font-size: 13px; display: none;"></span>
    </div>

    <div class="form-group">
      <label for="broj_upisa">Broj upisa</label>
      <input type="text" name="broj_upisa" id="broj_upisa">
    </div>

    <div class="form-group">
      <label for="anamneza">Anamneza</label>
      <textarea name="anamneza" id="anamneza" rows="3"></textarea>
    </div>

    <!-- ZAMIJENITE STARI textarea za dijagnozu sa ovim: -->
    
     <div class="form-group">
      <label for="dijagnoze_select">Dijagnoze</label>
      <select 
        id="dijagnoze_select" 
        name="dijagnoze[]" 
        multiple 
        class="select2-dijagnoze" 
        style="width: 100%;"
        data-placeholder="Odaberite dijagnoze...">
        <?php
        // Dohvati sve dijagnoze iz baze
        $stmt_dijagnoze = $pdo->query("SELECT id, naziv, opis FROM dijagnoze ORDER BY naziv ASC");
        $sve_dijagnoze = $stmt_dijagnoze->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sve_dijagnoze as $d):
        ?>
          <option value="<?= $d['id'] ?>" data-opis="<?= htmlspecialchars($d['opis']) ?>">
            <?= htmlspecialchars($d['naziv']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <small style="color: #7f8c8d; font-size: 12px; display: block; margin-top: 5px;">
        <i class="fa-solid fa-info-circle"></i> Možete odabrati više dijagnoza. Kucajte za pretragu.
      </small>
    </div>

    <div class="form-group">
      <label for="rehabilitacija">Rehabilitacija</label>
      <textarea name="rehabilitacija" id="rehabilitacija" rows="3"></textarea>
    </div>

    <div class="form-group">
      <label for="pocetna_procjena">Početna procjena</label>
      <textarea name="pocetna_procjena" id="pocetna_procjena" rows="3"></textarea>
    </div>

    <div class="form-group">
      <label for="biljeske">Bilješke</label>
      <textarea name="biljeske" id="biljeske" rows="3"></textarea>
    </div>

    <div class="form-group">
      <label for="napomena">Napomena</label>
      <textarea name="napomena" id="napomena" rows="2"></textarea>
    </div>

    <button type="submit" class="submit-button">Snimi karton</button>
  </form>
  </div>
  <div class="left-content">
      <?php include __DIR__ . '/../partials/help-box.php'; ?>
    </div>
</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pacijentSelect = document.getElementById('pacijent_id');
  const hiddenInput = document.getElementById('pacijent_id_hidden');
  const izborRadios = document.querySelectorAll('input[name="tip_pacijenta"]');
  const noviPacijentFields = document.getElementById('novi-pacijent-fields');
  const postojeciSelect = document.getElementById('pacijent_id');
  const jmbgInput = document.getElementById('jmbg');
  const jmbgStatus = document.getElementById('jmbg-status');
  const pacijentStatus = document.getElementById('pacijent-status');
  const imeInput = document.getElementById('ime');
  const prezimeInput = document.getElementById('prezime');
  const usernameInput = document.getElementById('username');
  const usernameStatus = document.getElementById('username-status');
if (window.jQuery) {
    $('.select2').select2().on('change', function () {
      const selectedId = $(this).val();
      hiddenInput.value = selectedId;
      provjeriPostojeciKarton(selectedId);
    });
  } else {
    pacijentSelect.addEventListener('change', function () {
      const selectedId = this.value;
      hiddenInput.value = selectedId;
      provjeriPostojeciKarton(selectedId);
    });
  }
  function slugify(text) {
    const map = { 'č': 'c', 'ć': 'c', 'š': 's', 'đ': 'dj', 'ž': 'z', 'Č': 'C', 'Ć': 'C', 'Š': 'S', 'Đ': 'Dj', 'Ž': 'Z' };
    return text.split('').map(char => map[char] || char).join('')
      .toLowerCase().trim().replace(/\s+/g, '.').replace(/[^\w.]+/g, '').replace(/\.+/g, '.');
  }

  function updateUsername() {
    const generated = slugify(imeInput.value + '.' + prezimeInput.value);
    usernameInput.value = generated;
    checkUsernameAvailability(generated);
  }

  function checkUsernameAvailability(username) {
    fetch('/provjeri-username?username=' + encodeURIComponent(username))
      .then(response => response.json())
      .then(data => {
        usernameStatus.style.display = 'inline';
        usernameStatus.textContent = data.postoji ? '❌ Zauzeto' : '✅ Dostupno';
        usernameStatus.style.color = data.postoji ? 'red' : 'green';
      });
  }

  function togglePolja() {
    const selected = document.querySelector('input[name="tip_pacijenta"]:checked').value;
    if (selected === 'novi') {
      noviPacijentFields.style.display = 'block';
      postojeciSelect.disabled = true;
      document.getElementById('username').setAttribute('required', 'required');
      document.getElementById('ime').setAttribute('required', 'required');
      document.getElementById('prezime').setAttribute('required', 'required');
      document.getElementById('email').setAttribute('required', 'required');
      document.getElementById('lozinka').setAttribute('required', 'required');
    } else {
      noviPacijentFields.style.display = 'none';
      postojeciSelect.disabled = false;
      document.getElementById('username').removeAttribute('required');
      document.getElementById('ime').removeAttribute('required');
      document.getElementById('prezime').removeAttribute('required');
      document.getElementById('email').removeAttribute('required');
      document.getElementById('lozinka').removeAttribute('required');
    }
  }

  izborRadios.forEach(radio => radio.addEventListener('change', togglePolja));
  togglePolja();

  imeInput.addEventListener('input', updateUsername);
  prezimeInput.addEventListener('input', updateUsername);
  usernameInput.addEventListener('input', () => checkUsernameAvailability(usernameInput.value));

  jmbgInput.addEventListener('input', function () {
    if (jmbgInput.value.length >= 8) {
      fetch('/provjeri-username?jmbg=' + encodeURIComponent(jmbgInput.value))
        .then(res => res.json())
        .then(data => {
          jmbgStatus.style.display = 'inline';
          jmbgStatus.textContent = data.postoji ? '❌ Ovaj JMBG već postoji!' : '✅ JMBG je slobodan';
          jmbgStatus.style.color = data.postoji ? 'red' : 'green';
        });
    }
  });
  jmbgInput.addEventListener('input', function () {
      // Ukloni sve što nije broj
      this.value = this.value.replace(/\D/g, '');
      
      // Skrati na maksimalno 13 znakova
      if (this.value.length > 13) {
        this.value = this.value.slice(0, 13);
      }
    });
 
});
function provjeriPostojeciKarton(pacijentId) {
  fetch('/provjeri-username?id=' + encodeURIComponent(pacijentId))
    .then(res => res.json())
    .then(data => {
      const pacijentStatus = document.getElementById('pacijent-status');
      pacijentStatus.style.display = 'inline';
      pacijentStatus.textContent = data.postoji
        ? '❌ Ovaj pacijent već ima karton!'
        : '✅ Pacijent je slobodan';
      pacijentStatus.style.color = data.postoji ? 'red' : 'green';
    })
    .catch(error => {
      console.error('Greška pri fetchanju:', error);
    });
}

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
  input.select();

  try {
    const uspjeh = document.execCommand('copy');
    if (uspjeh) {
      prikaziNotifikaciju("Lozinka je kopirana u clipboard");
    } else {
      prikaziNotifikaciju("Kopiranje nije uspjelo");
    }
  } catch (err) {
    prikaziNotifikaciju("Greška pri kopiranju");
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
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
