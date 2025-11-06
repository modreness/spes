<div class="naslov-dugme">
    <h2>Nalazi mojih pacijenata</h2>
    <a href="/dashboard" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content-fw">
    <!-- Statistike -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #255AA5);">
            <h3>Ukupno nalaza</h3>
            <div class="stat-number"><?= $ukupno_nalaza ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #289CC6);">
            <h3>Pacijenti sa nalazima</h3>
            <div class="stat-number"><?= $pacijenti_sa_nalazima ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #289CC6);">
            <h3>Novi nalazi (30 dana)</h3>
            <div class="stat-number"><?= $novi_nalazi_30_dana ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #289CC6)">
            <h3>Prosjek po pacijentu</h3>
            <div class="stat-number"><?= $pacijenti_sa_nalazima > 0 ? round($ukupno_nalaza / $pacijenti_sa_nalazima, 1) : 0 ?></div>
        </div>
    </div>

    <!-- Tipovi nalaza -->
    <?php if (!empty($tipovi_nalaza)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; margin-bottom: 25px;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">
                <i class="fa-solid fa-chart-pie"></i> Tipovi nalaza
            </h3>
        </div>
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <?php foreach ($tipovi_nalaza as $tip): ?>
                <div style="display: flex; align-items: center; padding: 15px; border: 1px solid #e9ecef; border-radius: 8px;">
                    <div style="margin-right: 15px;">
                        <?php
                        $ikona = 'fa-solid fa-file';
                        $boja = '#95a5a6';
                        switch($tip['tip_nalaza']) {
                            case 'PDF dokumenti': $ikona = 'fa-solid fa-file-pdf'; $boja = '#e74c3c'; break;
                            case 'Slike/RTG': $ikona = 'fa-solid fa-images'; $boja = '#289CC6'; break;
                            case 'Word dokumenti': $ikona = 'fa-solid fa-file-word'; $boja = '#255AA5'; break;
                        }
                        ?>
                        <i class="<?= $ikona ?>" style="font-size: 32px; color: <?= $boja ?>;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 1.2em; color: #2c3e50;"><?= $tip['broj'] ?></div>
                        <div style="color: #7f8c8d; font-size: 0.9em;"><?= htmlspecialchars($tip['tip_nalaza']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filteri -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; color: #2c3e50;">
            <i class="fa-solid fa-filter"></i> Filteri
        </h3>
        <form method="GET" action="/nalazi/moji-pacijenti" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            <div class="form-group">
                <label for="pacijent_id">Pacijent</label>
                <select name="pacijent_id" id="pacijent_id" class="select2">
                    <option value="">Svi pacijenti</option>
                    <?php foreach ($pacijenti_lista as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $pacijent_id == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['ime_prezime']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="datum_od">Datum od</label>
                <input type="date" name="datum_od" id="datum_od" value="<?= $datum_od ?>" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
            </div>
            <div class="form-group">
                <label for="datum_do">Datum do</label>
                <input type="date" name="datum_do" id="datum_do" value="<?= $datum_do ?>" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
            </div>
            <button type="submit" class="btn btn-search">
                <i class="fa-solid fa-search"></i> Filtriraj
            </button>
        </form>
        <?php if ($datum_od || $datum_do || $pacijent_id): ?>
        <div style="margin-top: 15px;">
            <a href="/nalazi/moji-pacijenti" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-times"></i> Ukloni filtere
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Lista nalaza -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">
                <i class="fa-solid fa-list"></i> Nalazi mojih pacijenata (<?= count($nalazi) ?> rezultata)
            </h3>
        </div>
        
        <?php if (empty($nalazi)): ?>
            <div style="padding: 40px; text-align: center; color: #7f8c8d;">
                <i class="fa-solid fa-file-medical" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <p style="font-size: 18px; margin: 0;">
                    <?php if ($datum_od || $datum_do || $pacijent_id): ?>
                        Nema nalaza koji zadovoljavaju filter kriterijume.
                    <?php else: ?>
                        Vaši pacijenti još uvek nemaju uploadovane nalaze.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <table class="table-standard">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Pacijent</th>
                        <th>Karton</th>
                        <th>Naziv nalaza</th>
                        <th>Opis</th>
                        <th>Dodao</th>
                        <th>Tip</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nalazi as $nalaz): ?>
                    <tr>
                        <td>
                            <strong><?= $nalaz['datum_upload_format'] ?></strong>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($nalaz['pacijent_ime']) ?></strong>
                        </td>
                        <td>
                            <span style="background: #289CC6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= htmlspecialchars($nalaz['broj_upisa']) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($nalaz['naziv']) ?>
                        </td>
                        <td>
                            <?php if ($nalaz['opis']): ?>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($nalaz['opis']) ?>">
                                    <?= htmlspecialchars(substr($nalaz['opis'], 0, 50)) ?><?= strlen($nalaz['opis']) > 50 ? '...' : '' ?>
                                </div>
                            <?php else: ?>
                                <span style="color: #bdc3c7;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small style="color: #7f8c8d;"><?= htmlspecialchars($nalaz['dodao_ime'] ?? 'Nepoznato') ?></small>
                        </td>
                        <td>
                            <?php
                            $ext = strtolower(pathinfo($nalaz['file_path'], PATHINFO_EXTENSION));
                            $ikona = 'fa-solid fa-file';
                            $boja = '#95a5a6';
                            $tip_text = 'Fajl';
                            
                            switch($ext) {
                                case 'pdf': 
                                    $ikona = 'fa-solid fa-file-pdf'; 
                                    $boja = '#e74c3c'; 
                                    $tip_text = 'PDF';
                                    break;
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                case 'gif':
                                    $ikona = 'fa-solid fa-images'; 
                                    $boja = '#289CC6'; 
                                    $tip_text = 'Slika';
                                    break;
                                case 'doc':
                                case 'docx':
                                    $ikona = 'fa-solid fa-file-word'; 
                                    $boja = '#255AA5'; 
                                    $tip_text = 'Word';
                                    break;
                            }
                            ?>
                            <span style="background: <?= $boja ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <i class="<?= $ikona ?>"></i> <?= $tip_text ?>
                            </span>
                        </td>
                        <td>
                            <a href="/<?= $nalaz['file_path'] ?>" target="_blank" 
                               class="btn btn-sm btn-view" 
                               title="Preuzmi/Otvori">
                                <i class="fa-solid fa-download"></i>
                            </a>
                            <a href="/kartoni/pregled?id=<?= $nalaz['pacijent_id'] ?>" 
                               class="btn btn-sm btn-edit" 
                               title="Karton pacijenta">
                                <i class="fa-solid fa-folder-open"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-add" 
                                    onclick="prikaziNalaz(<?= htmlspecialchars(json_encode($nalaz)) ?>)" 
                                    title="Detaljno">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Grupisano po pacijentima -->
    <?php if (!empty($nalazi_po_pacijentima)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; margin-top: 25px;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">
                <i class="fa-solid fa-users"></i> Pregled po pacijentima
            </h3>
        </div>
        <div style="padding: 20px;">
            <?php $index = 0; foreach ($nalazi_po_pacijentima as $pacijent_ime => $nalazi_pacijenta): ?>
            <div style="border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                <div style="background: #f8f9fa; padding: 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" 
                     onclick="togglePacijent(<?= $index ?>)">
                    <div>
                        <strong style="color: #2c3e50;"><?= htmlspecialchars($pacijent_ime) ?></strong>
                        <span style="background: #255AA5; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; margin-left: 10px;">
                            <?= count($nalazi_pacijenta) ?> nalaza
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down" id="arrow-<?= $index ?>" style="color: #7f8c8d; transition: transform 0.3s;"></i>
                </div>
                <div id="pacijent-<?= $index ?>" style="display: none; padding: 20px; border-top: 1px solid #e9ecef;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                        <?php foreach ($nalazi_pacijenta as $nalaz): ?>
                        <div style="border: 1px solid #e9ecef; border-radius: 8px; padding: 15px;">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                                <?= htmlspecialchars($nalaz['naziv']) ?>
                            </div>
                            <div style="color: #7f8c8d; font-size: 0.9em; margin-bottom: 10px;">
                                <i class="fa-solid fa-calendar"></i> <?= $nalaz['datum_upload_format'] ?>
                                <?php if ($nalaz['opis']): ?>
                                    <br><small><?= htmlspecialchars(substr($nalaz['opis'], 0, 100)) ?><?= strlen($nalaz['opis']) > 100 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <a href="/<?= $nalaz['file_path'] ?>" target="_blank" class="btn btn-add btn-sm">
                                    <i class="fa-solid fa-download"></i> Otvori
                                </a>
                                <span style="background: #289CC6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                    <?= strtoupper(pathinfo($nalaz['file_path'], PATHINFO_EXTENSION)) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php $index++; endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    

    <!-- Legenda -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-top: 25px;">
        <h4 style="margin: 0 0 15px 0; color: #2c3e50;">
            <i class="fa-solid fa-info-circle"></i> Napomene
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; color: #7f8c8d;">
            <div><i class="fa-solid fa-download" style="color: #289cc6;"></i> Preuzmi/Otvori nalaz</div>
            <div><i class="fa-solid fa-folder-open" style="color: #289cc6;"></i> Karton pacijenta</div>
            <div><i class="fa-solid fa-eye" style="color: #289cc6;"></i> Detaljno o nalazu</div>
        </div>
        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e9ecef;">
            <small style="color: #7f8c8d;">
                Prikazani su nalazi samo onih pacijenata sa kojima ste radili (imali termine ili tretmane).
            </small>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;" onclick="zatvoriSveModale()"></div>

<!-- Modal za pregled nalaza -->
<div id="nalaz-modal-view" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Detaljno o nalazu</h3>
        <div id="nalazModalBody">
            <!-- Content will be loaded here -->
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="#" id="nalazDownloadLink" target="_blank" class="btn btn-add">
                <i class="fa-solid fa-download"></i> Preuzmi
            </a>
            <button type="button" class="btn btn-secondary" onclick="zatvoriViewNalaz()">Zatvori</button>
        </div>
    </div>
</div>

<script>
function prikaziNalaz(nalaz) {
    const modalBody = document.getElementById('nalazModalBody');
    const downloadLink = document.getElementById('nalazDownloadLink');
    
    // Određuj tip fajla
    const ext = nalaz.file_path.split('.').pop().toLowerCase();
    let tipFajla = 'Dokument';
    let ikona = 'fa-solid fa-file';
    
    switch(ext) {
        case 'pdf': tipFajla = 'PDF dokument'; ikona = 'fa-solid fa-file-pdf'; break;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif': tipFajla = 'Slika/RTG'; ikona = 'fa-solid fa-images'; break;
        case 'doc':
        case 'docx': tipFajla = 'Word dokument'; ikona = 'fa-solid fa-file-word'; break;
    }
    
    modalBody.innerHTML = `
        <div class="tretman-view">
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <p><strong>Pacijent:</strong> ${nalaz.pacijent_ime}</p>
                    <p><strong>Karton:</strong> ${nalaz.broj_upisa}</p>
                    <p><strong>Tip:</strong> <i class="${ikona}"></i> ${tipFajla}</p>
                </div>
                <div>
                    <p><strong>Datum upload-a:</strong> ${nalaz.datum_upload_format}</p>
                    <p><strong>Dodao:</strong> ${nalaz.dodao_ime || 'Nepoznato'}</p>
                    <p><strong>Putanja:</strong> <small style="color: #7f8c8d;">${nalaz.file_path}</small></p>
                </div>
            </div>
            
            <div class="form-group">
                <label>Naziv nalaza</label>
                <div class="readonly-box">${nalaz.naziv}</div>
            </div>

            ${nalaz.opis ? `
            <div class="form-group">
                <label>Opis</label>
                <div class="readonly-box">${nalaz.opis}</div>
            </div>
            ` : ''}
        </div>
    `;
    
    downloadLink.href = '/' + nalaz.file_path;
    
    document.getElementById('nalaz-modal-view').style.display = 'block';
    document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriViewNalaz() {
    document.getElementById('nalaz-modal-view').style.display = 'none';
    document.getElementById('modal-overlay').style.display = 'none';
}

function zatvoriSveModale() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.getElementById('nalaz-modal-view').style.display = 'none';
}

function togglePacijent(index) {
    const content = document.getElementById('pacijent-' + index);
    const arrow = document.getElementById('arrow-' + index);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        arrow.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        arrow.style.transform = 'rotate(0deg)';
    }
}
</script>