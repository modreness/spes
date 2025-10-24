<div class="naslov-dugme">
    <h2>Moji tretmani</h2>
    <a href="/dashboard" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-warning">Ovaj tretman nije moguće obrisati.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'obrisan'): ?>
    <div class="alert alert-success">Tretman je uspješno obrisan.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'tretman-ok'): ?>
    <div class="notifikacija uspjeh">Tretman je uspješno dodan.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'tretman-greska'): ?>
    <div class="notifikacija greska">Greška pri dodavanju tretmana.</div>
<?php endif; ?>

<div class="main-content-fw">
    <!-- Statistike -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #255AA5);">
            <h3>Ukupno tretmana</h3>
            <div class="stat-number"><?= $ukupno_svih_tretmana ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #289CC6);">
            <h3>Ovaj mjesec</h3>
            <div class="stat-number"><?= $tretmani_ovaj_mesec ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #289CC6);">
            <h3>Broj pacijenata</h3>
            <div class="stat-number"><?= $broj_pacijenata ?></div>
        </div>
    </div>

    <!-- Filteri -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; color: #2c3e50;">
            <i class="fa-solid fa-filter"></i> Filteri
        </h3>
        <form method="GET" action="/tretmani/moji" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
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
                <input type="date" name="datum_od" id="datum_od" class="terapeut-tretmani-date" value="<?= $datum_od ?>">
            </div>
            <div class="form-group">
                <label for="datum_do">Datum do</label>
                <input type="date" name="datum_do" id="datum_do" class="terapeut-tretmani-date" value="<?= $datum_do ?>">
            </div>
            <button type="submit" class="btn btn-search">
                <i class="fa-solid fa-search"></i> Filtriraj
            </button>
        </form>
        <?php if ($datum_od || $datum_do || $pacijent_id): ?>
        <div style="margin-top: 15px;">
            <a href="/tretmani/moji" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-times"></i> Ukloni filtere
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Lista tretmana -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">
                <i class="fa-solid fa-list"></i> Lista tretmana (<?= $ukupno_tretmana ?> rezultata)
            </h3>
            <?php if ($ukupno_stranica > 1): ?>
            <div style="margin-top: 10px;">
                <span class="btn btn-secondary btn-sm">Stranica <?= $page ?> od <?= $ukupno_stranica ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($moji_tretmani)): ?>
            <div style="padding: 40px; text-align: center; color: #7f8c8d;">
                <i class="fa-solid fa-info-circle" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <p style="font-size: 18px; margin: 0;">
                    <?php if ($datum_od || $datum_do || $pacijent_id): ?>
                        Nema tretmana koji zadovoljavaju filter kriterijume.
                    <?php else: ?>
                        Niste još uvek unosili tretmane.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <table class="table-standard">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Pacijent</th>
                        <th>Broj kartona</th>
                        <th>Stanje prije</th>
                        <th>Terapija</th>
                        <th>Stanje poslije</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moji_tretmani as $tretman): ?>
                    <tr>
                        <td>
                            <strong><?= $tretman['datum_format'] ?></strong>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($tretman['pacijent_ime']) ?></strong>
                            <?php if ($tretman['dijagnoza']): ?>
                                <br><small style="color: #7f8c8d;"><?= htmlspecialchars($tretman['dijagnoza']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="background: #289CC6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= htmlspecialchars($tretman['broj_upisa']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($tretman['stanje_prije']) ?>">
                                <?= htmlspecialchars(substr($tretman['stanje_prije'], 0, 50)) ?><?= strlen($tretman['stanje_prije']) > 50 ? '...' : '' ?>
                            </div>
                        </td>
                        <td>
                            <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($tretman['terapija']) ?>">
                                <?= htmlspecialchars(substr($tretman['terapija'], 0, 60)) ?><?= strlen($tretman['terapija']) > 60 ? '...' : '' ?>
                            </div>
                        </td>
                        <td>
                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($tretman['stanje_poslije']) ?>">
                                <?= htmlspecialchars(substr($tretman['stanje_poslije'], 0, 50)) ?><?= strlen($tretman['stanje_poslije']) > 50 ? '...' : '' ?>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-view"
                                    onclick="prikaziTretman(<?= $tretman['id'] ?>)" 
                                    title="Detaljno">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <a href="/kartoni/pregled?id=<?= $tretman['karton_id'] ?>" 
                               class="btn btn-sm btn-edit" 
                               title="Karton">
                                <i class="fa-solid fa-folder-open"></i>
                            </a>
                            
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginacija -->
            <?php if ($ukupno_stranica > 1): ?>
            <div style="padding: 20px; border-top: 1px solid #e9ecef; text-align: center;">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $pacijent_id ? '&pacijent_id=' . $pacijent_id : '' ?><?= $datum_od ? '&datum_od=' . $datum_od : '' ?><?= $datum_do ? '&datum_do=' . $datum_do : '' ?>" 
                   class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-chevron-left"></i> Prethodna
                </a>
                <?php endif; ?>

                <?php 
                $start = max(1, $page - 2);
                $end = min($ukupno_stranica, $page + 2);
                for ($i = $start; $i <= $end; $i++): 
                ?>
                <a href="?page=<?= $i ?><?= $pacijent_id ? '&pacijent_id=' . $pacijent_id : '' ?><?= $datum_od ? '&datum_od=' . $datum_od : '' ?><?= $datum_do ? '&datum_do=' . $datum_do : '' ?>" 
                   class="btn btn-sm <?= $i == $page ? 'btn-add' : 'btn-secondary' ?>" style="margin: 0 2px;">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <?php if ($page < $ukupno_stranica): ?>
                <a href="?page=<?= $page + 1 ?><?= $pacijent_id ? '&pacijent_id=' . $pacijent_id : '' ?><?= $datum_od ? '&datum_od=' . $datum_od : '' ?><?= $datum_do ? '&datum_do=' . $datum_do : '' ?>" 
                   class="btn btn-secondary btn-sm">
                    Sljedeća <i class="fa-solid fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;" onclick="zatvoriSveModale()"></div>

<!-- Modal za pregled pojedinačnog tretmana -->
<div id="tretman-modal-view" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Detalji tretmana</h3>
        <div id="tretmanModalBody">
            <!-- Content will be loaded here -->
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="zatvoriViewTretman()">Zatvori</button>
        </div>
    </div>
</div>

<script>
function prikaziTretman(tretmanId) {
    const tretmani = <?= json_encode($moji_tretmani) ?>;
    const tretman = tretmani.find(t => t.id == tretmanId);
    
    if (tretman) {
        const modalBody = document.getElementById('tretmanModalBody');
        modalBody.innerHTML = `
            <div class="tretman-view">
                <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <p><strong>Pacijent:</strong> ${tretman.pacijent_ime}</p>
                        <p><strong>Datum:</strong> ${tretman.datum_format}</p>
                        <p><strong>Karton:</strong> ${tretman.broj_upisa}</p>
                        ${tretman.dijagnoza ? '<p><strong>Dijagnoza:</strong> ' + tretman.dijagnoza + '</p>' : ''}
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Stanje prije tretmana</label>
                    <div class="readonly-box">${tretman.stanje_prije}</div>
                </div>

                <div class="form-group">
                    <label>Sprovedena terapija</label>
                    <div class="readonly-box">${tretman.terapija}</div>
                </div>

                <div class="form-group">
                    <label>Stanje nakon tretmana</label>
                    <div class="readonly-box">${tretman.stanje_poslije}</div>
                </div>
            </div>
        `;
        
        document.getElementById('tretman-modal-view').style.display = 'block';
        document.getElementById('modal-overlay').style.display = 'block';
    }
}

function zatvoriViewTretman() {
    document.getElementById('tretman-modal-view').style.display = 'none';
    document.getElementById('modal-overlay').style.display = 'none';
}

function zatvoriSveModale() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.getElementById('tretman-modal-view').style.display = 'none';
}

// Notifikacije
const notif = document.querySelector('.notifikacija');
if (notif) {
    setTimeout(() => notif.remove(), 3500);
}
</script>