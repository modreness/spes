<section class="section-box">
  <h2><?= htmlspecialchars($title) ?></h2>
  
  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'ureden'): ?>
    <div class="alert alert-success">✅ Karton je uspješno ažuriran.</div>
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'gagal'): ?>
    <div class="alert alert-danger">❌ Greška pri ažuriranju kartona.</div>
  <?php endif; ?>

  <div class="main-content">
    <div class="right-content">
      <form method="POST" action="/kartoni/update" class="form-standard">
        <input type="hidden" name="karton_id" value="<?= (int)$karton['id'] ?>">

        <h3>Lični podaci</h3>

        <div class="form-group">
          <label for="ime">Ime</label>
          <input type="text" id="ime" name="ime" value="<?= htmlspecialchars($karton['ime'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="prezime">Prezime</label>
          <input type="text" id="prezime" name="prezime" value="<?= htmlspecialchars($karton['prezime'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="datum_rodjenja">Datum rođenja</label>
          <input type="date" id="datum_rodjenja" name="datum_rodjenja" value="<?= htmlspecialchars($karton['datum_rodjenja']) ?>" required>
        </div>

        <div class="form-group">
          <label for="spol">Spol</label>
          <select id="spol" name="spol" required>
            <option value="Muško" <?= $karton['spol'] === 'Muško' ? 'selected' : '' ?>>Muško</option>
            <option value="Žensko" <?= $karton['spol'] === 'Žensko' ? 'selected' : '' ?>>Žensko</option>
          </select>
        </div>

        <div class="form-group">
          <label for="adresa">Adresa</label>
          <textarea id="adresa" name="adresa" rows="2"><?= htmlspecialchars($karton['adresa']) ?></textarea>
        </div>

        <div class="form-group">
          <label for="telefon">Telefon</label>
          <input type="text" id="telefon" name="telefon" value="<?= htmlspecialchars($karton['telefon']) ?>">
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($karton['email']) ?>">
        </div>

        <div class="form-group">
          <label for="jmbg">JMBG</label>
          <input type="text" id="jmbg" name="jmbg" value="<?= htmlspecialchars($karton['jmbg']) ?>" maxlength="13" required>
        </div>

        <div class="form-group">
          <label for="broj_upisa">Broj upisa</label>
          <input type="text" id="broj_upisa" name="broj_upisa" value="<?= htmlspecialchars($karton['broj_upisa']) ?>" required>
        </div>

        <hr>
        <h3>Medicinski podaci</h3>

        <div class="form-group">
          <label for="anamneza">Anamneza</label>
          <textarea id="anamneza" name="anamneza" rows="3"><?= htmlspecialchars($karton['anamneza']) ?></textarea>
        </div>

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
            // Dohvati sve dijagnoze
            $stmt_dijagnoze = $pdo->query("SELECT id, naziv, opis FROM dijagnoze ORDER BY naziv ASC");
            $sve_dijagnoze = $stmt_dijagnoze->fetchAll(PDO::FETCH_ASSOC);
            
            // Dohvati dijagnoze koje su već povezane sa ovim kartonom
            $stmt_odabrane = $pdo->prepare("SELECT dijagnoza_id FROM karton_dijagnoze WHERE karton_id = ?");
            $stmt_odabrane->execute([$karton['id']]);
            $odabrane_dijagnoze = $stmt_odabrane->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($sve_dijagnoze as $d):
            ?>
              <option 
                value="<?= $d['id'] ?>" 
                data-opis="<?= htmlspecialchars($d['opis']) ?>"
                <?= in_array($d['id'], $odabrane_dijagnoze) ? 'selected' : '' ?>>
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
          <textarea id="rehabilitacija" name="rehabilitacija" rows="3"><?= htmlspecialchars($karton['rehabilitacija']) ?></textarea>
        </div>

        <div class="form-group">
          <label for="pocetna_procjena">Početna procjena</label>
          <textarea id="pocetna_procjena" name="pocetna_procjena" rows="3"><?= htmlspecialchars($karton['pocetna_procjena']) ?></textarea>
        </div>

        <div class="form-group">
          <label for="biljeske">Bilješke</label>
          <textarea id="biljeske" name="biljeske" rows="3"><?= htmlspecialchars($karton['biljeske']) ?></textarea>
        </div>

        <div class="form-group">
          <label for="napomena">Napomena</label>
          <textarea id="napomena" name="napomena" rows="2"><?= htmlspecialchars($karton['napomena']) ?></textarea>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
          <button type="submit" class="submit-button">Spremi promjene</button>
          <a href="/kartoni/pregled?id=<?= $karton['id'] ?>" class="btn btn-secondary">Otkaži</a>
        </div>
      </form>
    </div>
    
    <div class="left-content">
      <?php include __DIR__ . '/../partials/help-box.php'; ?>
    </div>
  </div>
</section>

<script>
console.log('Script loaded');
console.log('jQuery available:', typeof jQuery !== 'undefined');
console.log('Select2 available:', typeof $.fn.select2 !== 'undefined');

$(document).ready(function() {
    console.log('DOM ready');
    
    // Provjeri da li element postoji
    var element = $('#dijagnoze_select');
    console.log('Element found:', element.length > 0);
    
    // Inicijalizuj SVE Select2 elemente prvo
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2();
        console.log('Initialized .select2 elements');
        
        // Inicijalizuj Select2 za dijagnoze sa custom opcijama
        $('#dijagnoze_select').select2({
            placeholder: 'Odaberite dijagnoze...',
            allowClear: true,
            closeOnSelect: false,
            templateResult: formatDijagnoza,
            templateSelection: formatDijagnozaSelection
        });
        console.log('Initialized #dijagnoze_select');
    } else {
        console.error('Select2 is not loaded!');
    }
    
    // Custom template za prikaz dijagnoza u dropdown-u
    function formatDijagnoza(dijagnoza) {
        if (!dijagnoza.id) {
            return dijagnoza.text;
        }
        
        var opis = $(dijagnoza.element).data('opis');
        var $dijagnoza = $(
            '<div style="line-height: 1.4;">' +
                '<strong>' + dijagnoza.text + '</strong>' +
                (opis ? '<br><small style="color: #7f8c8d;">' + opis + '</small>' : '') +
            '</div>'
        );
        return $dijagnoza;
    }
    
    // Template za selektovane dijagnoze
    function formatDijagnozaSelection(dijagnoza) {
        return dijagnoza.text;
    }
});
</script>