<div class="naslov-dugme">
    <h2>Kreiraj novi termin</h2>
    <a href="/termini" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="greska">
        <?php foreach ($errors as $error): ?>
            <p><i class="fa-solid fa-times-circle"></i> <?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="main-content">
    <form method="post" action="/termini/kreiraj" id="termin-forma">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            
            <div class="form-group">
                <label for="pacijent_id">Pacijent *</label>
                <select id="pacijent_id" name="pacijent_id" class="select2" required onchange="provjeriPakete()">
                    <option value="">Odaberite pacijenta</option>
                    <?php foreach ($pacijenti as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($odabrani_pacijent_id == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['ime'] . ' ' . $p['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="terapeut_id">Terapeut </label>
                <select id="terapeut_id" name="terapeut_id" class="select2" >
                    <option value="">Odaberite terapeuta</option>
                    <?php foreach ($terapeuti as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($odabrani_terapeut_id == $t['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['ime'] . ' ' . $t['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <!-- Prikaz aktivnih paketa pacijenta -->
        <?php if (!empty($aktivni_paketi)): ?>
        <div style="background: linear-gradient(135deg, #255AA5, #289CC6); color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0;">
                <i class="fa-solid fa-box"></i> Pacijent ima aktivne pakete
            </h4>
            <div class="form-group">
                <label for="koristi_paket" style="color: white; font-weight: 600;">Način plaćanja:</label>
                <div style="display: grid; gap: 10px; margin-top: 10px;">
                    <?php foreach ($aktivni_paketi as $paket): ?>
                        <label style="background: rgba(255,255,255,1); padding: 15px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 15px;">
                            <input type="radio" 
                                   name="koristi_paket" 
                                   value="<?= $paket['id'] ?>" 
                                   onchange="toggleUslugaPolje()"
                                   style="width: 20px; height: 20px;">
                            <div style="flex: 1;">
                                <strong style="font-size: 1.1em;"><?= htmlspecialchars($paket['paket_naziv']) ?></strong>
                                <div style="opacity: 0.9; font-size: 0.9em; margin-top: 5px;">
                                    Preostalo: <strong><?= $paket['ukupno_termina'] - $paket['iskoristeno_termina'] ?></strong> termina
                                    | Iskorišteno: <?= $paket['iskoristeno_termina'] ?>/<?= $paket['ukupno_termina'] ?>
                                </div>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 20px; font-weight: 600;">
                                BESPLATNO
                            </div>
                        </label>
                    <?php endforeach; ?>
                    
                    <label style="background: rgba(255,255,255,1); padding: 15px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 15px;">
                        <input type="radio" 
                               name="koristi_paket" 
                               value="ne" 
                               checked 
                               onchange="toggleUslugaPolje()"
                               style="width: 20px; height: 20px;">
                        <div style="flex: 1;">
                            <strong style="font-size: 1.1em;">Plati pojedinačno</strong>
                            <div style="opacity: 0.9; font-size: 0.9em; margin-top: 5px;">
                                Ne koristi paket - naplati punu cijenu
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        <?php else: ?>
            <input type="hidden" name="koristi_paket" value="ne">
        <?php endif; ?>

        <div class="form-group" id="usluga-polje">
            <label for="usluga_id">Usluga *</label>
            <select id="usluga_id" name="usluga_id" class="select2">
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
                    <option value="<?= $u['id'] ?>" <?= ($_POST['usluga_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['naziv']) ?> - <?= number_format($u['cijena'], 2) ?> KM
                    </option>
                <?php endforeach; ?>
                <?php if ($trenutna_kategorija !== '') echo '</optgroup>'; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            
            <div class="form-group">
                <label for="datum">Datum *</label>
                <input type="date" id="datum" name="datum" required 
                       value="<?= htmlspecialchars($_POST['datum'] ?? date('Y-m-d', strtotime('+1 day'))) ?>"
                       min="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label for="vrijeme">Vrijeme *</label>
                <input type="time" id="vrijeme" name="vrijeme" required 
                       value="<?= htmlspecialchars($_POST['vrijeme'] ?? '') ?>">
            </div>

        </div>
        <!-- Plaćeno checkbox - samo za pojedinačne termine -->
        <div class="form-group" id="placeno-polje">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="placeno" value="1" 
                    <?= isset($_POST['placeno']) ? 'checked' : '' ?>
                    style="width: 20px; height: 20px;">
                <span style="font-weight: 600;">Plaćeno</span>
                <span style="color: #7f8c8d; font-weight: normal;">— označi ako je pacijent platio odmah</span>
            </label>
        </div>

        <div class="form-group">
            <label for="napomena">Napomena</label>
            <textarea id="napomena" name="napomena" rows="3" 
                    placeholder="Dodatne napomene o terminu..."><?= htmlspecialchars($_POST['napomena'] ?? '') ?></textarea>
        </div>
        

        <div class="form-actions">
            <button type="submit" class="btn btn-add">
                <i class="fa-solid fa-save"></i> Kreiraj termin
            </button>
            <a href="/termini" class="btn btn-secondary">Otkaži</a>
        </div>
    </form>
</div>

<script>
// Proveri pakete kada se odabere pacijent
function provjeriPakete() {
    const pacijentId = document.getElementById('pacijent_id').value;
    const terapeutId = document.getElementById('terapeut_id').value;
    
    if (pacijentId) {
        // Reload stranicu sa pacijent_id i terapeut_id u URL-u
        let url = '/termini/kreiraj?pacijent_id=' + pacijentId;
        if (terapeutId) {
            url += '&terapeut_id=' + terapeutId;
        }
        window.location.href = url;
    }
}

// Prikaži/sakrij usluga polje
// Prikaži/sakrij usluga polje i placeno polje
function toggleUslugaPolje() {
    const paketRadios = document.querySelectorAll('input[name="koristi_paket"]');
    const uslugaPolje = document.getElementById('usluga-polje');
    const uslugaSelect = document.getElementById('usluga_id');
    const placenoPolje = document.getElementById('placeno-polje');
    
    let koristiPaket = false;
    paketRadios.forEach(radio => {
        if (radio.checked && radio.value !== 'ne') {
            koristiPaket = true;
        }
    });
    
    if (koristiPaket) {
        uslugaPolje.style.display = 'none';
        uslugaSelect.removeAttribute('required');
        uslugaSelect.value = '';
        if (placenoPolje) placenoPolje.style.display = 'none';
    } else {
        uslugaPolje.style.display = 'block';
        uslugaSelect.setAttribute('required', 'required');
        if (placenoPolje) placenoPolje.style.display = 'block';
    }
}

// Pozovi na load
document.addEventListener('DOMContentLoaded', function() {
    toggleUslugaPolje();
});
</script>