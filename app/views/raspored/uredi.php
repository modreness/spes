<div class="naslov-dugme">
    <h2>Uredi rasporede</h2>
    <div>
        <a href="/raspored/pregled?filter_datum_od=<?= htmlspecialchars($datum_od) ?>" class="btn btn-secondary">
            <i class="fa-solid fa-eye"></i> Pregled
        </a>
        <a href="/raspored" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Povratak
        </a>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'obrisan'): ?>
        <div class="uspjeh"><i class="fa-solid fa-check-circle"></i> Raspored je uspešno obrisan.</div>
    <?php elseif ($_GET['msg'] === 'azuriran'): ?>
        <div class="uspjeh"><i class="fa-solid fa-check-circle"></i> Raspored je uspešno ažuriran.</div>
    <?php elseif ($_GET['msg'] === 'greska'): ?>
        <div class="greska"><i class="fa-solid fa-times-circle"></i> Greška pri obradi zahteva.</div>
    <?php endif; ?>
<?php endif; ?>

<div class="main-content-fw">
    <!-- Header sa filterima -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #2c3e50;">Upravljanje rasporedima</h3>
            <p style="margin: 5px 0 0 0; color: #7f8c8d;">Sedmica: <?= $start_date->format('d.m.Y') ?> - <?= $end_date->format('d.m.Y') ?></p>
        </div>
        
        <form method="get" style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <label for="datum_od" style="font-weight: 500; color: #34495e;">Sedmica počinje:</label>
                <input type="date" id="datum_od" name="datum_od" 
                       value="<?= htmlspecialchars($datum_od) ?>"
                       style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px;">
            </div>
            
            <div style="display: flex; align-items: center; gap: 8px;">
                <label for="filter_terapeut" style="font-weight: 500; color: #34495e;">Terapeut:</label>
                <select id="filter_terapeut" name="filter_terapeut" 
                        style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; min-width: 200px;">
                    <option value="">Svi terapeuti</option>
                    <?php foreach ($svi_terapeuti as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $terapeut_filter == $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['ime'] . ' ' . $t['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-search">
                <i class="fa-solid fa-search"></i> Prikaži
            </button>
        </form>
    </div>

    <?php if (empty($rasporedi_po_terapeutu)): ?>
        <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; color: #7f8c8d;">
            <i class="fa-solid fa-calendar-xmark" style="font-size: 3em; margin-bottom: 20px; opacity: 0.3;"></i>
            <p style="margin: 0; font-size: 1.2em;">Nema rasporeda za odabranu sedmicu<?= !empty($terapeut_filter) ? ' i terapeuta' : '' ?></p>
            <p style="margin: 10px 0 0 0;">
                <a href="/raspored/dodaj?datum_od=<?= htmlspecialchars($datum_od) ?>" class="btn btn-add">
                    <i class="fa-solid fa-plus"></i> Dodaj raspored
                </a>
            </p>
        </div>
    <?php else: ?>
        <!-- Lista rasporeda po terapeutima -->
        <?php foreach ($rasporedi_po_terapeutu as $terapeut_id => $podaci): ?>
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 20px; overflow: hidden;">
            <!-- Header terapeuta -->
            <div style="background: linear-gradient(135deg, #289cc6, #255AA5); padding: 20px; color: white;">
                <h3 style="margin: 0; font-size: 1.3rem;"><?= htmlspecialchars($podaci['info']['ime']) ?></h3>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">
                    <?= htmlspecialchars($podaci['info']['email']) ?>
                    <span style="margin-left: 15px;">
                        Ukupno rasporeda: <?= count($podaci['dani']) ?>
                    </span>
                </p>
            </div>

            <!-- Rasporedi terapeuta -->
            <div style="padding: 0;">
                <table class="table-standard" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Dan</th>
                            <th>Smjena</th>
                            <th>Datum</th>
                            <th>Kreiran</th>
                            <th style="text-align: center; width: 120px;">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($podaci['dani'] as $r): ?>
                            <?php
                            // Izračunaj stvarni datum dana
                            $dan_offset = array_search($r['dan'], array_keys(dani()));
                            $stvarni_datum = date('d.m.Y', strtotime("+$dan_offset days", strtotime($datum_od)));
                            
                            // Boja smjene
                            $smjena_boja = [
                                'jutro' => '#289cc6',
                                'popodne' => '#255AA5', 
                                'vecer' => '#666666'
                            ];
                            ?>
                        <tr>
                            <td>
                                <strong><?= dani()[$r['dan']] ?></strong>
                            </td>
                            <td>
                                <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; background: <?= $smjena_boja[$r['smjena']] ?>; color: white;">
                                    <?= ucfirst($r['smjena']) ?>
                                </span>
                            </td>
                            <td><?= $stvarni_datum ?></td>
                            <td style="color: #7f8c8d; font-size: 0.9em;">
                                <?= date('d.m.Y H:i', strtotime($r['datum_unosa'])) ?>
                            </td>
                            <td style="text-align: center;">
                                <!-- Uredi -->
                                <a href="/raspored/uredi-pojedinacni?id=<?= $r['id'] ?>" 
                                   class="btn btn-primary btn-no-margin" 
                                   style="padding: 6px 12px; font-size: 0.85em; margin-right: 5px;"
                                   title="Uredi raspored">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                
                                <!-- Obriši -->
                                <button type="button" 
                                        class="btn btn-danger btn-no-margin" 
                                        style="padding: 6px 12px; font-size: 0.85em;"
                                        title="Obriši raspored"
                                        onclick="otvoriModalBrisanja(<?= $r['id'] ?>, '<?= htmlspecialchars($podaci['info']['ime'], ENT_QUOTES) ?>', '<?= dani()[$r['dan']] ?>', '<?= ucfirst($r['smjena']) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Dugme za dodavanje novog -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="/raspored/dodaj?datum_od=<?= htmlspecialchars($datum_od) ?>" class="btn btn-add">
                <i class="fa-solid fa-plus"></i> Dodaj novi raspored
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal za potvrdu brisanja -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>
<div id="brisanje-modal" class="modal" style="display: none;">
  <div class="modal-content">
    <h3 style="margin-top: 0; color: #e74c3c;">
        <i class="fa-solid fa-exclamation-triangle"></i> Potvrda brisanja
    </h3>
    <p id="modal-poruka">Jeste li sigurni da želite obrisati ovaj raspored?</p>
    <div id="modal-detalji" style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0; text-align: left;">
        <!-- Detalji će biti ubačeni JavaScript-om -->
    </div>
    <form method="post" action="" id="forma-brisanja">
      <input type="hidden" name="action" value="obrisi">
      <input type="hidden" name="raspored_id" id="id-brisanja">
      <div style="text-align: center; margin-top: 20px;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
        <button type="submit" class="btn btn-danger">
            <i class="fa-solid fa-trash"></i> Da, obriši
        </button>
      </div>
    </form>
  </div>
</div>

<!-- JavaScript -->
<script>
function otvoriModalBrisanja(rasporedId, terapeutIme, dan, smjena) {
    // Postavi ID za brisanje
    document.getElementById('id-brisanja').value = rasporedId;
    
    // Postavi detalje
    const detalji = `
        <div style="display: grid; grid-template-columns: 120px 1fr; gap: 10px; font-size: 0.95rem;">
            <strong>Terapeut:</strong> <span>${terapeutIme}</span>
            <strong>Dan:</strong> <span>${dan}</span>
            <strong>Smjena:</strong> <span>${smjena}</span>
        </div>
    `;
    document.getElementById('modal-detalji').innerHTML = detalji;
    
    // Prikaži modal
    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById('brisanje-modal').style.display = 'block';
}

function zatvoriModal() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.getElementById('brisanje-modal').style.display = 'none';
}

// Zatvori modal kada se klikne na overlay
document.getElementById('modal-overlay').addEventListener('click', zatvoriModal);
</script>