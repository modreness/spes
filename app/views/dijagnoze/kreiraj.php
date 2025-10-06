<div class="naslov-dugme">
    <h2>Nova dijagnoza</h2>
    <a href="/dijagnoze" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Nazad
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="main-content">
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); max-width: 800px;">
        <form method="POST" action="/dijagnoze?action=create">
            <div class="form-group">
                <label for="naziv">Naziv dijagnoze <span style="color: #e74c3c;">*</span></label>
                <input 
                    type="text" 
                    id="naziv" 
                    name="naziv" 
                    required
                    placeholder="Npr: Lumbalni sindrom"
                    value="<?= htmlspecialchars($_POST['naziv'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="opis">Opis</label>
                <textarea 
                    id="opis" 
                    name="opis" 
                    rows="4"
                    placeholder="Dodatne informacije o dijagnozi..."
                ><?= htmlspecialchars($_POST['opis'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="submit" class="btn btn-add">
                    <i class="fa-solid fa-save"></i> Sačuvaj dijagnozu
                </button>
                <a href="/dijagnoze" class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i> Otkaži
                </a>
            </div>
        </form>
    </div>
</div>