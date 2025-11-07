<div class="naslov-dugme">
    <h2>Kartoteka</h2>
    <a href="/kartoni/kreiraj"class="btn btn-novo"><i class="fa-solid fa-add"></i> Novi karton</a>
</div>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-warning">Ovaj karton nije moguće obrisati.</div>
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'obrisan'): ?>
    <div class="alert alert-success">Karton je uspješno obrisan.</div>
  
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'tretman-ok'): ?>
  <div class="notifikacija uspjeh">Tretman je uspješno dodan.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'tretman-greska'): ?>
  <div class="notifikacija greska">Greška pri dodavanju tretmana.</div>
<?php endif; ?>

<div class="main-content-fw">
<table id="tabela" class="table-standard">
  <thead>
      <tr>
        <th>ID</th>
        <th>Ime i prezime</th>
        <th>Email</th>
        <th>JMBG</th>
        <th>Datum rođenja</th>
        <th>Broj upisa</th>
        <th>Akcije</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($kartoni as $k): ?>
      <tr>
        <td><?= $k['id'] ?></td>
        <td>
          <a class="openlink" href="/kartoni/pregled?id=<?= $k['id'] ?>">
            <?= htmlspecialchars($k['ime']) ?> <?= htmlspecialchars($k['prezime']) ?>
          </a>
        </td>
        <td><?= htmlspecialchars($k['email'] ?? '') ?></td>
        <td><?= $k['jmbg'] ?></td>
        <td><?= date('d.m.Y', strtotime($k['datum_rodjenja'])) ?></td>
        <td><?= $k['broj_upisa'] ?></td>
        <td>
            <?php if (in_array($user['uloga'], ['admin', 'recepcioner'])): ?>
            <button class="btn btn-sm btn-add"
        onclick="otvoriModalTretman(<?= $k['id'] ?>, '<?= htmlspecialchars($k['ime'] . ' ' . $k['prezime']) ?>', '<?= htmlspecialchars($k['broj_upisa']) ?>')">
  <i class="fa-solid fa-add"></i>
</button>

          <a href="/kartoni/uredi?id=<?= $k['id'] ?>"class="btn btn-sm btn-edit"><i class="fa-solid fa-edit"></i></a>
          <button class="btn btn-sm btn-danger" onclick="potvrdiBrisanje(<?= $k['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
</table>
</div>
<!-- Modal za potvrdu brisanja -->
<!-- Overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<!-- Modal za potvrdu brisanja -->
<div id="brisanje-modal" class="modal" style="display:none;">
  <div class="modal-content">
    <p>Jeste li sigurni da želite obrisati ovaj karton?</p>
    <form method="post" action="/kartoni/obrisi" style="margin-top: 20px;">
      <input type="hidden" name="id" id="id-brisanja">
      <div style="text-align: center;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
        <button type="submit" class="btn btn-danger">Da, obriši</button>
      </div>
    </form>
  </div>
</div>


<!-- Modal za tretman -->
<div id="tretman-modal" class="modal" style="display: none;">
  <div class="modal-content">
    <h3>Dodaj tretman</h3>
    <p><strong>Karton za:</strong> <span id="modal-ime"></span></p>
<p><strong>Broj kartona:</strong> <span id="modal-broj"></span></p>

    <form method="post" action="/kartoni/dodaj-tretman">
      <input type="hidden" name="karton_id" id="modal-karton-id">
      <div class="form-group">
        <label for="terapeut_id">Terapeut</label>
        <select name="terapeut_id" class="select2" required>
          <option value="">-- Odaberi terapeuta --</option>
          <?php foreach ($terapeuti as $terapeut): ?>
            <option value="<?= $terapeut['id'] ?>"><?= htmlspecialchars($terapeut['ime'] . ' ' . $terapeut['prezime']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <hr>
      <div class="form-group">
        <label for="stanje_prije">Stanje prije</label>
        <textarea name="stanje_prije" rows="3" required></textarea>
      </div>

      <div class="form-group">
        <label for="terapija">Terapija</label>
        <textarea name="terapija" rows="3" required></textarea>
      </div>

      <div class="form-group">
        <label for="stanje_poslije">Stanje poslije</label>
        <textarea name="stanje_poslije" rows="3" required></textarea>
      </div>

      <div style="text-align: center; margin-top: 15px;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriModalTretman()">Otkaži</button>
        <button type="submit" class="btn btn-add">Snimi tretman</button>
      </div>
    </form>
  </div>
</div>

<script>
function potvrdiBrisanje(id) {
  document.getElementById('id-brisanja').value = id;
  document.getElementById('brisanje-modal').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriModal() {
  document.getElementById('brisanje-modal').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
}

function otvoriModalTretman(kartonId, imePrezime, brojKartona) {
  document.getElementById('modal-karton-id').value = kartonId;
  document.getElementById('modal-ime').textContent = imePrezime;
  document.getElementById('modal-broj').textContent = brojKartona;

  document.getElementById('tretman-modal').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}


function zatvoriModalTretman() {
  document.getElementById('tretman-modal').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
}
</script>
<script>
  const notif = document.querySelector('.notifikacija');
  if (notif) {
    setTimeout(() => notif.remove(), 3500);
  }
</script>