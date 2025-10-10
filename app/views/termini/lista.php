<div class="naslov-dugme">
    <h2>Lista termina</h2>
    <div>
        <a href="/termini/kreiraj" class="btn btn-add"><i class="fa-solid fa-plus"></i> Novi termin</a>
        <a href="/termini" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'azuriran'): ?>
    <div class="alert alert-success">Termin je uspješno ažuriran.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'otkazan'): ?>
    <div class="alert alert-success">Termin je otkazan.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-warning">Greška pri operaciji.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'obavljen'): ?>
    <div class="alert alert-success">Termin je označen kao obavljen.</div>
<?php endif; ?>

<div class="main-content-fw">
    <!-- Filteri -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <form method="get" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            
            <div class="form-group">
                <label for="datum_od">Datum od</label>
                <input type="date" id="datum_od" name="datum_od" value="<?= htmlspecialchars($datum_od) ?>" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
            </div>
            
            <div class="form-group">
                <label for="datum_do">Datum do</label>
                <input type="date" id="datum_do" name="datum_do" value="<?= htmlspecialchars($datum_do) ?>" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
                    <option value="">Svi statusi</option>
                    <option value="zakazan" <?= $status_filter == 'zakazan' ? 'selected' : '' ?>>Zakazan</option>
                    <option value="otkazan" <?= $status_filter == 'otkazan' ? 'selected' : '' ?>>Otkazan</option>
                    <option value="obavljen" <?= $status_filter == 'obavljen' ? 'selected' : '' ?>>Obavljen</option>
                    <option value="slobodan" <?= $status_filter == 'slobodan' ? 'selected' : '' ?>>Slobodan</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="terapeut">Terapeut</label>
                <select id="terapeut" name="terapeut" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
                    <option value="">Svi terapeuti</option>
                    <?php foreach ($terapeuti as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $terapeut_filter == $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['ime'] . ' ' . $t['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-search">Filtriraj</button>
        </form>
    </div>

    <!-- Tabela termina -->
    <table id="tabela" class="table-standard">
        <thead>
            <tr>
                <th>ID</th>
                <th>Datum i vrijeme</th>
                <th>Pacijent</th>
                <th>Terapeut</th>
                <th>Usluga</th>
                <th>Cijena</th>
                <th>Status</th>
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($termini as $t): ?>
                <?php 
                $status_colors = [
                    'zakazan' => '#27ae60',
                    'otkazan' => '#e74c3c',
                    'obavljen' => '#95a5a6',
                    'slobodan' => '#f39c12'
                ];
                ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td>
                        <div style="font-weight: 600;"><?= date('d.m.Y', strtotime($t['datum_vrijeme'])) ?></div>
                        <div style="color: #7f8c8d; font-size: 13px;"><?= date('H:i', strtotime($t['datum_vrijeme'])) ?></div>
                    </td>
                    <td><?= htmlspecialchars($t['pacijent_ime']) ?></td>
                    <td><?= htmlspecialchars($t['terapeut_ime']) ?></td>
                    <td><?= htmlspecialchars($t['usluga_naziv']) ?></td>
                    <td>
                        <?php if ($t['placeno_iz_paketa']): ?>
                            <span style="display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                <i class="fa-solid fa-box"></i> Paket
                            </span>
                        <?php else: ?>
                            <span style="font-weight: 600;"><?= number_format($t['stvarna_cijena'] ?? $t['usluga_cijena'], 2, ',', '.') ?> KM</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="background: <?= $status_colors[$t['status']] ?? '#95a5a6' ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                            <?= ucfirst($t['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="/termini/uredi?id=<?= $t['id'] ?>" class="btn btn-sm btn-edit" title="Uredi">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="potvrdiBrisanje(<?= $t['id'] ?>)" title="Obriši">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <?php if ($t['status'] == 'zakazan'): ?>
                            <button class="btn btn-sm btn-warning" onclick="promeniStatus(<?= $t['id'] ?>, 'otkazan')" title="Otkaži">
                                <i class="fa-solid fa-times"></i>
                            </button>
                            <button class="btn btn-sm btn-success" onclick="promeniStatus(<?= $t['id'] ?>, 'obavljen')" title="Označi kao obavljen">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function promeniStatus(terminId, noviStatus) {
    const poruka = noviStatus === 'otkazan' ? 'otkazati' : 'označiti kao obavljen';
    
    if (confirm(`Da li ste sigurni da želite ${poruka} ovaj termin?`)) {
        // Kreiraj form i pošalji
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/termini/status';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = terminId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = noviStatus;
        
        form.appendChild(idInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<!-- Modal za brisanje -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<div id="brisanje-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <p>Da li ste sigurni da želite obrisati ovaj termin?</p>
        <form method="post" action="/termini/obrisi" style="margin-top: 20px;">
            <input type="hidden" name="id" id="id-brisanja">
            <div style="text-align: center;">
                <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
                <button type="submit" class="btn btn-danger">Da, obriši</button>
            </div>
        </form>
    </div>
</div>

<script>
function potvrdiBrisanje(id) {
    document.getElementById('id-brisanja').value = id;
    document.getElementById('brisanje-modal').style.display = 'block';
    document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriModal() {
    document.getElementById('brisanje-modal').style.display = 'none';
    document.getElementById('modal-overlay').style.display = 'none';
}

document.getElementById('modal-overlay').addEventListener('click', zatvoriModal);
</script>