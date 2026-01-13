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
            <?php elseif (!empty($termin['poklon_bon'])): ?>
            <div><strong>Plaćanje:</strong> 
                <span style="background: #9b59b6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                    <i class="fa-solid fa-gift"></i> Poklon bon
                </span>
            </div>
            <?php endif; ?>
            <?php if (!empty($termin['tip_termina']) && $termin['tip_termina'] === 'grupni'): ?>
            <div><strong>Tip:</strong> 
                <span style="background: #9b59b6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                    <i class="fa-solid fa-users"></i> Grupni termin
                </span>
            </div>
            <?php endif; ?>
            <?php if (!empty($termin['dozvoli_pridruzivanje'])): ?>
            <div><strong>Pridruživanje:</strong> 
                <span style="background: #17a2b8; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                    <i class="fa-solid fa-user-plus"></i> Dozvoljeno
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Prikaz članova grupe ako je grupni termin -->
    <?php if (!empty($grupa_clanovi)): ?>
    <div style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <h4 style="margin: 0 0 15px 0;">
            <i class="fa-solid fa-users"></i> Ostali pacijenti u grupi (<?= count($grupa_clanovi) ?>)
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px;">
            <?php foreach ($grupa_clanovi as $clan): ?>
            <div style="background: rgba(255,255,255,0.15); padding: 12px 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong><?= htmlspecialchars($clan['pacijent_ime']) ?></strong>
                    <div style="font-size: 0.85em; opacity: 0.9;">
                        Status: <?= ucfirst($clan['status']) ?>
                        <?= $clan['placeno'] ? ' • Plaćeno ✓' : '' ?>
                    </div>
                </div>
                <a href="/termini/uredi?id=<?= $clan['id'] ?>" 
                   style="background: rgba(255,255,255,0.2); color: white; padding: 5px 12px; border-radius: 5px; text-decoration: none; font-size: 0.85em;">
                    <i class="fa-solid fa-edit"></i> Uredi
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

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

        <!-- Dozvoli pridruživanje -->
        <div style="background: #e8f4fd; border: 1px solid #bee5eb; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="dozvoli_pridruzivanje" id="dozvoli_pridruzivanje" value="1" 
                    <?= ($_POST['dozvoli_pridruzivanje'] ?? $termin['dozvoli_pridruzivanje'] ?? 0) ? 'checked' : '' ?>
                    style="width: 20px; height: 20px;">
                <div>
                    <strong style="color: #0c5460;"><i class="fa-solid fa-user-plus"></i> Dozvoli pridruživanje drugog pacijenta</strong>
                    <div style="color: #0c5460; font-size: 0.9em;">
                        Omogućite ako želite da se još jedan pacijent može dodati u isto vrijeme kod istog terapeuta
                    </div>
                </div>
            </label>
        </div>

        <!-- Plaćanje i popusti - samo ako NIJE iz paketa -->
        <?php if (!$termin['placeno_iz_paketa']): ?>
        <div id="placanje-sekcija" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">
                <i class="fa-solid fa-money-bill-wave"></i> Plaćanje i popusti
            </h4>
            
            <!-- Tip plaćanja - Radio buttons -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px;">
                <label class="tip-placanja-label" id="label-puna-cijena" style="display: flex; flex-direction: column; align-items: center; padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="tip_placanja" value="puna_cijena" 
                           <?= ($trenutni_tip_placanja ?? 'puna_cijena') === 'puna_cijena' ? 'checked' : '' ?>
                           onchange="toggleTipPlacanja()" style="margin-bottom: 8px;">
                    <i class="fa-solid fa-money-bill" style="font-size: 1.5em; color: #27ae60; margin-bottom: 5px;"></i>
                    <strong>Puna cijena</strong>
                </label>
                
                <label class="tip-placanja-label" id="label-besplatno" style="display: flex; flex-direction: column; align-items: center; padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="tip_placanja" value="besplatno" 
                           <?= ($trenutni_tip_placanja ?? '') === 'besplatno' ? 'checked' : '' ?>
                           onchange="toggleTipPlacanja()" style="margin-bottom: 8px;">
                    <i class="fa-solid fa-hand-holding-heart" style="font-size: 1.5em; color: #e74c3c; margin-bottom: 5px;"></i>
                    <strong>Besplatno</strong>
                </label>
                
                <label class="tip-placanja-label" id="label-poklon-bon" style="display: flex; flex-direction: column; align-items: center; padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="tip_placanja" value="poklon_bon" 
                           <?= ($trenutni_tip_placanja ?? '') === 'poklon_bon' ? 'checked' : '' ?>
                           onchange="toggleTipPlacanja()" style="margin-bottom: 8px;">
                    <i class="fa-solid fa-gift" style="font-size: 1.5em; color: #9b59b6; margin-bottom: 5px;"></i>
                    <strong>Poklon bon</strong>
                </label>
                
                <label class="tip-placanja-label" id="label-umanjenje" style="display: flex; flex-direction: column; align-items: center; padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="tip_placanja" value="umanjenje" 
                           <?= ($trenutni_tip_placanja ?? '') === 'umanjenje' ? 'checked' : '' ?>
                           onchange="toggleTipPlacanja()" style="margin-bottom: 8px;">
                    <i class="fa-solid fa-percent" style="font-size: 1.5em; color: #f39c12; margin-bottom: 5px;"></i>
                    <strong>Umanjenje %</strong>
                </label>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">
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
                
                <!-- Umanjenje posto -->
                <div class="form-group" style="margin: 0; display: none;" id="umanjenje-polje">
                    <label for="umanjenje_posto" style="font-weight: 600;">Procenat umanjenja</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" id="umanjenje_posto" name="umanjenje_posto" 
                               min="0" max="100" step="1" disabled
                               value="<?= htmlspecialchars($_POST['umanjenje_posto'] ?? $termin['umanjenje_posto'] ?? '50') ?>"
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
        
        <!-- Opcija za ažuriranje cijele grupe -->
        <?php if (!empty($grupa_clanovi)): ?>
        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="azuriraj_grupu" id="azuriraj_grupu" value="1" 
                    style="width: 20px; height: 20px;">
                <div>
                    <strong style="color: #856404;"><i class="fa-solid fa-users"></i> Ažuriraj cijelu grupu</strong>
                    <div style="color: #856404; font-size: 0.9em;">
                        Promjene terapeuta, usluge, datuma/vremena, statusa i popusta će se primijeniti na sve pacijente u grupi
                    </div>
                </div>
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

<?php if (!$termin['placeno_iz_paketa']): ?>
<script>
// Toggle tip plaćanja
function toggleTipPlacanja() {
    const tipPlacanja = document.querySelector('input[name="tip_placanja"]:checked')?.value || 'puna_cijena';
    const umanjenjePolje = document.getElementById('umanjenje-polje');
    const umanjenjeInput = document.getElementById('umanjenje_posto');
    
    // Reset svih labela
    document.querySelectorAll('.tip-placanja-label').forEach(label => {
        label.style.borderColor = '#e0e0e0';
        label.style.background = 'white';
    });
    
    // Označi odabrani
    const colors = {
        'puna_cijena': '#27ae60',
        'besplatno': '#e74c3c',
        'poklon_bon': '#9b59b6',
        'umanjenje': '#f39c12'
    };
    
    const selectedLabel = document.getElementById('label-' + tipPlacanja.replace('_', '-'));
    if (selectedLabel) {
        selectedLabel.style.borderColor = colors[tipPlacanja];
        selectedLabel.style.background = colors[tipPlacanja] + '10';
    }
    
    // Prikaži/sakrij polje za umanjenje i DISABLE kad je skriveno
    if (tipPlacanja === 'umanjenje') {
        umanjenjePolje.style.display = 'block';
        umanjenjeInput.disabled = false;
        // Postavi default ako je 0
        if (parseFloat(umanjenjeInput.value) === 0) {
            umanjenjeInput.value = '50';
        }
    } else {
        umanjenjePolje.style.display = 'none';
        umanjenjeInput.disabled = true;
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
    const tipPlacanja = document.querySelector('input[name="tip_placanja"]:checked')?.value || 'puna_cijena';
    const umanjenjeInput = document.getElementById('umanjenje_posto');
    const umanjenje = umanjenjeInput && !umanjenjeInput.disabled ? (parseFloat(umanjenjeInput.value) || 0) : 0;
    const cijenaPrikaz = document.getElementById('cijena-prikaz');
    const originalnaEl = document.getElementById('originalna-cijena');
    const konacnaEl = document.getElementById('konacna-cijena');
    
    const selectedOption = uslugaSelect.options[uslugaSelect.selectedIndex];
    const cijena = parseFloat(selectedOption?.dataset?.cijena) || 0;
    
    if (cijena > 0 || uslugaSelect.value) {
        cijenaPrikaz.style.display = 'block';
        originalnaEl.textContent = cijena.toFixed(2).replace('.', ',') + ' KM';
        
        let konacnaCijena = cijena;
        
        if (tipPlacanja === 'besplatno' || tipPlacanja === 'poklon_bon') {
            konacnaCijena = 0;
            konacnaEl.style.color = tipPlacanja === 'besplatno' ? '#e74c3c' : '#9b59b6';
        } else if (tipPlacanja === 'umanjenje' && umanjenje > 0) {
            konacnaCijena = cijena * (100 - umanjenje) / 100;
            konacnaEl.style.color = '#f39c12';
        } else {
            konacnaEl.style.color = '#27ae60';
        }
        
        konacnaEl.textContent = konacnaCijena.toFixed(2).replace('.', ',') + ' KM';
        
        // Prikaži/sakrij originalnu cijenu
        if (tipPlacanja !== 'puna_cijena') {
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
    toggleTipPlacanja();
    izracunajCijenu();
});
</script>
<?php endif; ?>