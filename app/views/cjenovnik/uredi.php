<div class="naslov-dugme">
    <h2>Uredi uslugu</h2>
    <a href="/cjenovnik" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak na cjenovnik</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="main-content">
    <form method="post" action="/cjenovnik/uredi">
        <input type="hidden" name="id" value="<?= $usluga['id'] ?>">
        
        <div class="form-group">
            <label for="kategorija_id">Kategorija *</label>
            <select id="kategorija_id" name="kategorija_id" class="select2" required>
                <option value="">Odaberite kategoriju</option>
                <?php foreach ($kategorije as $kat): ?>
                    <option value="<?= $kat['id'] ?>" <?= ($_POST['kategorija_id'] ?? $usluga['kategorija_id']) == $kat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['naziv']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="naziv">Naziv usluge *</label>
            <input type="text" id="naziv" name="naziv" required 
                   value="<?= htmlspecialchars($_POST['naziv'] ?? $usluga['naziv']) ?>"
                   placeholder="Npr. Klasična masaža, Ultrazvučna terapija...">
        </div>

        <div class="form-group">
            <label for="cijena">Cijena (KM) *</label>
            <input type="number" id="cijena" name="cijena" step="0.01" min="0" required 
                   value="<?= htmlspecialchars($_POST['cijena'] ?? $usluga['cijena']) ?>"
                   placeholder="0.00">
        </div>

        <div class="form-group">
            <label for="opis">Opis usluge</label>
            <textarea id="opis" name="opis" rows="4" 
                      placeholder="Kratki opis usluge..."><?= htmlspecialchars($_POST['opis'] ?? $usluga['opis']) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-edit">
                <i class="fa-solid fa-save"></i> Sačuvaj promjene
            </button>
            <a href="/cjenovnik" class="btn btn-secondary">Otkaži</a>
        </div>
    </form>
</div>