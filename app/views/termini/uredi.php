<div class="naslov-dugme">
    <h2>Uredi termin</h2>
    <a href="/termini" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="main-content">
    <!-- Info o trenutnom terminu -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <h4 style="margin: 0 0 10px 0; color: #2c3e50;">Trenutni termin</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; color: #7f8c8d;">
            <div><strong>Pacijent:</strong> <?= htmlspecialchars($termin['pacijent_ime']) ?></div>
            <div><strong>Terapeut:</strong> <?= htmlspecialchars($termin['terapeut_ime']) ?></div>
            <div><strong>Usluga:</strong> <?= htmlspecialchars($termin['usluga_naziv']) ?></div>
            <div><strong>Status:</strong> 
                <span style="background: <?= $termin['status'] == 'zakazan' ? '#27ae60' : ($termin['status'] == 'otkazan' ? '#e74c3c' : '#95a5a6') ?>; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                    <?= ucfirst($termin['status']) ?>
                </span>
            </div>
        </div>
    </div>

    <form method="post" action="/termini/uredi">
        <input type="hidden" name="id" value="<?= $termin['id'] ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            
            <div class="form-group">
                <label for="pacijent_id">Pacijent *</label>
                <select id="pacijent_id" name="pacijent_id" class="select2" required>
                    <option value="">Odaberite pacijenta</option>
                    <?php foreach ($pacijenti as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($_POST['pacijent_id'] ?? $termin['pacijent_id']) == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['ime'] . ' ' . $p['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="terapeut_id">Terapeut *</label>
                <select id="terapeut_id" name="terapeut_id" class="select2" required>
                    <option value="">Odaberite terapeuta</option>
                    <?php foreach ($terapeuti as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($_POST['terapeut_id'] ?? $termin['terapeut_id']) == $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['ime'] . ' ' . $t['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <div class="form-group">
            <label for="usluga_id">Usluga *</label>
            <select id="usluga_id" name="usluga_id" class="select2" required>
                <option value="">Odaberite uslugu</option>
                <?php 
                $trenutna_kategorija = '';
                foreach ($usluge as $u): 
                    if ($u['kategorija_naziv'] !== $trenutna_kategorija): 
                        if ($trenutna_kategorija !== '') echo '</optgroup>';
                        $trenutna_kategorija = $u['kategorija_naziv'] ?? 'Bez kategorije';
                        echo '<optgroup label="' . htmlspecialchars($trenutna_kategorija) . '">';
                    endif;
                ?>
                    <option value="<?= $u['id'] ?>" <?= ($_POST['usluga_id'] ?? $termin['usluga_id']) == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['naziv']) ?> - <?= number_format($u['cijena'], 2) ?> KM
                    </option>
                <?php endforeach; ?>
                <?php if ($trenutna_kategorija !== '') echo '</optgroup>'; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            
            <div class="form-group">
                <label for="datum">Datum *</label>
                <input type="date" id="datum" name="datum" required 
                       value="<?= htmlspecialchars($_POST['datum'] ?? date('Y-m-d', strtotime($termin['datum_vrijeme']))) ?>">
            </div>

            <div class="form-group">
                <label for="vrijeme">Vrijeme *</label>
                <input type="time" id="vrijeme" name="vrijeme" required 
                       value="<?= htmlspecialchars($_POST['vrijeme'] ?? date('H:i', strtotime($termin['datum_vrijeme']))) ?>">
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="">Odaberite status</option>
                    <option value="zakazan" <?= ($_POST['status'] ?? $termin['status']) == 'zakazan' ? 'selected' : '' ?>>Zakazan</option>
                    <option value="otkazan" <?= ($_POST['status'] ?? $termin['status']) == 'otkazan' ? 'selected' : '' ?>>Otkazan</option>
                    <option value="obavljen" <?= ($_POST['status'] ?? $termin['status']) == 'obavljen' ? 'selected' : '' ?>>Obavljen</option>
                    <option value="slobodan" <?= ($_POST['status'] ?? $termin['status']) == 'slobodan' ? 'selected' : '' ?>>Slobodan</option>
                </select>
            </div>

        </div>

        <!-- Plaćeno checkbox -->
        <?php if (!$termin['placeno_iz_paketa']): ?>
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="placeno" value="1" 
                    <?= ($_POST['placeno'] ?? $termin['placeno']) ? 'checked' : '' ?>
                    style="width: 20px; height: 20px;">
                <span style="font-weight: 600;">Plaćeno</span>
                <span style="color: #7f8c8d; font-weight: normal;">— označi ako je pacijent platio</span>
            </label>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="napomena">Napomena</label>
            <textarea id="napomena" name="napomena" rows="3" 
                    placeholder="Dodatne napomene o terminu..."><?= htmlspecialchars($_POST['napomena'] ?? $termin['napomena']) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-edit">
                <i class="fa-solid fa-save"></i> Spremi izmjene
            </button>
            <a href="/termini" class="btn btn-secondary">Otkaži</a>
        </div>
    </form>
</div>