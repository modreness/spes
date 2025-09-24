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
        <div class="alert-success">Raspored je uspešno obrisan.</div>
    <?php elseif ($_GET['msg'] === 'azuriran'): ?>
        <div class="alert-success">Status rasporeda je ažuriran.</div>
    <?php elseif ($_GET['msg'] === 'greska'): ?>
        <div class="greska">Greška pri obradi zahteva.</div>
    <?php endif; ?>
<?php endif; ?>

<div class="main-content-fw">
    <!-- Header -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #2c3e50;">Upravljanje rasporedima</h3>
            <p style="margin: 5px 0 0 0; color: #7f8c8d;">Sedmica: <?= $start_date->format('d.m.Y') ?> - <?= $end_date->format('d.m.Y') ?></p>
        </div>
        
        <form method="get" style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap;">
            <label for="datum_od" style="font-weight: 500; color: #34495e;">Sedmica počinje:</label>
            <input type="date" id="datum_od" name="datum_od" 
                   value="<?= htmlspecialchars($datum_od) ?>"
                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-search"></i> Prikaži
            </button>
        </form>
    </div>

    <?php if (empty($rasporedi_po_terapeutu)): ?>
        <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; color: #7f8c8d;">
            <i class="fa-solid fa-calendar-xmark" style="font-size: 3em; margin-bottom: 20px; opacity: 0.3;"></i>
            <p style="margin: 0; font-size: 1.2em;">Nema rasporeda za odabranu sedmicu</p>
            <p style="margin: 10px 0 0 0;">
                <a href="/raspored/dodaj?datum_od=<?= htmlspecialchars($datum_od) ?>" class="btn btn-add">
                    <i class="fa-solid fa-plus"></i> Dodaj raspored
                </a>
            </p>
        </div>
    <?php else: ?>
        <!-- Lista rasporeda po terapeutima -->
        <?php foreach ($rasporedi_po_terapeutu as $terapeut_ime => $terapeut_rasporedi): ?>
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 20px; overflow: hidden;">
            <!-- Header terapeuta -->
            <div style="background: linear-gradient(135deg, #3498db, #2980b9); padding: 20px; color: white;">
                <h3 style="margin: 0; font-size: 1.3rem;"><?= htmlspecialchars($terapeut_ime) ?></h3>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">
                    <?= htmlspecialchars($terapeut_rasporedi[0]['terapeut_email']) ?>
                    <span style="margin-left: 15px;">
                        Ukupno rasporeda: <?= count($terapeut_rasporedi) ?>
                    </span>
                </p>
            </div>

            <!-- Rasporedi terapeuta -->
            <div style="padding: 0;">
                <table class="table-standard" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Dan</th>
                            <th>Smena</th>
                            <th>Datum</th>
                            <th>Status</th>
                            <th>Kreiran</th>
                            <th style="text-align: center;">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terapeut_rasporedi as $r): ?>
                        <tr style="<?= !$r['aktivan'] ? 'opacity: 0.6; background: #f8f9fa;' : '' ?>">
                            <td>
                                <strong><?= ucfirst(htmlspecialchars($r['dan'])) ?></strong>
                            </td>
                            <td>
                                <span style="display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: 500; background: <?= 
                                    $r['smjena'] === 'jutro' ? '#f39c12' : 
                                    ($r['smjena'] === 'popodne' ? '#3498db' : '#9b59b6') 
                                ?>; color: white;">
                                    <?= ucfirst(htmlspecialchars($r['smjena'])) ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y', strtotime($r['datum_od'])) ?></td>
                            <td>
                                <?php if ($r['aktivan']): ?>
                                    <span style="color: #27ae60; font-weight: bold;">
                                        <i class="fa-solid fa-check-circle"></i> Aktivan
                                    </span>
                                <?php else: ?>
                                    <span style="color: #e74c3c; font-weight: bold;">
                                        <i class="fa-solid fa-times-circle"></i> Neaktivan
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="color: #7f8c8d; font-size: 0.9em;">
                                <?= date('d.m.Y H:i', strtotime($r['kreiran_at'] ?? 'now')) ?>
                            </td>
                            <td style="text-align: center;">
                                <!-- Toggle status -->
                                <form method="post" style="display: inline;" 
                                      onsubmit="return confirm('<?= $r['aktivan'] ? 'Deaktiviraj' : 'Aktiviraj' ?> ovaj raspored?')">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="raspored_id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn <?= $r['aktivan'] ? 'btn-secondary' : 'btn-add' ?> btn-no-margin" 
                                            style="margin-right: 5px; padding: 4px 8px; font-size: 0.8em;">
                                        <i class="fa-solid fa-<?= $r['aktivan'] ? 'pause' : 'play' ?>"></i>
                                    </button>
                                </form>

                                <!-- Obriši -->
                                <form method="post" style="display: inline;" 
                                      onsubmit="return confirm('Da li ste sigurni da želite obrisati ovaj raspored?\\n\\nTerapeut: <?= htmlspecialchars($terapeut_ime) ?>\\nDan: <?= ucfirst($r['dan']) ?>\\nSmena: <?= ucfirst($r['smjena']) ?>')">
                                    <input type="hidden" name="action" value="obrisi">
                                    <input type="hidden" name="raspored_id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-no-margin" 
                                            style="padding: 4px 8px; font-size: 0.8em;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
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

<!-- Potvrda modali -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dodatno osiguraj potvrde
    const deleteButtons = document.querySelectorAll('button[name="action"][value="obrisi"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const form = this.closest('form');
            const confirmed = confirm('PAŽNJA: Ova akcija će trajno obrisati raspored!\\n\\nDa li ste apsolutno sigurni?');
            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        });
    });
});
</script>