<div class="naslov-dugme">
    <h2>Detalji paketa #<?= $paket['id'] ?></h2>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-primary" onclick="otvoriStatusModal()">
            <i class="fa-solid fa-edit"></i> Promijeni status
        </button>
        <button type="button" class="btn <?= $paket['placeno'] ? 'btn-warning' : 'btn-success' ?>" onclick="otvoriPlacenoModal()">
        <i class="fa-solid fa-<?= $paket['placeno'] ? 'times' : 'check' ?>"></i> 
        <?= $paket['placeno'] ? 'Označi neplaćeno' : 'Označi plaćeno' ?>
        </button>
        <button type="button" class="btn btn-danger" onclick="otvoriBrisanjeModal()">
            <i class="fa-solid fa-trash"></i> Obriši
        </button>
        <a href="/paketi" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Povratak
        </a>
    </div>
</div>

<div class="main-content-fw">
    
    <!-- Kartica sa osnovnim informacijama -->
    <div style="background: linear-gradient(135deg, #255AA5, #289CC6); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
        <div style="display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: start;">
            <div>
                <h3 style="margin: 0 0 15px 0; font-size: 1.8em;">
                    <i class="fa-solid fa-box"></i> <?= htmlspecialchars($paket['paket_naziv']) ?>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; opacity: 0.95;">
                    <div>
                        <small style="opacity: 0.8;">Pacijent:</small><br>
                        <strong style="font-size: 1.1em;"><?= htmlspecialchars($paket['pacijent_ime']) ?></strong><br>
                        <small><?= htmlspecialchars($paket['pacijent_email']) ?></small>
                    </div>
                    <div>
                        <small style="opacity: 0.8;">Kategorija:</small><br>
                        <strong><?= htmlspecialchars($paket['kategorija_naziv'] ?? 'N/A') ?></strong>
                    </div>
                    <div>
                        <small style="opacity: 0.8;">Period:</small><br>
                        <strong><?= ucfirst($paket['paket_period'] ?? 'N/A') ?></strong>
                    </div>
                    <div>
                        <small style="opacity: 0.8;">Cijena:</small><br>
                        <strong style="font-size: 1.2em;"><?= number_format($paket['paket_cijena'], 2, ',', '.') ?> KM</strong>
                    </div>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 12px;">
                    <div style="font-size: 3em; font-weight: bold; margin-bottom: 5px;">
                        <?= $paket['iskoristeno_termina'] ?>/<?= $paket['ukupno_termina'] ?>
                    </div>
                    <div style="opacity: 0.9;">termina</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress i status -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px;">
        
        <!-- Progress bar -->
        <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
            <h4 style="margin-top: 0;">
                <i class="fa-solid fa-chart-line"></i> Iskorištenost paketa
            </h4>
            <div style="margin: 20px 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span><strong><?= $procenat ?>%</strong> iskorišteno</span>
                    <span style="color: #7f8c8d;">
                        Preostalo: <strong><?= $paket['ukupno_termina'] - $paket['iskoristeno_termina'] ?></strong> termina
                    </span>
                </div>
                <div style="background: #ecf0f1; border-radius: 20px; height: 20px; overflow: hidden;">
                    <div style="background: <?= $procenat >= 100 ? '#e74c3c' : ($procenat >= 75 ? '#f39c12' : '#289CC6') ?>; height: 100%; width: <?= $procenat ?>%; transition: width 0.5s; border-radius: 20px;"></div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px;">
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #3498db; font-size: 2em; font-weight: bold;"><?= $paket['ukupno_termina'] ?></div>
                    <small style="color: #7f8c8d;">Ukupno</small>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #27ae60; font-size: 2em; font-weight: bold;"><?= $paket['iskoristeno_termina'] ?></div>
                    <small style="color: #7f8c8d;">Iskorišteno</small>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #333333; font-size: 2em; font-weight: bold;"><?= $paket['ukupno_termina'] - $paket['iskoristeno_termina'] ?></div>
                    <small style="color: #7f8c8d;">Preostalo</small>
                </div>
            </div>
        </div>

        <!-- Status i datumi -->
        <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
            <h4 style="margin-top: 0;">
                <i class="fa-solid fa-info-circle"></i> Status
            </h4>
            <div style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                <?php
                $status_boja = [
                    'aktivan' => '#27ae60',
                    'zavrsen' => '#289CC6',
                    'istekao' => '#e74c3c',
                    'otkazan' => '#333333'
                ];
                ?>
                <span style="display: inline-block; padding: 8px 16px; border-radius: 25px; font-size: 1.1em; font-weight: 600; background: <?= $status_boja[$paket['status']] ?>; color: white;">
                    <?= ucfirst($paket['status']) ?>
                </span>
                <span style="display: inline-block; padding: 8px 16px; border-radius: 25px; font-size: 1.1em; font-weight: 600; background: <?= $paket['placeno'] ? '#27ae60' : '#e74c3c' ?>; color: white;">
                    <i class="fa-solid fa-<?= $paket['placeno'] ? 'check' : 'times' ?>"></i>
                    <?= $paket['placeno'] ? 'Plaćeno' : 'Nije plaćeno' ?>
                </span>
            </div>
            
            <div style="color: #7f8c8d; font-size: 0.9em; line-height: 2;">
                <div><strong>Kupljeno:</strong><br><?= date('d.m.Y', strtotime($paket['datum_kupovine'])) ?></div>
                
                <?php if ($paket['datum_pocetka']): ?>
                <div style="margin-top: 10px;"><strong>Početak:</strong><br><?= date('d.m.Y', strtotime($paket['datum_pocetka'])) ?></div>
                <?php endif; ?>
                
                <?php if ($paket['datum_kraja']): ?>
                <div style="margin-top: 10px;"><strong>Istek:</strong><br><?= date('d.m.Y', strtotime($paket['datum_kraja'])) ?></div>
                <?php endif; ?>
                
                <div style="margin-top: 10px;"><strong>Kreirao:</strong><br><?= htmlspecialchars($paket['kreirao_ime']) ?></div>
            </div>
        </div>
    </div>

    <!-- Napomena -->
    <?php if (!empty($paket['napomena'])): ?>
    <div style="display:flex; flex-direction:column; row-gap:10px; background: #fcf4d7ff; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
        <strong><i class="fa-solid fa-sticky-note"></i> Napomena:</strong>
        <?= nl2br(htmlspecialchars($paket['napomena'])) ?>
    </div>
    <?php endif; ?>

    <!-- Lista termina -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
        <h4 style="margin-top: 0;">
            <i class="fa-solid fa-calendar-check"></i> Iskorišteni termini (<?= count($termini) ?>)
        </h4>
        
        <?php if (empty($termini)): ?>
            <div style="text-align: center; padding: 40px; color: #7f8c8d;">
                <i class="fa-solid fa-calendar-xmark" style="font-size: 3em; opacity: 0.3; margin-bottom: 15px; display: block;"></i>
                <p>Još nema iskorištenih termina iz ovog paketa.</p>
            </div>
        <?php else: ?>
            <table class="table-standard">
                <thead>
                    <tr>
                        <th>Datum i vrijeme</th>
                        <th>Terapeut</th>
                        <th>Usluga</th>
                        <th>Status</th>
                        <th>Iskorišteno</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($termini as $t): ?>
                    <tr>
                        <td><?= date('d.m.Y H:i', strtotime($t['datum_vrijeme'])) ?></td>
                        <td><?= htmlspecialchars($t['terapeut_ime'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($t['usluga_naziv'] ?? 'N/A') ?></td>
                        <td>
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 15px; font-size: 0.85em; background: <?= 
                                $t['status'] === 'zavrsen' ? '#27ae60' : 
                                ($t['status'] === 'otkazan' ? '#e74c3c' : '#255AA5') 
                            ?>; color: white;">
                                <?= ucfirst($t['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($t['datum_koriscenja'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<!-- Modal overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<!-- Modal za promjenu statusa -->
<div id="status-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fa-solid fa-exchange-alt" style="font-size: 48px; color: #3498db;"></i>
        </div>
        
        <h3 style="text-align: center; margin-bottom: 20px;">Promijeni status paketa</h3>
        
        <form method="POST" action="/paketi?action=update_status">
            <input type="hidden" name="id" value="<?= $paket['id'] ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Novi status:</label>
                <select name="status" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                    <option value="aktivan" <?= $paket['status'] === 'aktivan' ? 'selected' : '' ?>>Aktivan</option>
                    <option value="završen" <?= $paket['status'] === 'završen' ? 'selected' : '' ?>>Završen</option>
                    <option value="istekao" <?= $paket['status'] === 'istekao' ? 'selected' : '' ?>>Istekao</option>
                    <option value="otkazan" <?= $paket['status'] === 'otkazan' ? 'selected' : '' ?>>Otkazan</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
                <button type="submit" class="btn btn-primary">Spremi</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal za promjenu plaćanja -->
<div id="placeno-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fa-solid fa-<?= $paket['placeno'] ? 'times-circle' : 'check-circle' ?>" 
               style="font-size: 48px; color: <?= $paket['placeno'] ? '#e74c3c' : '#27ae60' ?>;"></i>
        </div>
        
        <h3 style="text-align: center; margin-bottom: 20px;">
            <?= $paket['placeno'] ? 'Označiti paket kao neplaćen?' : 'Označiti paket kao plaćen?' ?>
        </h3>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <div style="display: grid; gap: 8px;">
                <div><strong>Paket:</strong> <?= htmlspecialchars($paket['paket_naziv']) ?></div>
                <div><strong>Pacijent:</strong> <?= htmlspecialchars($paket['pacijent_ime']) ?></div>
                <div><strong>Cijena:</strong> <?= number_format($paket['paket_cijena'], 2, ',', '.') ?> KM</div>
            </div>
        </div>
        
        <form method="POST" action="/paketi?action=update_placeno">
            <input type="hidden" name="id" value="<?= $paket['id'] ?>">
            <?php if (!$paket['placeno']): ?>
                <input type="hidden" name="placeno" value="1">
            <?php endif; ?>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
                <button type="submit" class="btn <?= $paket['placeno'] ? 'btn-warning' : 'btn-success' ?>">
                    Da, <?= $paket['placeno'] ? 'označi neplaćeno' : 'označi plaćeno' ?>
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Modal za brisanje -->
<div id="brisanje-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fa-solid fa-exclamation-triangle" style="font-size: 48px; color: #e74c3c;"></i>
        </div>
        
        <h3 style="text-align: center; margin-bottom: 20px; color: #2c3e50;">
            Da li ste sigurni da želite obrisati ovaj paket?
        </h3>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <div style="display: grid; gap: 8px;">
                <div><strong>Paket:</strong> <?= htmlspecialchars($paket['paket_naziv']) ?></div>
                <div><strong>Pacijent:</strong> <?= htmlspecialchars($paket['pacijent_ime']) ?></div>
                <div><strong>Termini:</strong> <?= $paket['iskoristeno_termina'] ?>/<?= $paket['ukupno_termina'] ?> iskorišteno</div>
            </div>
        </div>
        
        <?php if ($paket['iskoristeno_termina'] > 0): ?>
        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <i class="fa-solid fa-exclamation-triangle" style="color: #856404;"></i>
            <span style="color: #856404;">
                Ovaj paket ima <strong><?= $paket['iskoristeno_termina'] ?></strong> iskorištenih termina. 
                Brisanjem paketa, termini će ostati ali više neće biti povezani s paketom.
            </span>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="/paketi?action=delete">
            <input type="hidden" name="id" value="<?= $paket['id'] ?>">
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
                <button type="submit" class="btn btn-danger">Da, obriši</button>
            </div>
        </form>
    </div>
</div>

<script>
function otvoriStatusModal() {
    document.getElementById('status-modal').style.display = 'block';
    document.getElementById('modal-overlay').style.display = 'block';
}

function otvoriBrisanjeModal() {
    document.getElementById('brisanje-modal').style.display = 'block';
    document.getElementById('modal-overlay').style.display = 'block';
}

function otvoriPlacenoModal() {
    document.getElementById('placeno-modal').style.display = 'block';
    document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriModal() {
    document.getElementById('status-modal').style.display = 'none';
    document.getElementById('brisanje-modal').style.display = 'none';
    document.getElementById('placeno-modal').style.display = 'none';
    document.getElementById('modal-overlay').style.display = 'none';
}
document.getElementById('modal-overlay').addEventListener('click', zatvoriModal);
</script>