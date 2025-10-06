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

      <!-- ZAMIJENITE STARI textarea za dijagnozu sa ovim: -->
      
      <div class="card-block cb-top">
        <label>Dijagnoze</label>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6;">
          <?php
          // Dohvati sve dijagnoze
          $stmt_dijagnoze = $pdo->query("SELECT id, naziv, opis FROM dijagnoze ORDER BY naziv ASC");
          $sve_dijagnoze = $stmt_dijagnoze->fetchAll(PDO::FETCH_ASSOC);
          
          // Dohvati dijagnoze koje su već povezane sa ovim kartonom
          $stmt_odabrane = $pdo->prepare("SELECT dijagnoza_id FROM karton_dijagnoze WHERE karton_id = ?");
          $stmt_odabrane->execute([$karton['id']]);
          $odabrane_dijagnoze = $stmt_odabrane->fetchAll(PDO::FETCH_COLUMN);
          
          if (empty($sve_dijagnoze)):
          ?>
            <p style="color: #7f8c8d; text-align: center; padding: 20px;">
              <i class="fa-solid fa-info-circle"></i> Nema dostupnih dijagnoza. 
              <a href="/dijagnoze?action=create" target="_blank">Dodajte dijagnoze</a>
            </p>
          <?php else: ?>
            <?php foreach ($sve_dijagnoze as $d): ?>
              <div style="padding: 8px; margin-bottom: 5px; border-radius: 4px; background: white;">
                <label style="display: flex; align-items: start; cursor: pointer; margin: 0;">
                  <input 
                    type="checkbox" 
                    name="dijagnoze[]" 
                    value="<?= $d['id'] ?>"
                    <?= in_array($d['id'], $odabrane_dijagnoze) ? 'checked' : '' ?>
                    style="margin-right: 10px; margin-top: 3px;"
                  >
                  <div style="flex: 1;">
                    <strong style="color: #2c3e50;"><?= htmlspecialchars($d['naziv']) ?></strong>
                    <?php if ($d['opis']): ?>
                      <br><span style="color: #7f8c8d; font-size: 13px;"><?= htmlspecialchars($d['opis']) ?></span>
                    <?php endif; ?>
                  </div>
                </label>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <small style="color: #7f8c8d; font-size: 12px;">
          <i class="fa-solid fa-info-circle"></i> Možete odabrati više dijagnoza
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
