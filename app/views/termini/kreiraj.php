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
        
        <!-- Tip termina - Pojedinačni/Grupni -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">
                <i class="fa-solid fa-users"></i> Tip termina
            </h4>
            <div style="display: flex; gap: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 15px 25px; background: white; border-radius: 8px; border: 2px solid #e0e0e0; flex: 1;" id="label-pojedinacni">
                    <input type="radio" name="tip_termina" value="pojedinacni" 
                           <?= ($tip_termina ?? 'pojedinacni') === 'pojedinacni' ? 'checked' : '' ?>
                           onchange="toggleTipTermina()"
                           style="width: 20px; height: 20px;">
                    <div>
                        <strong style="font-size: 1.1em;"><i class="fa-solid fa-user"></i> Pojedinačni</strong>
                        <div style="color: #7f8c8d; font-size: 0.9em;">Jedan pacijent</div>
                    </div>
                </label>
                
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 15px 25px; background: white; border-radius: 8px; border: 2px solid #e0e0e0; flex: 1;" id="label-grupni">
                    <input type="radio" name="tip_termina" value="grupni" 
                           <?= ($tip_termina ?? '') === 'grupni' ? 'checked' : '' ?>
                           onchange="toggleTipTermina()"
                           style="width: 20px; height: 20px;">
                    <div>
                        <strong style="font-size: 1.1em;"><i class="fa-solid fa-users"></i> Grupni</strong>
                        <div style="color: #7f8c8d; font-size: 0.9em;">Više pacijenata istovremeno</div>
                    </div>
                </label>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            
            <!-- Pojedinačni pacijent -->
            <div class="form-group" id="pacijent-pojedinacni">
                <label for="pacijent_id">Pacijent *</label>
                <select id="pacijent_id" name="pacijent_id" class="select2" onchange="provjeriPakete()">
                    <option value="">Odaberite pacijenta</option>
                    <?php foreach ($pacijenti as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($odabrani_pacijent_id == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['ime'] . ' ' . $p['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Grupni - multiselect pacijenata -->
            <div class="form-group" id="pacijenti-grupni" style="display: none; grid-column: 1 / -1;">
                <label for="pacijenti_ids">Pacijenti * <small style="color: #7f8c8d;">(odaberite najmanje 2)</small></label>
                <select id="pacijenti_ids" name="pacijenti_ids[]" class="select2" multiple>
                    <?php foreach ($pacijenti as $p): ?>
                        <option value="<?= $p['id'] ?>" 
                            <?= (isset($_POST['pacijenti_ids']) && in_array($p['id'], $_POST['pacijenti_ids'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['ime'] . ' ' . $p['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="terapeut-polje">
                <label for="terapeut_id">Terapeut</label>
                <select id="terapeut_id" name="terapeut_id" class="select2">
                    <option value="">Odaberite terapeuta</option>
                    <?php foreach ($terapeuti as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($odabrani_terapeut_id == $t['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['ime'] . ' ' . $t['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <!-- Prikaz aktivnih paketa pacijenta - SAMO ZA POJEDINAČNE -->
        <div id="paketi-sekcija">
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
        </div>

        <div class="form-group" id="usluga-polje">
            <label for="usluge_ids">Usluge * <small style="color: #7f8c8d;">(možete odabrati više usluga)</small></label>
            <select id="usluge_ids" name="usluge_ids[]" class="select2" multiple onchange="izracunajCijenu()">
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
                            data-naziv="<?= htmlspecialchars($u['naziv']) ?>"
                            <?= (isset($_POST['usluge_ids']) && in_array($u['id'], $_POST['usluge_ids'])) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['naziv']) ?> - <?= number_format($u['cijena'], 2) ?> KM
                    </option>
                <?php endforeach; ?>
                <?php if ($trenutna_kategorija !== '') echo '</optgroup>'; ?>
            </select>
        </div>
        
        <!-- Prikaz odabranih usluga i cijena -->
        <div id="odabrane-usluge" style="display: none; background: #e8f4f8; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <h5 style="margin: 0 0 10px 0; color: #2c3e50;">
                <i class="fa-solid fa-list-check"></i> Odabrane usluge:
            </h5>
            <div id="lista-usluga"></div>
            <div style="border-top: 2px solid #289CC6; margin-top: 10px; padding-top: 10px;">
                <strong style="font-size: 1.1em;">Ukupno: <span id="ukupna-cijena-usluge">0,00 KM</span></strong>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            
            <div class="form-group">
                <label for="datum">Datum *</label>
                <input type="date" id="datum" name="datum" required 
                       value="<?= htmlspecialchars($_POST['datum'] ?? date('Y-m-d')) ?>"
                       onchange="provjeriDatum()">
            </div>

            <div class="form-group">
                <label for="vrijeme">Vrijeme *</label>
                <input type="time" id="vrijeme" name="vrijeme" required 
                       value="<?= htmlspecialchars($_POST['vrijeme'] ?? '') ?>"
                       onchange="provjeriDatum()">
            </div>

        </div>
        
        <!-- Upozorenje za retroaktivni termin -->
        <div id="retroaktivno-upozorenje" style="display: none; background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; color: #856404;">
                <i class="fa-solid fa-clock-rotate-left" style="font-size: 1.5em;"></i>
                <div>
                    <strong>Retroaktivni unos</strong>
                    <div style="font-size: 0.9em;">Termin je u prošlosti. Status će automatski biti postavljen na "Obavljen" i email notifikacije neće biti poslane.</div>
                </div>
            </div>
        </div>

        <!-- Plaćanje i popusti -->
        <div id="placanje-sekcija" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">
                <i class="fa-solid fa-money-bill-wave"></i> Plaćanje i popusti
                <span id="grupni-napomena" style="display: none; font-size: 0.8em; color: #7f8c8d; font-weight: normal;">
                    (primjenjuje se na sve pacijente u grupi)
                </span>
            </h4>
            
            <!-- Tip plaćanja - Radio buttons -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px;">
                <label class="tip-placanja-label" id="label-puna-cijena" style="display: flex; flex-direction: column; align-items: center; padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="tip_placanja" value="puna_cijena" checked onchange="toggleTipPlacanja()" style="margin-bottom: 8px;">
                    <i class="fa-solid fa-money-bill" style="font-size: 1.5em; color: #27ae60; margin-bottom: 5px;"></i>
                    <strong>Puna cijena</strong>
                </label>
                
                <label class="tip-placanja-label" id="label-besplatno" style="display: flex; flex-direction: column; align-items: center; padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="tip_placanja" value="besplatno" onchange="toggleTipPlacanja()" style="margin-bottom: 8px;">
                    <i class="fa-solid fa-hand-holding-heart" style="font-size: 1.5em; color: #e74c3c; margin-bottom: 5px;"></i>
                    <strong>Besplatno</strong>
                </label>
                
                <label class="tip-placanja-label" id="label-poklon-bon" style="display: flex; flex-direction: column; align-items: center; padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="tip_placanja" value="poklon_bon" onchange="toggleTipPlacanja()" style="margin-bottom: 8px;">
                    <i class="fa-solid fa-gift" style="font-size: 1.5em; color: #9b59b6; margin-bottom: 5px;"></i>
                    <strong>Poklon bon</strong>
                </label>
                
                <label class="tip-placanja-label" id="label-umanjenje" style="display: flex; flex-direction: column; align-items: center; padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center;">
                    <input type="radio" name="tip_placanja" value="umanjenje" onchange="toggleTipPlacanja()" style="margin-bottom: 8px;">
                    <i class="fa-solid fa-percent" style="font-size: 1.5em; color: #f39c12; margin-bottom: 5px;"></i>
                    <strong>Umanjenje %</strong>
                </label>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">
                <!-- Plaćeno checkbox -->
                <div class="form-group" style="margin: 0;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="placeno" id="placeno" value="1" 
                            <?= isset($_POST['placeno']) ? 'checked' : '' ?>
                            style="width: 20px; height: 20px;">
                        <span style="font-weight: 600;">Plaćeno</span>
                    </label>
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">Pacijent(i) platio odmah</small>
                </div>
                
                <!-- Umanjenje posto - prikaži samo ako je odabrano umanjenje -->
                <div class="form-group" style="margin: 0; display: none;" id="umanjenje-polje">
                    <label for="umanjenje_posto" style="font-weight: 600;">Procenat umanjenja</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" id="umanjenje_posto" name="umanjenje_posto" 
                               min="1" max="100" step="1"
                               value="<?= htmlspecialchars($_POST['umanjenje_posto'] ?? '50') ?>"
                               onchange="izracunajCijenu()"
                               oninput="izracunajCijenu()"
                               style="width: 80px;">
                        <span>%</span>
                    </div>
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">Npr. 50% za pola termina</small>
                </div>
            </div>
            
            <!-- Prikaz izračunate cijene -->
            <div id="cijena-prikaz" style="margin-top: 15px; padding: 15px; background: white; border-radius: 8px; display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="color: #7f8c8d;">Originalna cijena:</span>
                        <span id="originalna-cijena" style="text-decoration: line-through; margin-left: 10px;">0,00 KM</span>
                    </div>
                    <div style="font-size: 1.3em; font-weight: 600; color: #27ae60;">
                        <span>Konačna cijena <span id="po-pacijentu">(po pacijentu)</span>:</span>
                        <span id="konacna-cijena" style="margin-left: 10px;">0,00 KM</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="napomena">Napomena</label>
            <textarea id="napomena" name="napomena" rows="3" 
                    placeholder="Dodatne napomene o terminu..."><?= htmlspecialchars($_POST['napomena'] ?? '') ?></textarea>
        </div>
        

        <div class="form-actions">
            <button type="submit" class="btn btn-add">
                <i class="fa-solid fa-save"></i> <span id="btn-tekst">Kreiraj termin</span>
            </button>
            <a href="/termini" class="btn btn-secondary">Otkaži</a>
        </div>
    </form>
</div>

<script>
// Provjeri da li je datum u prošlosti
function provjeriDatum() {
    const datum = document.getElementById('datum').value;
    const vrijeme = document.getElementById('vrijeme').value;
    const upozorenje = document.getElementById('retroaktivno-upozorenje');
    
    if (datum && vrijeme) {
        const odabraniDatum = new Date(datum + 'T' + vrijeme);
        const sada = new Date();
        
        if (odabraniDatum <= sada) {
            upozorenje.style.display = 'block';
        } else {
            upozorenje.style.display = 'none';
        }
    } else if (datum) {
        const odabraniDatum = new Date(datum);
        const danas = new Date();
        danas.setHours(0, 0, 0, 0);
        
        if (odabraniDatum < danas) {
            upozorenje.style.display = 'block';
        } else {
            upozorenje.style.display = 'none';
        }
    }
}

// Toggle tip termina
function toggleTipTermina() {
    const tipPojedinacni = document.querySelector('input[name="tip_termina"][value="pojedinacni"]').checked;
    const pacijentPojedinacni = document.getElementById('pacijent-pojedinacni');
    const pacijentiGrupni = document.getElementById('pacijenti-grupni');
    const paketiSekcija = document.getElementById('paketi-sekcija');
    const terapeutPolje = document.getElementById('terapeut-polje');
    const grupniNapomena = document.getElementById('grupni-napomena');
    const poPacijentu = document.getElementById('po-pacijentu');
    const btnTekst = document.getElementById('btn-tekst');
    const labelPojedinacni = document.getElementById('label-pojedinacni');
    const labelGrupni = document.getElementById('label-grupni');
    
    if (tipPojedinacni) {
        pacijentPojedinacni.style.display = 'block';
        pacijentiGrupni.style.display = 'none';
        paketiSekcija.style.display = 'block';
        terapeutPolje.style.gridColumn = 'auto';
        grupniNapomena.style.display = 'none';
        poPacijentu.style.display = 'none';
        btnTekst.textContent = 'Kreiraj termin';
        
        labelPojedinacni.style.borderColor = '#255AA5';
        labelPojedinacni.style.background = '#f0f7ff';
        labelGrupni.style.borderColor = '#e0e0e0';
        labelGrupni.style.background = 'white';
        
        if (typeof $ !== 'undefined' && $('#pacijent_id').data('select2')) {
            $('#pacijent_id').select2('destroy').select2({
                placeholder: 'Odaberite pacijenta',
                allowClear: true
            });
        }
    } else {
        pacijentPojedinacni.style.display = 'none';
        pacijentiGrupni.style.display = 'block';
        paketiSekcija.style.display = 'none';
        terapeutPolje.style.gridColumn = '1 / -1';
        grupniNapomena.style.display = 'inline';
        poPacijentu.style.display = 'inline';
        btnTekst.textContent = 'Kreiraj grupni termin';
        
        labelGrupni.style.borderColor = '#255AA5';
        labelGrupni.style.background = '#f0f7ff';
        labelPojedinacni.style.borderColor = '#e0e0e0';
        labelPojedinacni.style.background = 'white';
        
        if (typeof $ !== 'undefined') {
            $('#pacijenti_ids').select2({
                placeholder: 'Odaberite pacijente (min. 2)',
                allowClear: true
            });
        }
    }
    
    toggleUslugaPolje();
}

// Toggle tip plaćanja
function toggleTipPlacanja() {
    const tipPlacanja = document.querySelector('input[name="tip_placanja"]:checked').value;
    const umanjenjePolje = document.getElementById('umanjenje-polje');
    
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
    
    // Prikaži/sakrij polje za umanjenje
    if (tipPlacanja === 'umanjenje') {
        umanjenjePolje.style.display = 'block';
    } else {
        umanjenjePolje.style.display = 'none';
    }
    
    izracunajCijenu();
}

// Proveri pakete kada se odabere pacijent
function provjeriPakete() {
    const pacijentId = document.getElementById('pacijent_id').value;
    const terapeutId = document.getElementById('terapeut_id').value;
    const tipTermina = document.querySelector('input[name="tip_termina"]:checked').value;
    
    if (pacijentId && tipTermina === 'pojedinacni') {
        let url = '/termini/kreiraj?pacijent_id=' + pacijentId + '&tip_termina=pojedinacni';
        if (terapeutId) {
            url += '&terapeut_id=' + terapeutId;
        }
        window.location.href = url;
    }
}

// Prikaži/sakrij usluga polje i plaćanje sekciju
function toggleUslugaPolje() {
    const tipPojedinacni = document.querySelector('input[name="tip_termina"][value="pojedinacni"]').checked;
    const paketRadios = document.querySelectorAll('input[name="koristi_paket"]');
    const uslugaPolje = document.getElementById('usluga-polje');
    const uslugaSelect = document.getElementById('usluge_ids');
    const placanjeSekcija = document.getElementById('placanje-sekcija');
    const odabraneUsluge = document.getElementById('odabrane-usluge');
    
    let koristiPaket = false;
    
    if (tipPojedinacni) {
        paketRadios.forEach(radio => {
            if (radio.checked && radio.value !== 'ne') {
                koristiPaket = true;
            }
        });
    }
    
    if (koristiPaket) {
        uslugaPolje.style.display = 'none';
        if (odabraneUsluge) odabraneUsluge.style.display = 'none';
        if (typeof $ !== 'undefined' && $('#usluge_ids').data('select2')) {
            $('#usluge_ids').val(null).trigger('change');
        }
        if (placanjeSekcija) placanjeSekcija.style.display = 'none';
    } else {
        uslugaPolje.style.display = 'block';
        if (placanjeSekcija) placanjeSekcija.style.display = 'block';
    }
    
    izracunajCijenu();
}

// Ažuriraj cijenu kada se promijeni usluga
function azurirajCijenu() {
    izracunajCijenu();
}

// Izračunaj i prikaži konačnu cijenu - MULTISELECT VERZIJA
function izracunajCijenu() {
    const uslugaSelect = document.getElementById('usluge_ids');
    const tipPlacanja = document.querySelector('input[name="tip_placanja"]:checked')?.value || 'puna_cijena';
    const umanjenje = parseFloat(document.getElementById('umanjenje_posto').value) || 0;
    const cijenaPrikaz = document.getElementById('cijena-prikaz');
    const originalnaEl = document.getElementById('originalna-cijena');
    const konacnaEl = document.getElementById('konacna-cijena');
    const odabraneUsluge = document.getElementById('odabrane-usluge');
    const listaUsluga = document.getElementById('lista-usluga');
    const ukupnaCijenaUsluge = document.getElementById('ukupna-cijena-usluge');
    
    // Dohvati sve odabrane opcije
    const selectedOptions = Array.from(uslugaSelect.selectedOptions);
    
    if (selectedOptions.length === 0) {
        if (odabraneUsluge) odabraneUsluge.style.display = 'none';
        cijenaPrikaz.style.display = 'none';
        return;
    }
    
    // Izračunaj ukupnu cijenu i prikaži listu
    let ukupnaCijena = 0;
    let listaHTML = '';
    
    selectedOptions.forEach(option => {
        const cijena = parseFloat(option.dataset.cijena) || 0;
        const naziv = option.dataset.naziv || option.text.split(' - ')[0];
        ukupnaCijena += cijena;
        listaHTML += `<div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #d4e8ef;">
            <span>${naziv}</span>
            <span style="font-weight: 500;">${cijena.toFixed(2).replace('.', ',')} KM</span>
        </div>`;
    });
    
    // Prikaži listu odabranih usluga
    if (odabraneUsluge && listaUsluga) {
        odabraneUsluge.style.display = 'block';
        listaUsluga.innerHTML = listaHTML;
        ukupnaCijenaUsluge.textContent = ukupnaCijena.toFixed(2).replace('.', ',') + ' KM';
    }
    
    // Prikaži prikaz cijene
    cijenaPrikaz.style.display = 'block';
    originalnaEl.textContent = ukupnaCijena.toFixed(2).replace('.', ',') + ' KM';
    
    let konacnaCijena = ukupnaCijena;
    
    if (tipPlacanja === 'besplatno' || tipPlacanja === 'poklon_bon') {
        konacnaCijena = 0;
        konacnaEl.style.color = tipPlacanja === 'besplatno' ? '#e74c3c' : '#9b59b6';
    } else if (tipPlacanja === 'umanjenje' && umanjenje > 0) {
        konacnaCijena = ukupnaCijena * (100 - umanjenje) / 100;
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
}

// Pozovi na load
document.addEventListener('DOMContentLoaded', function() {
    toggleTipTermina();
    toggleTipPlacanja();
    provjeriDatum();
    
    // Inicijalizuj Select2 za usluge (multiselect)
    if (typeof $ !== 'undefined') {
        $('#usluge_ids').select2({
            placeholder: 'Odaberite usluge',
            allowClear: true,
            closeOnSelect: false
        }).on('change', function() {
            izracunajCijenu();
        });
    }
    
    izracunajCijenu();
});
</script>