<div class="naslov-dugme">
<h2><?= htmlspecialchars($title) ?></h2>
<a href="/kartoni/pregled?id=<?= $karton['id'] ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Povratak
    </a>
</div>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
  <div class="alert alert-warning">Ovaj tretman nije moguće obrisati.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'obrisan'): ?>
  <div class="alert alert-success">Tretman je uspješno obrisan.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'tretman-ok'): ?>
  <div class="notifikacija uspjeh">Tretman je uspješno dodan.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'tretman-greska'): ?>
  <div class="notifikacija greska">Greška pri dodavanju tretmana.</div>
<?php endif; ?>

<div class="main-content-fw">
  <div class="main-podaci">
    <p><strong>Broj kartona:</strong> <?= htmlspecialchars($karton['broj_upisa'] ?? '') ?></p>
    <p><strong>JMBG:</strong> <?= htmlspecialchars($karton['jmbg'] ?? '') ?></p>
  </div>

  <?php if (in_array($user['uloga'], ['admin', 'recepcioner'])): ?>
    <button class="btn btn-sm btn-add btn-no-margin"
      onclick="otvoriModalTretman(<?= $karton['id'] ?>, '<?= htmlspecialchars($karton['ime'] . ' ' . $karton['prezime']) ?>', '<?= htmlspecialchars($karton['broj_upisa']) ?>')">
      <i class="fa-solid fa-add"></i>
    </button>
  <?php endif; ?>

  <table class="table-standard">
    <thead>
      <tr>
        <th>Datum</th>
        <th>Stanje prije</th>
        <th>Terapija</th>
        <th>Stanje poslije</th>
        <th>Terapeut</th>
        <th>Akcije</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($tretmani): ?>
        <?php foreach ($tretmani as $t): ?>
          <tr>
            <td><?= date('d.m.Y. H:i', strtotime($t['datum'])) ?></td>
            <td><?= mb_strimwidth(strip_tags($t['stanje_prije']), 0, 50, '...') ?></td>
            <td><?= mb_strimwidth(strip_tags($t['terapija']), 0, 50, '...') ?></td>
            <td><?= mb_strimwidth(strip_tags($t['stanje_poslije']), 0, 50, '...') ?></td>
            <td><?= $t['terapeut_ime'] . ' ' . $t['terapeut_prezime'] ?></td>
            <td>
                <button class="btn btn-sm btn-view"
  onclick='prikaziTretmanDetalji(
    <?= json_encode($t["id"]) ?>,
    <?= json_encode($t["stanje_prije"]) ?>,
    <?= json_encode($t["terapija"]) ?>,
    <?= json_encode($t["stanje_poslije"]) ?>,
    <?= json_encode($t["datum"]) ?>,
    <?= json_encode($t["terapeut_ime"] . ' ' . $t["terapeut_prezime"]) ?>,
    <?= json_encode($t["unio_ime"] . ' ' . $t["unio_prezime"]) ?>  
  )'>
  <i class="fa-solid fa-eye"></i>
</button>

              <?php if (in_array($user['uloga'], ['admin', 'recepcioner'])): ?>
                <button class="btn btn-sm btn-edit"
  onclick='otvoriUrediTretman(
    <?= json_encode($t["id"]) ?>,
    <?= json_encode($t["stanje_prije"]) ?>,
    <?= json_encode($t["terapija"]) ?>,
    <?= json_encode($t["stanje_poslije"]) ?>,
    <?= json_encode($karton["id"]) ?>,
    <?= json_encode($t["terapeut_id"]) ?>
  )'>
                  <i class="fa-solid fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="potvrdiBrisanje(<?= $t['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5">Nema unesenih tretmana.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <a href="/kartoni/print-tretmani?id=<?= $karton['id'] ?>" class="btn btn-print btn-no-margin btn-top-margin" target="_blank">
  <i class="fa-solid fa-print"></i> PDF lista tretmana
</a>

</div>

<!-- Overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;" onclick="zatvoriSveModale()"></div>

<!-- Modal za potvrdu brisanja -->
<div id="brisanje-modal" class="modal" style="display:none;">
  <div class="modal-content">
    <p>Jeste li sigurni da želite obrisati ovaj tretman?</p>
    <form method="post" action="/kartoni/obrisitretman">
      <input type="hidden" name="id" id="id-brisanja">
      <input type="hidden" name="id_kartona" value="<?= $karton['id'] ?>">
      <div style="text-align: center;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
        <button type="submit" class="btn btn-danger">Da, obriši</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal za dodavanje tretmana -->
<div id="tretman-modal" class="modal" style="display: none;">
  <div class="modal-content">
    <h3>Dodaj tretman</h3>
    <p><strong>Karton za:</strong> <span id="modal-ime"></span></p>
    <p><strong>Broj kartona:</strong> <span id="modal-broj"></span></p>
    

    <form method="post" action="/kartoni/dodaj-tretman">
        <div class="form-group">
  <label for="terapeut_id">Terapeut</label>
  <select name="terapeut_id" class="select2" required>
    <option value="">-- Odaberi terapeuta --</option>
    <?php foreach ($terapeuti as $terapeut): ?>
      <option value="<?= $terapeut['id'] ?>"><?= htmlspecialchars($terapeut['ime'] . ' ' . $terapeut['prezime']) ?></option>
    <?php endforeach; ?>
  </select>
</div><hr>
      <input type="hidden" name="karton_id" id="modal-karton-id-dodaj">
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

<!-- Modal za uređivanje tretmana -->
<div id="tretman-modal-uredi" class="modal" style="display: none;">
  <div class="modal-content">
    <h3>Uredi tretman</h3>
    <form method="post" action="/kartoni/uredi-tretman">
      <input type="hidden" name="karton_id" id="modal-karton-id-uredi">
      <input type="hidden" name="tretman_id" id="modal-tretman-id">
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
      <hr>
      <div class="form-group">
      <label for="terapeut_id">Terapeut</label>
      <select name="terapeut_id" id="modal-terapeut-id-uredi" class="select2" required>
        <option value="">-- Odaberi terapeuta --</option>
        <?php foreach ($terapeuti as $terapeut): ?>
          <option value="<?= $terapeut['id'] ?>"><?= htmlspecialchars($terapeut['ime'] . ' ' . $terapeut['prezime']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

      <div style="text-align: center; margin-top: 15px;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriUrediTretman()">Otkaži</button>
        <button type="submit" class="btn btn-add">Snimi izmjene</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal za pregled pojedinačnog tretmana -->
<div id="tretman-modal-view" class="modal" style="display: none;">
  <div class="modal-content">
    <h3>Detalji tretmana</h3>
    <p><strong>Datum unosa:</strong> <span id="view-datum"></span></p>
    <p><strong>Unio:</strong> <span id="view-unio"></span></p>
    <div class="tretman-view">
    <div class="form-group">
      <label>Stanje prije</label>
      <div class="readonly-box" id="view-stanje-prije"></div>
    </div>

    <div class="form-group">
      <label>Terapija</label>
      <div class="readonly-box" id="view-terapija"></div>
    </div>

    <div class="form-group">
      <label>Stanje poslije</label>
      <div class="readonly-box" id="view-stanje-poslije"></div>
    </div>
    <div class="form-group">
      <label>Terapeut</label>
      <div class="readonly-box" id="view-terapeut"></div>
    </div>
    </div>
    <div style="text-align: center; margin-top: 15px;">
      <button type="button" class="btn btn-secondary" onclick="zatvoriViewTretman()">Zatvori</button>
      <a href="/kartoni/print-tretman?id=" id="btn-tretman-pdf" class="btn btn-print" target="_blank"><i class="fa-solid fa-print"></i> Print/PDF</a>

    </div>
  </div>
</div>


<!-- JS -->
<script>
// Inicijalizacija kada se DOM učita
$(document).ready(function() {
    console.log('DOM ready - initializing Select2');
    initializeSelect2();
});

function initializeSelect2() {
    // Uništi postojeće Select2 instance ako postoje
    if ($('.select2').hasClass('select2-hidden-accessible')) {
        $('.select2').select2('destroy');
    }
    
    // Inicijalizuj sve Select2 elemente
    $('.select2').each(function() {
        var parentModal = $(this).closest('.modal');
        var config = {
            width: '100%',
            placeholder: '-- Odaberi terapeuta --'
        };
        
        if (parentModal.length > 0) {
            config.dropdownParent = parentModal;
        }
        
        $(this).select2(config);
    });
    
    console.log('Select2 initialized');
}

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
  document.getElementById('modal-karton-id-dodaj').value = kartonId;
  document.getElementById('modal-ime').textContent = imePrezime;
  document.getElementById('modal-broj').textContent = brojKartona;
  
  // Re-inicijalizuj Select2 za modal dodavanja
  var selectElement = $('#tretman-modal select.select2');
  if (selectElement.hasClass('select2-hidden-accessible')) {
      selectElement.select2('destroy');
  }
  selectElement.select2({
      dropdownParent: $('#tretman-modal'),
      width: '100%',
      placeholder: '-- Odaberi terapeuta --'
  });
  
  document.getElementById('tretman-modal').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriModalTretman() {
  document.getElementById('tretman-modal').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
}

function otvoriUrediTretman(id, stanje_prije, terapija, stanje_poslije, karton_id, terapeut_id) {
  console.log('Opening edit modal, terapeut_id:', terapeut_id);
  
  document.getElementById('modal-tretman-id').value = id;
  document.getElementById('modal-karton-id-uredi').value = karton_id;
  document.querySelector('#tretman-modal-uredi [name="stanje_prije"]').value = stanje_prije;
  document.querySelector('#tretman-modal-uredi [name="terapija"]').value = terapija;
  document.querySelector('#tretman-modal-uredi [name="stanje_poslije"]').value = stanje_poslije;
  
  // Uništi postojeću Select2 instancu
  var selectElement = $('#modal-terapeut-id-uredi');
  if (selectElement.hasClass('select2-hidden-accessible')) {
      selectElement.select2('destroy');
      console.log('Destroyed existing Select2');
  }
  
  // Re-inicijalizuj Select2
  selectElement.select2({
      dropdownParent: $('#tretman-modal-uredi'),
      width: '100%',
      placeholder: '-- Odaberi terapeuta --'
  });
  console.log('Re-initialized Select2');
  
  // Postavi vrijednost
  selectElement.val(terapeut_id).trigger('change');
  console.log('Set value to:', terapeut_id);
  
  document.getElementById('tretman-modal-uredi').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriUrediTretman() {
  document.getElementById('tretman-modal-uredi').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
}

function prikaziTretmanDetalji(id, stanje_prije, terapija, stanje_poslije, datum, terapeut_ime_prezime, unio) {
  document.getElementById('view-datum').textContent = new Date(datum).toLocaleString('hr-HR');
  document.getElementById('view-unio').textContent = unio;

  document.getElementById('view-stanje-prije').textContent = stanje_prije;
  document.getElementById('view-terapija').textContent = terapija;
  document.getElementById('view-stanje-poslije').textContent = stanje_poslije;
  document.getElementById('view-terapeut').textContent = terapeut_ime_prezime;
  document.getElementById('btn-tretman-pdf').href = '/kartoni/print-tretman?id=' + id;

  document.getElementById('tretman-modal-view').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriViewTretman() {
  document.getElementById('tretman-modal-view').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
}

// Zatvori modal klikom na overlay
function zatvoriSveModale() {
  console.log('Closing all modals');
  document.getElementById('modal-overlay').style.display = 'none';
  document.getElementById('brisanje-modal').style.display = 'none';
  document.getElementById('tretman-modal').style.display = 'none';
  document.getElementById('tretman-modal-uredi').style.display = 'none';
  document.getElementById('tretman-modal-view').style.display = 'none';
}
</script>