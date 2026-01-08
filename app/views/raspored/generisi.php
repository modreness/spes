<div class="naslov-dugme">
    <h2>Generiši raspored automatski</h2>
    <a href="/raspored" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="greska">
        <?php foreach ($errors as $error): ?>
            <p><i class="fa-solid fa-times-circle"></i> <?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="main-content">
    <div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); max-width: 700px; margin: 0 auto;">
        
        <!-- Info box -->
        <div style="background: linear-gradient(135deg, #289cc6, #255AA5); padding: 20px; border-radius: 8px; color: white; margin-bottom: 25px;">
            <h4 style="margin: 0 0 10px 0;"><i class="fa-solid fa-magic"></i> Automatsko generiranje</h4>
            <p style="margin: 0; opacity: 0.9; font-size: 0.95rem;">
                Sistem će automatski kreirati raspored za odabrani period sa naizmjeničnim smjenama (Jutro ↔ Večer) svake sedmice.
            </p>
        </div>

        <form method="post" action="/raspored/generisi">
            
            <!-- Terapeut -->
            <div class="form-group">
                <label for="terapeut_id"><i class="fa-solid fa-user-doctor"></i> Terapeut *</label>
                <select id="terapeut_id" name="terapeut_id" class="select2" required onchange="provjeriZadnjuSmjenu()">
                    <option value="">Odaberite terapeuta</option>
                    <?php foreach ($terapeuti as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($_POST['terapeut_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['ime'] . ' ' . $t['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Info o zadnjoj smjeni -->
            <div id="zadnja-smjena-info" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #289cc6;">
                <div id="zadnja-smjena-tekst"></div>
            </div>

            <!-- Početni datum -->
            <div class="form-group">
                <label for="datum_od"><i class="fa-solid fa-calendar"></i> Početni datum (ponedjeljak) *</label>
                <input type="date" id="datum_od" name="datum_od" required 
                       value="<?= htmlspecialchars($_POST['datum_od'] ?? date('Y-m-d', strtotime('next monday'))) ?>">
                <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                    Datum mora biti ponedjeljak — početak sedmice
                </small>
            </div>

            <!-- Broj sedmica -->
            <div class="form-group">
                <label for="broj_sedmica"><i class="fa-solid fa-hashtag"></i> Broj sedmica *</label>
                <select id="broj_sedmica" name="broj_sedmica" required>
                    <option value="4" <?= ($_POST['broj_sedmica'] ?? '') == '4' ? 'selected' : '' ?>>4 sedmice (1 mjesec)</option>
                    <option value="8" <?= ($_POST['broj_sedmica'] ?? '') == '8' ? 'selected' : '' ?>>8 sedmica (2 mjeseca)</option>
                    <option value="13" <?= ($_POST['broj_sedmica'] ?? '') == '13' ? 'selected' : '' ?>>13 sedmica (3 mjeseca)</option>
                    <option value="26" <?= ($_POST['broj_sedmica'] ?? '26') == '26' ? 'selected' : '' ?>>26 sedmica (6 mjeseci)</option>
                    <option value="52" <?= ($_POST['broj_sedmica'] ?? '') == '52' ? 'selected' : '' ?>>52 sedmice (1 godina)</option>
                </select>
            </div>

            <!-- Radni dani -->
            <div class="form-group">
                <label><i class="fa-solid fa-calendar-week"></i> Radni dani *</label>
                <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px;">
                    <?php 
                    $dani_lista = dani();
                    $default_dani = $_POST['radni_dani'] ?? ['pon', 'uto', 'sri', 'cet', 'pet'];
                    foreach ($dani_lista as $key => $naziv): 
                    ?>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 15px; background: #f8f9fa; border-radius: 6px; border: 2px solid <?= in_array($key, $default_dani) ? '#289cc6' : '#e0e0e0' ?>;" class="dan-checkbox">
                            <input type="checkbox" name="radni_dani[]" value="<?= $key ?>" 
                                   <?= in_array($key, $default_dani) ? 'checked' : '' ?>
                                   style="width: 18px; height: 18px;"
                                   onchange="toggleDanStyle(this)">
                            <span style="font-weight: 500;"><?= $naziv ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Početna smjena -->
            <div class="form-group">
                <label for="pocetna_smjena"><i class="fa-solid fa-sun"></i> Početna smjena *</label>
                <select id="pocetna_smjena" name="pocetna_smjena" required>
                    <option value="jutro" <?= ($_POST['pocetna_smjena'] ?? 'jutro') == 'jutro' ? 'selected' : '' ?>>
                        Jutro
                    </option>
                    <option value="vecer" <?= ($_POST['pocetna_smjena'] ?? '') == 'vecer' ? 'selected' : '' ?>>
                        Večer
                    </option>
                </select>
                <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                    Prva sedmica će biti ova smjena, sljedeća suprotna, i tako naizmjenično
                </small>
            </div>

            <!-- Preview -->
            <div style="background: #f0f7ff; padding: 20px; border-radius: 8px; margin: 25px 0; border: 1px solid #289cc6;">
                <h4 style="margin: 0 0 15px 0; color: #255AA5;"><i class="fa-solid fa-eye"></i> Pregled rotacije</h4>
                <div id="rotacija-preview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px;">
                    <!-- Popunjava se JavaScript-om -->
                </div>
            </div>

            <!-- Submit -->
            <div class="form-actions">
                <button type="submit" class="btn btn-add">
                    <i class="fa-solid fa-magic"></i> Generiši raspored
                </button>
                <a href="/raspored" class="btn btn-secondary">Otkaži</a>
            </div>
        </form>
    </div>
</div>

<script>
function provjeriZadnjuSmjenu() {
    const terapeutId = document.getElementById('terapeut_id').value;
    const infoDiv = document.getElementById('zadnja-smjena-info');
    const tekstDiv = document.getElementById('zadnja-smjena-tekst');
    
    if (!terapeutId) {
        infoDiv.style.display = 'none';
        return;
    }
    
    fetch('/raspored/generisi?ajax=zadnja_smjena&terapeut_id=' + terapeutId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.zadnja_smjena) {
                    const smjenaLabels = {'jutro': 'Jutro', 'vecer': 'Večer', 'popodne': 'Popodne'};
                    tekstDiv.innerHTML = `
                        <strong>Zadnja smjena:</strong> ${smjenaLabels[data.zadnja_smjena] || data.zadnja_smjena} 
                        (${data.zadnji_datum})<br>
                        <strong>Predložena početna:</strong> <span style="color: #27ae60; font-weight: 600;">${smjenaLabels[data.predlozena_smjena] || data.predlozena_smjena}</span>
                    `;
                    // Automatski postavi predloženu smjenu
                    document.getElementById('pocetna_smjena').value = data.predlozena_smjena;
                } else {
                    tekstDiv.innerHTML = `
                        <span style="color: #7f8c8d;"><i class="fa-solid fa-info-circle"></i> 
                        Terapeut nema prethodnih rasporeda. Odaberite početnu smjenu ručno.</span>
                    `;
                }
                infoDiv.style.display = 'block';
                azurirajPreview();
            }
        })
        .catch(error => {
            console.error('Greška:', error);
            infoDiv.style.display = 'none';
        });
}

function toggleDanStyle(checkbox) {
    const label = checkbox.closest('label');
    if (checkbox.checked) {
        label.style.borderColor = '#289cc6';
    } else {
        label.style.borderColor = '#e0e0e0';
    }
}

function azurirajPreview() {
    const brojSedmica = parseInt(document.getElementById('broj_sedmica').value) || 26;
    const pocetnaSmjena = document.getElementById('pocetna_smjena').value;
    const previewDiv = document.getElementById('rotacija-preview');
    
    // Prikaži samo prvih 8 sedmica kao preview
    const prikaziSedmica = Math.min(brojSedmica, 8);
    let html = '';
    let trenutnaSmjena = pocetnaSmjena;
    
    const smjenaLabels = {'jutro': 'Jutro', 'vecer': 'Večer'};
    const smjenaBoje = {'jutro': '#289cc6', 'vecer': '#255AA5'};
    
    for (let i = 1; i <= prikaziSedmica; i++) {
        html += `
            <div style="background: ${smjenaBoje[trenutnaSmjena]}; color: white; padding: 10px; border-radius: 6px; text-align: center;">
                <div style="font-size: 0.8rem; opacity: 0.8;">Sedmica ${i}</div>
                <div style="font-weight: 600;">${smjenaLabels[trenutnaSmjena]}</div>
            </div>
        `;
        // Rotiraj
        trenutnaSmjena = (trenutnaSmjena === 'jutro') ? 'vecer' : 'jutro';
    }
    
    if (brojSedmica > 8) {
        html += `
            <div style="background: #f8f9fa; color: #7f8c8d; padding: 10px; border-radius: 6px; text-align: center; display: flex; align-items: center; justify-content: center;">
                <span>... i još ${brojSedmica - 8}</span>
            </div>
        `;
    }
    
    previewDiv.innerHTML = html;
}

// Event listeneri
document.getElementById('broj_sedmica').addEventListener('change', azurirajPreview);
document.getElementById('pocetna_smjena').addEventListener('change', azurirajPreview);

// Inicijalni preview
document.addEventListener('DOMContentLoaded', function() {
    azurirajPreview();
    
    // Ako je terapeut već odabran (POST reload)
    if (document.getElementById('terapeut_id').value) {
        provjeriZadnjuSmjenu();
    }
});
</script>