<div class="naslov-dugme">
    <h2>Uredi raspored</h2>
    <a href="/raspored/uredi?datum_od=<?= htmlspecialchars($raspored['datum_od']) ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Povratak
    </a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="greska">
        <i class="fa-solid fa-times-circle"></i>
        Greška pri ažuriranju rasporeda.
    </div>
<?php endif; ?>

<div class="main-content">
    <div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); max-width: 600px; margin: 0 auto;">
        
        <!-- Info card -->
        <div style="background: linear-gradient(135deg, #289cc6, #255AA5); padding: 20px; border-radius: 8px; color: white; margin-bottom: 25px;">
            <h3 style="margin: 0 0 10px 0; font-size: 1.2rem;">
                <i class="fa-solid fa-user-doctor"></i> 
                <?= htmlspecialchars($raspored['terapeut_ime']) ?>
            </h3>
            <p style="margin: 0; opacity: 0.9; font-size: 0.95rem;">
                <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($raspored['terapeut_email']) ?>
            </p>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.2);">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem;">
                    <div>
                        <strong>Dan:</strong> <?= dani()[$raspored['dan']] ?>
                    </div>
                    <div>
                        <strong>Datum:</strong> <?= $stvarni_datum ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forma za edit -->
        <form method="post">
            <div class="form-group">
                <label for="smjena" style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    <i class="fa-solid fa-clock"></i> Odaberi smjenu *
                </label>
                <select name="smjena" id="smjena" required 
                        style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; background: white;">
                    <?php foreach (smjene() as $key => $naziv): ?>
                        <option value="<?= $key ?>" <?= $raspored['smjena'] === $key ? 'selected' : '' ?>>
                            <?= $naziv ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="display: block; margin-top: 8px; color: #7f8c8d;">
                    Trenutna smjena: <strong><?= smjene()[$raspored['smjena']] ?? ucfirst($raspored['smjena']) ?></strong>
                </small>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px; justify-content: flex-end;">
                <a href="/raspored/uredi?datum_od=<?= htmlspecialchars($raspored['datum_od']) ?>" 
                   class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i> Otkaži
                </a>
                <button type="submit" class="btn btn-add">
                    <i class="fa-solid fa-save"></i> Spremi izmjene
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 20px;
}

select:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}
</style>