<div class="naslov-dugme">
    <h2>Uredi dijagnozu</h2>
    <a href="/dijagnoze" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Nazad
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="main-content">
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); max-width: 800px;">
        <form method="POST" action="/dijagnoze?action=edit&id=<?= $dijagnoza['id'] ?>">
            <div class="form-group">
                <label for="naziv">Naziv dijagnoze <span style="color: #e74c3c;">*</span></label>
                <input 
                    type="text" 
                    id="naziv" 
                    name="naziv" 
                    required
                    placeholder="Npr: Lumbalni sindrom"
                    value="<?= htmlspecialchars($dijagnoza['naziv']) ?>"
                >
            </div>

            <div class="form-group">
                <label for="opis">Opis</label>
                <textarea 
                    id="opis" 
                    name="opis" 
                    rows="4"
                    placeholder="Dodatne informacije o dijagnozi..."
                ><?= htmlspecialchars($dijagnoza['opis']) ?></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Sačuvaj izmjene
                </button>
                <a href="/dijagnoze" class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i> Otkaži
                </a>
            </div>
        </form>
    </div>

    <!-- Informacija o upotrebi -->
    <?php
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM karton_dijagnoze WHERE dijagnoza_id = ?");
        $stmt->execute([$dijagnoza['id']]);
        $usage_count = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $usage_count = 0;
    }
    ?>
    
    <?php if ($usage_count > 0): ?>
    <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
        <p style="margin: 0; color: #856404;">
            <i class="fa-solid fa-exclamation-triangle" style="color: #ffc107;"></i>
            Ova dijagnoza se trenutno koristi u <strong><?= $usage_count ?></strong> kartona.
        </p>
    </div>
    <?php endif; ?>
</div>