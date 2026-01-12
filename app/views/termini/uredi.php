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
            <div><strong>Pacijent:</strong> <?= htmlspecialchars($termin['pacijent_ime_display'] ?? $termin['pacijent_ime'] . ' ' . $termin['pacijent_prezime']) ?></div>
            <div><strong>Terapeut:</strong> <?= htmlspecialchars($termin['terapeut_ime_display'] ?? ($termin['terapeut_ime'] ? $termin['terapeut_ime'] . ' ' . $termin['terapeut_prezime'] : 'Nije dodijeljen')) ?></div>
            <div><strong>Usluga:</strong> <?= htmlspecialchars($termin['usluga_naziv']) ?></div>
            <div><strong>Status:</strong> 
                <span style="background: <?= $termin['status'] == 'zakazan' ? '#27ae60' : ($termin['status'] == 'otkazan' ? '#e74c3c' : '#95a5a6') ?>; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                    <?= ucfirst($termin['status']) ?>
                </span>
            </div>
            <?php if ($termin['placeno_iz_paketa']): ?>
            <div><strong>Plaćanje:</strong> 
                <span style="background: #3498db; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                    Iz paketa
                </span>
            </div>
            <?php endif; ?>
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
                <label for="terapeut_id">Terapeut</label>
                <select id="terapeut_id" name="terapeut_id" class="select2" >
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
            <select id="usluga_id" name="usluga_id" class="select2" required onchange="azurirajCijenu()">
                <option value="" data-cijena="0">Odaberite uslugu</option>
                <?php 
                $trenutna_kategorija = '';
                foreach ($usluge as $u): 
                    if ($u['kategorija_naziv'] !== $trenutna_kategorija): 
                        if ($trenutna_kategorija !== '') echo '</optgroup>';
                        $trenutna_kategorija = $u['kategorija_naziv'] ?? 'Bez kategorije';
                        echo '<optgroup label="' . htmlspecialchars($trenutna_kategorija) . '">';
                    endif;
                ?>
                    <option value="<?= $u['id'] ?>" 
                            data-cijena="<?= $u['cijena'] ?>"
                            <?= ($_POST['usluga_id'] ?? $termin['usluga_id']) == $u['id'] ? 'selected' : '' ?>>
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

        <!-- Plaćanje i popusti - samo ako NIJE iz paketa -->
        <?php if (!$termin['placeno_iz_paketa']): ?>
        <div id="placanje-sekcija" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">
                <i class="fa-solid fa-money-bill-wave"></i> Plaćanje i popusti
            </h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; align-items: start;">
                <!-- Plaćeno checkbox -->
                <div class="form-group" style="margin: 0;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="placeno" id="placeno" value="1" 
                            <?= ($_POST['placeno'] ?? $termin['placeno']) ? 'checked' : '' ?>
                            style="width: 20px; height: 20px;">
                        <span style="font-weight: 600;">Plaćeno</span>
                    </label>
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">Pacijent je platio</small>
                </div>
                
                <!-- Besplatno checkbox -->
                <div class="form-group" style="margin: 0;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="besplatno" id="besplatno" value="1" 
                            <?= ($_POST['besplatno'] ?? $termin['besplatno'] ?? 0) ? 'checked' : '' ?>
                            onchange="toggleBesplatno()"
                            style="width: 20px; height: 20px;">
                        <span style="font-weight: 600;">Besplatno</span>
                    </label>
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">Termin bez naplate</small>
                </div>
                
                <!-- Umanjenje posto -->
                <div class="form-group" style="margin: 0;" id="umanjenje-polje">
                    <label for="umanjenje_posto" style="font-weight: 600;">Umanjenje (%)</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" id="umanjenje_posto" name="umanjenje_posto" 
                               min="0" max="100" step="5"
                               value="<?= htmlspecialchars($_POST['umanjenje_posto'] ?? $termin['umanjenje_posto'] ?? '0') ?>"
                               onchange="izracunajCijenu()"
                               oninput="izracunajCijenu()"
                               style="width: 80px;">
                        <span>%</span>
                    </div>
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">Npr. 50% za pola termina</small>
                </div>
            </div>
            
            <!-- Prikaz izračunate cijene -->
            <div id="cijena-prikaz" style="margin-top: 15px; padding: 15px; background: white; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="color: #7f8c8d;">Originalna cijena:</span>
                        <span id="originalna-cijena" style="text-decoration: line-through; margin-left: 10px;">0,00 KM</span>
                    </div>
                    <div style="font-size: 1.3em; font-weight: 600; color: #27ae60;">
                        <span>Konačna cijena:</span>
                        <span id="konacna-cijena" style="margin-left: 10px;">0,00 KM</span>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Poruka za termine iz paketa -->
        <div style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0;">
                <i class="fa-solid fa-box"></i> Termin iz paketa
            </h4>
            <p style="margin: 0; opacity: 0.9;">
                Ovaj termin je plaćen iz paketa. Opcije za plaćanje i popuste nisu dostupne.
            </p>
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

<?php if (!$termin['placeno_iz_paketa']): ?>
<script>
// Toggle besplatno - onemogući umanjenje ako je besplatno
function toggleBesplatno() {
    const besplatno = document.getElementById('besplatno').checked;
    const umanjenjePolje = document.getElementById('umanjenje-polje');
    const umanjenjeInput = document.getElementById('umanjenje_posto');
    
    if (besplatno) {
        umanjenjePolje.style.opacity = '0.5';
        umanjenjeInput.disabled = true;
        umanjenjeInput.value = '0';
    } else {
        umanjenjePolje.style.opacity = '1';
        umanjenjeInput.disabled = false;
    }
    
    izracunajCijenu();
}

// Ažuriraj cijenu kada se promijeni usluga
function azurirajCijenu() {
    izracunajCijenu();
}

// Izračunaj i prikaži konačnu cijenu
function izracunajCijenu() {
    const uslugaSelect = document.getElementById('usluga_id');
    const besplatno = document.getElementById('besplatno').checked;
    const umanjenje = parseFloat(document.getElementById('umanjenje_posto').value) || 0;
    const cijenaPrikaz = document.getElementById('cijena-prikaz');
    const originalnaEl = document.getElementById('originalna-cijena');
    const konacnaEl = document.getElementById('konacna-cijena');
    
    const selectedOption = uslugaSelect.options[uslugaSelect.selectedIndex];
    const cijena = parseFloat(selectedOption?.dataset?.cijena) || 0;
    
    if (cijena > 0 || uslugaSelect.value) {
        cijenaPrikaz.style.display = 'block';
        originalnaEl.textContent = cijena.toFixed(2).replace('.', ',') + ' KM';
        
        let konacnaCijena = cijena;
        
        if (besplatno) {
            konacnaCijena = 0;
            konacnaEl.style.color = '#e74c3c';
        } else if (umanjenje > 0) {
            konacnaCijena = cijena * (100 - umanjenje) / 100;
            konacnaEl.style.color = '#f39c12';
        } else {
            konacnaEl.style.color = '#27ae60';
        }
        
        konacnaEl.textContent = konacnaCijena.toFixed(2).replace('.', ',') + ' KM';
        
        // Prikaži/sakrij originalnu cijenu
        if (besplatno || umanjenje > 0) {
            originalnaEl.style.display = 'inline';
        } else {
            originalnaEl.style.display = 'none';
        }
    } else {
        cijenaPrikaz.style.display = 'none';
    }
}

// Pozovi na load
document.addEventListener('DOMContentLoaded', function() {
    toggleBesplatno();
    izracunajCijenu();
});
</script>
<?php endif; ?>