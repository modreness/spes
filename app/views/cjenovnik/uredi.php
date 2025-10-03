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
            <label for="tip_usluge">Tip usluge *</label>
            <select id="tip_usluge" name="tip_usluge" required onchange="togglePaketPolja()">
                <option value="pojedinacna" <?= ($_POST['tip_usluge'] ?? $usluga['tip_usluge']) === 'pojedinacna' ? 'selected' : '' ?>>
                    Pojedinačna usluga
                </option>
                <option value="paket" <?= ($_POST['tip_usluge'] ?? $usluga['tip_usluge']) === 'paket' ? 'selected' : '' ?>>
                    Paket termina
                </option>
            </select>
        </div>

        <!-- Polja specifična za pakete -->
        <div id="paket-polja" style="display: <?= ($_POST['tip_usluge'] ?? $usluga['tip_usluge']) === 'paket' ? 'block' : 'none' ?>;">
            <div class="form-group">
                <label for="broj_termina">Broj termina u paketu *</label>
                <input type="number" 
                       id="broj_termina" 
                       name="broj_termina" 
                       min="1" 
                       value="<?= htmlspecialchars($_POST['broj_termina'] ?? $usluga['broj_termina'] ?? '') ?>"
                       placeholder="Npr. 12">
                <small style="color: #7f8c8d; display: block; margin-top: 5px;">Ukupan broj termina koje paket sadrži</small>
            </div>

            <div class="form-group">
                <label for="period">Period</label>
                <select id="period" name="period">
                    <option value="">Nije definisan</option>
                    <option value="tjedno" <?= ($_POST['period'] ?? $usluga['period'] ?? '') === 'tjedno' ? 'selected' : '' ?>>Tjedno</option>
                    <option value="mjesečno" <?= ($_POST['period'] ?? $usluga['period'] ?? '') === 'mjesečno' ? 'selected' : '' ?>>Mjesečno</option>
                    <option value="kvartalno" <?= ($_POST['period'] ?? $usluga['period'] ?? '') === 'kvartalno' ? 'selected' : '' ?>>Kvartalno</option>
                </select>
                <small style="color: #7f8c8d; display: block; margin-top: 5px;">Npr. "mjesečno" za mjesečnu rehabilitaciju</small>
            </div>
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

<script>
// Prikaži/sakrij polja za pakete
function togglePaketPolja() {
    const tipUsluge = document.getElementById('tip_usluge').value;
    const paketPolja = document.getElementById('paket-polja');
    const brojTermina = document.getElementById('broj_termina');
    
    if (tipUsluge === 'paket') {
        paketPolja.style.display = 'block';
        brojTermina.required = true;
    } else {
        paketPolja.style.display = 'none';
        brojTermina.required = false;
    }
}
</script>

<style>
#paket-polja {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-top: 15px;
    border-left: 4px solid #3498db;
}
</style>