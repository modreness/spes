<div class="naslov-dugme">
    <h2>Kreiraj novu kategoriju</h2>
    <a href="/kategorije" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak na listu</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="main-content">
    <form method="post" action="/kategorije/kreiraj">
        <div class="form-group">
            <label for="naziv">Naziv kategorije *</label>
            <input type="text" id="naziv" name="naziv" required 
                   value="<?= htmlspecialchars($_POST['naziv'] ?? '') ?>"
                   placeholder="Npr. Fizioterapija, Masaža, Elektroterapija...">
        </div>

        <div class="form-group">
            <label for="opis">Opis kategorije</label>
            <textarea id="opis" name="opis" rows="4" 
                      placeholder="Kratki opis kategorije usluga..."><?= htmlspecialchars($_POST['opis'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-add">
                <i class="fa-solid fa-save"></i> Sačuvaj kategoriju
            </button>
            <a href="/kategorije" class="btn btn-secondary">Otkaži</a>
        </div>
    </form>
</div>