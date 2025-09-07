<div class="naslov-dugme">
    <h2>Uredi kategoriju</h2>
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
    <form method="post" action="/kategorije/uredi">
        <input type="hidden" name="id" value="<?= $kategorija['id'] ?>">
        
        <div class="form-group">
            <label for="naziv">Naziv kategorije *</label>
            <input type="text" id="naziv" name="naziv" required 
                   value="<?= htmlspecialchars($_POST['naziv'] ?? $kategorija['naziv']) ?>"
                   placeholder="Npr. Fizioterapija, Masaža, Elektroterapija...">
        </div>

        <div class="form-group">
            <label for="opis">Opis kategorije</label>
            <textarea id="opis" name="opis" rows="4" 
                      placeholder="Kratki opis kategorije usluga..."><?= htmlspecialchars($_POST['opis'] ?? $kategorija['opis']) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-edit">
                <i class="fa-solid fa-save"></i> Sačuvaj promjene
            </button>
            <a href="/kategorije" class="btn btn-secondary">Otkaži</a>
        </div>
    </form>
</div>