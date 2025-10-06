<h2><?= htmlspecialchars($title) ?></h2>

<form method="POST" action="/kartoni/update">
  <input type="hidden" name="karton_id" value="<?= (int)$karton['id'] ?>">

  <div class="main-content-fw">
    <div class="card-wrapper">
      <div class="card-head"><h3><i class="fa fa-user"></i> Uredi podatke o pacijentu</h3></div>

      <div class="card-block">
        <label>Ime</label>
       <input type="text" name="ime" value="<?= htmlspecialchars($karton['ime'] ?? '') ?>" required>
       
      </div>
      <div class="card-block">
        <label>Prezime</label>
        <input type="text" name="prezime" value="<?= htmlspecialchars($karton['prezime'] ?? '') ?>" required>
      </div>

      <div class="card-block">
        <label>Datum rođenja</label>
        <input type="date" name="datum_rodjenja" value="<?= htmlspecialchars($karton['datum_rodjenja']) ?>" required>

        <p>Spol</p>
        <select name="spol" required>
          <option value="Muško" <?= $karton['spol'] === 'Muško' ? 'selected' : '' ?>>Muško</option>
          <option value="Žensko" <?= $karton['spol'] === 'Žensko' ? 'selected' : '' ?>>Žensko</option>
        </select>
      </div>

      <div class="card-block">
        <label>Adresa</label>
        <input type="text" name="adresa" value="<?= htmlspecialchars($karton['adresa']) ?>">

        <p>Telefon</p>
        <input type="text" name="telefon" value="<?= htmlspecialchars($karton['telefon']) ?>">

        <p>Email</p>
        <input type="email" name="email" value="<?= htmlspecialchars($karton['email']) ?>">
      </div>

      <div class="card-block">
        <label>JMBG</label>
        <input type="text" name="jmbg" value="<?= htmlspecialchars($karton['jmbg']) ?>" required>

        <p>Broj upisa</p>
        <input type="text" name="broj_upisa" value="<?= htmlspecialchars($karton['broj_upisa']) ?>" required>
      </div>

      <div class="card-block cb-top">
        <label>Anamneza</label>
        <textarea name="anamneza" rows="3"><?= htmlspecialchars($karton['anamneza']) ?></textarea>
      </div>

      <div class="card-block cb-top">
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

      <div class="card-block cb-top">
        <label>Rehabilitacija</label>
        <textarea name="rehabilitacija" rows="3"><?= htmlspecialchars($karton['rehabilitacija']) ?></textarea>
      </div>

      <div class="card-block cb-top">
        <label>Početna procjena</label>
        <textarea name="pocetna_procjena" rows="3"><?= htmlspecialchars($karton['pocetna_procjena']) ?></textarea>
      </div>

      <div class="card-block cb-top">
        <label>Bilješke</label>
        <textarea name="biljeske" rows="3"><?= htmlspecialchars($karton['biljeske']) ?></textarea>
      </div>

      <div class="card-block cb-top">
        <label>Napomena</label>
        <textarea name="napomena" rows="3"><?= htmlspecialchars($karton['napomena']) ?></textarea>
      </div>

      <div class="card-block">
        <button type="submit" class="btn btn-primary">Spremi promjene</button>
        <a href="/kartoni/pregled?id=<?= $karton['id'] ?>" class="btn btn-secondary">Otkaži</a>
      </div>
    </div>
  </div>
</form>

<script>
$(document).ready(function() {
  $('.select2').select2();
    // Inicijalizuj Select2 za dijagnoze
    $('#dijagnoze_select').select2({
        placeholder: 'Odaberite dijagnoze...',
        allowClear: true,
        closeOnSelect: false,
        templateResult: formatDijagnoza,
        templateSelection: formatDijagnozaSelection
    });
    
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