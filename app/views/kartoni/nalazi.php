<h2><?= $title ?></h2>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'upload-ok'): ?>
        <div class="notifikacija uspjeh">Nalaz je uspješno uploadovan.</div>
    <?php elseif ($_GET['msg'] === 'upload-greska'): ?>
        <div class="notifikacija greska">Greška pri uploadu nalaza.</div>
    <?php elseif ($_GET['msg'] === 'tip-greska'): ?>
        <div class="notifikacija greska">Nepodržan tip datoteke. Dozvoljeni: PDF, JPG, PNG, DOC, DOCX.</div>
    <?php elseif ($_GET['msg'] === 'velicina-greska'): ?>
        <div class="notifikacija greska">Datoteka je prevelika. Maksimalna veličina je 10MB.</div>
    <?php elseif ($_GET['msg'] === 'obrisan'): ?>
        <div class="alert alert-success">Nalaz je uspješno obrisan.</div>
    <?php elseif ($_GET['msg'] === 'azuriran'): ?>
        <div class="notifikacija uspjeh">Nalaz je uspješno ažuriran.</div>
    <?php elseif ($_GET['msg'] === 'greska'): ?>
        <div class="alert alert-warning">Ovaj nalaz nije moguće obrisati.</div>
    <?php endif; ?>
<?php endif; ?>

<div class="main-content-fw">
  <div class="main-podaci">
    <p><strong>Pacijent:</strong> <?= htmlspecialchars($karton['ime'] . ' ' . $karton['prezime']) ?></p>
    <p><strong>Broj kartona:</strong> <?= htmlspecialchars($karton['broj_upisa'] ?? '') ?></p>
    <p><strong>JMBG:</strong> <?= htmlspecialchars($karton['jmbg'] ?? '') ?></p>
  </div>

  <?php if (hasPermission($user, 'upload_nalazi')): ?>
    <button class="btn btn-sm btn-add btn-no-margin"
      onclick="otvoriModalNalaz(<?= $karton['pacijent_id'] ?>, '<?= htmlspecialchars($karton['ime'] . ' ' . $karton['prezime']) ?>', '<?= htmlspecialchars($karton['broj_upisa']) ?>')">
      <i class="fa-solid fa-upload"></i> Upload nalaz
    </button>
  <?php endif; ?>

  <table class="table-standard">
    <thead>
      <tr>
        <th>Datum</th>
        <th>Naziv</th>
        <th>Opis</th>
        <th>Format</th>
        <th>Dodao</th>
        <th>Akcije</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($nalazi): ?>
        <?php foreach ($nalazi as $n): ?>
          <tr>
            <td><?= date('d.m.Y. H:i', strtotime($n['datum_upload'])) ?></td>
            <td><strong><?= htmlspecialchars($n['naziv']) ?></strong></td>
            <td><?= mb_strimwidth(strip_tags($n['opis']), 0, 50, '...') ?></td>
            <td>
              <?php 
              $file_ext = strtolower(pathinfo($n['file_path'], PATHINFO_EXTENSION));
              $file_icon = getFajlIkona($file_ext);
              ?>
              <span style="display: flex; align-items: center; gap: 5px;">
                <i class="<?= $file_icon ?>" style="color: #666;"></i>
                <?= strtoupper($file_ext) ?>
              </span>
            </td>
            <td><?= htmlspecialchars($n['dodao_ime'] ?? 'Nepoznat') ?></td>
            <td>
              <a href="/<?= $n['file_path'] ?>" target="_blank" class="btn btn-sm btn-view">
                <i class="fa-solid fa-download"></i>
              </a>
              
              <button class="btn btn-sm btn-view"
                onclick='prikaziNalazDetalji(
                  <?= json_encode($n["id"]) ?>,
                  <?= json_encode($n["naziv"]) ?>,
                  <?= json_encode($n["opis"]) ?>,
                  <?= json_encode($n["file_path"]) ?>,
                  <?= json_encode($n["datum_upload"]) ?>,
                  <?= json_encode($n["dodao_ime"]) ?>
                )'>
                <i class="fa-solid fa-eye"></i>
              </button>

              <?php if (in_array($user['uloga'], ['admin', 'recepcioner'])): ?>
                <button class="btn btn-sm btn-edit"
                  onclick='otvoriUrediNalaz(
                    <?= json_encode($n["id"]) ?>,
                    <?= json_encode($n["naziv"]) ?>,
                    <?= json_encode($n["opis"]) ?>
                  )'>
                  <i class="fa-solid fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="potvrdiBrisanje(<?= $n['id'] ?>)">
                  <i class="fa-solid fa-trash"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">Nema uploadovanih nalaza.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  
  <div class="bottom-foot">
    <a href="/kartoni/pregled?id=<?= $karton['id'] ?>" class="btn btn-secondary">
      <i class="fa-solid fa-arrow-left"></i> Nazad na karton
    </a>
  </div>
</div>

<!-- Overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<!-- Modal za potvrdu brisanja -->
<div id="brisanje-modal" class="modal" style="display:none;">
  <div class="modal-content">
    <p>Jeste li sigurni da želite obrisati ovaj nalaz? Datoteka će biti trajno uklonjena.</p>
    <form method="post" action="/kartoni/nalazi?id=<?= $karton['id'] ?>">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="nalaz_id" id="id-brisanja">
      <div style="text-align: center; margin-top: 20px;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
        <button type="submit" class="btn btn-danger">Da, obriši</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal za upload nalaza -->
<div id="nalaz-modal" class="modal" style="display: none;">
  <div class="modal-content">
    <h3>Upload nalaz</h3>
    <p><strong>Pacijent:</strong> <span id="modal-ime"></span></p>
    <p><strong>Broj kartona:</strong> <span id="modal-broj"></span></p>

    <form method="post" action="/kartoni/nalazi?id=<?= $karton['id'] ?>" enctype="multipart/form-data">
      <input type="hidden" name="action" value="upload">
      <input type="hidden" name="pacijent_id" id="modal-pacijent-id">
      
      <div class="form-group">
        <label for="naziv">Naziv nalaza *</label>
        <input type="text" name="naziv" placeholder="npr. RTG grudnog koša, Laboratorijski nalazi..." required>
      </div>
      
      <div class="form-group">
        <label for="opis">Opis (opcionalno)</label>
        <textarea name="opis" rows="2" placeholder="Dodatne napomene o nalazu..."></textarea>
      </div>
      
      <div class="form-group">
        <label for="nalaz_file">Fajl *</label>
        <input type="file" name="nalaz_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
        <small style="color: #666; font-size: 0.9em;">
          Podržani formati: PDF, JPG, PNG, DOC, DOCX. Maksimalno 10MB.
        </small>
      </div>

      <div style="text-align: center; margin-top: 20px;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriModalNalaz()">Otkaži</button>
        <button type="submit" class="btn btn-add">Upload nalaz</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal za uređivanje nalaza -->
<div id="nalaz-modal-uredi" class="modal" style="display: none;">
  <div class="modal-content">
    <h3>Uredi nalaz</h3>
    <form method="post" action="/kartoni/nalazi?id=<?= $karton['id'] ?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="nalaz_id" id="modal-nalaz-id">
      
      <div class="form-group">
        <label for="naziv">Naziv nalaza</label>
        <input type="text" name="naziv" id="modal-naziv-uredi" required>
      </div>
      
      <div class="form-group">
        <label for="opis">Opis</label>
        <textarea name="opis" rows="3" id="modal-opis-uredi"></textarea>
      </div>

      <div style="text-align: center; margin-top: 20px;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriUrediNalaz()">Otkaži</button>
        <button type="submit" class="btn btn-add">Spremi izmjene</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal za pregled nalaza -->
<div id="nalaz-modal-view" class="modal" style="display: none;">
  <div class="modal-content">
    <h3>Detalji nalaza</h3>
    <p><strong>Datum uploada:</strong> <span id="view-datum"></span></p>
    <p><strong>Dodao:</strong> <span id="view-dodao"></span></p>
    
    <div class="tretman-view">
      <div class="form-group">
        <label>Naziv</label>
        <div class="readonly-box" id="view-naziv"></div>
      </div>
      
      <div class="form-group">
        <label>Opis</label>
        <div class="readonly-box" id="view-opis"></div>
      </div>
      
      <div class="form-group">
        <label>Datoteka</label>
        <div class="readonly-box">
          <a href="#" id="view-fajl-link" target="_blank" style="color: #255AA5; text-decoration: none;">
            <i class="fa-solid fa-download"></i> <span id="view-fajl-naziv">Preuzmi datoteku</span>
          </a>
        </div>
      </div>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
      <button type="button" class="btn btn-secondary" onclick="zatvoriViewNalaz()">Zatvori</button>
      <a href="#" id="view-download-link" class="btn btn-add" target="_blank">
        <i class="fa-solid fa-download"></i> Preuzmi
      </a>
    </div>
  </div>
</div>

<script>
// Funkcije za modalne
function potvrdiBrisanje(id) {
  document.getElementById('id-brisanja').value = id;
  document.getElementById('brisanje-modal').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriModal() {
  document.getElementById('brisanje-modal').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
}

function otvoriModalNalaz(pacijentId, imePrezime, brojKartona) {
  document.getElementById('modal-pacijent-id').value = pacijentId;
  document.getElementById('modal-ime').textContent = imePrezime;
  document.getElementById('modal-broj').textContent = brojKartona;
  document.getElementById('nalaz-modal').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriModalNalaz() {
  document.getElementById('nalaz-modal').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
  document.querySelector('#nalaz-modal form').reset();
}

function otvoriUrediNalaz(id, naziv, opis) {
  document.getElementById('modal-nalaz-id').value = id;
  document.getElementById('modal-naziv-uredi').value = naziv;
  document.getElementById('modal-opis-uredi').value = opis;
  document.getElementById('nalaz-modal-uredi').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriUrediNalaz() {
  document.getElementById('nalaz-modal-uredi').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
}

function prikaziNalazDetalji(id, naziv, opis, filePath, datum, dodao) {
  document.getElementById('view-datum').textContent = new Date(datum).toLocaleString('hr-HR');
  document.getElementById('view-dodao').textContent = dodao || 'Nepoznat';
  document.getElementById('view-naziv').textContent = naziv;
  document.getElementById('view-opis').textContent = opis || 'Nema opisa';
  
  document.getElementById('view-fajl-link').href = '/' + filePath;
  document.getElementById('view-fajl-naziv').textContent = naziv;
  document.getElementById('view-download-link').href = '/' + filePath;
  
  document.getElementById('nalaz-modal-view').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriViewNalaz() {
  document.getElementById('nalaz-modal-view').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
}

// Notifikacije
const notif = document.querySelector('.notifikacija');
if (notif) {
  setTimeout(() => notif.remove(), 4000);
}
</script>

<?php
function getFajlIkona($ext) {
    switch ($ext) {
        case 'pdf':
            return 'fa-solid fa-file-pdf';
        case 'jpg':
        case 'jpeg':
        case 'png':
            return 'fa-solid fa-file-image';
        case 'doc':
        case 'docx':
            return 'fa-solid fa-file-word';
        default:
            return 'fa-solid fa-file';
    }
}
?>