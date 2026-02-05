<div class="naslov-dugme">
    <h2><?= $user['uloga'] === 'terapeut' ? 'Moji termini' : 'Lista termina' ?></h2>
    <div>
        <?php if ($user['uloga'] !== 'terapeut'): ?>
        <a href="/termini/kreiraj" class="btn btn-add"><i class="fa-solid fa-plus"></i> Novi termin</a>
        <?php endif; ?>
        <a href="/<?= $user['uloga'] === 'terapeut' ? 'dashboard' : 'termini' ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
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
            
            <!-- Sakrij terapeut dropdown ako je korisnik terapeut -->
            <?php if ($user['uloga'] !== 'terapeut'): ?>
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
            <?php endif; ?>
            
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
                <?php if ($user['uloga'] !== 'terapeut'): ?>
                <th>Terapeut</th>
                <?php endif; ?>
                <th>Usluga</th>
                <th>Cijena</th>
                <th>Status</th>
                <th>Plaćeno</th>
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
                    <?php if ($user['uloga'] !== 'terapeut'): ?>
                    <td><?= htmlspecialchars($t['terapeut_ime']) ?></td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($t['usluga_naziv']) ?></td>
                    <td>
                        <?php if ($t['placeno_iz_paketa']): ?>
                            <span style="display: inline-block; background: linear-gradient(90deg, #255AA5, #289CC6); color: white; padding: 6px 25px; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
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
                        <?php if ($t['placeno_iz_paketa']): ?>
                            <span style="color: #3498db;" title="Plaćeno iz paketa">
                                <i class="fa-solid fa-box"></i>
                            </span>
                        <?php elseif ($t['placeno']): ?>
                            <span style="color: #27ae60;" title="Plaćeno">
                                <i class="fa-solid fa-check-circle"></i>
                            </span>
                        <?php else: ?>
                            <span style="color: #e74c3c;" title="Nije plaćeno">
                                <i class="fa-solid fa-times-circle"></i>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['uloga'] !== 'terapeut'): ?>
                        <!-- Admin/recepcioner vidi sve opcije -->
                        <a href="/termini/uredi?id=<?= $t['id'] ?>&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-sm btn-edit" title="Uredi">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="potvrdiBrisanje(<?= $t['id'] ?>)" title="Obriši">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <?php endif; ?>
                        
                        <!-- Status akcije - svi mogu da menjaju status -->
                        <?php if ($t['status'] == 'zakazan'): ?>
                            <button class="btn btn-sm btn-warning" onclick="promeniStatus(<?= $t['id'] ?>, 'otkazan')" title="Otkaži">
                                <i class="fa-solid fa-times"></i>
                            </button>
                            <button class="btn btn-sm btn-success" onclick="promeniStatus(<?= $t['id'] ?>, 'obavljen')" title="Označi kao obavljen">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        <?php elseif ($t['status'] == 'otkazan' && $user['uloga'] !== 'terapeut'): ?>
                            <!-- Samo admin/recepcioner može da vrati otkazane termine -->
                            <button class="btn btn-sm btn-success" onclick="promeniStatus(<?= $t['id'] ?>, 'zakazan')" title="Vrati u zakazane">
                                <i class="fa-solid fa-undo"></i>
                            </button>
                        <?php endif; ?>
                        
                        <!-- Dugme za dodavanje tretmana - obavljeni termini sa kartonom -->
                        <?php if ($t['status'] == 'obavljen' && !empty($t['karton_id']) && (in_array($user['uloga'], ['admin', 'recepcioner']) || hasPermission($user, 'unos_tretmana'))): ?>
                            <button class="btn btn-sm btn-add" 
                                    onclick="otvoriModalTretman(<?= $t['karton_id'] ?>, '<?= htmlspecialchars($t['pacijent_ime']) ?>', '<?= $t['id'] ?>', '<?= date('Y-m-d', strtotime($t['datum_vrijeme'])) ?>', <?= $t['terapeut_id'] ?? 'null' ?>)" 
                                    title="Dodaj tretman">
                                <i class="fa-solid fa-notes-medical"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- DataTables konfiguracija sa stateSave i default 100 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('#tabela').DataTable({
            pageLength: 100,  // Default 100 prikaza
            stateSave: true,  // Pamti stranicu, sortiranje, pretragu
            stateDuration: 60 * 60 * 24, // Pamti 24 sata
            lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, "Sve"]],
            language: {
                search: "Pretraži:",
                lengthMenu: "Prikaži _MENU_ zapisa",
                info: "Prikazano _START_ do _END_ od _TOTAL_ zapisa",
                infoEmpty: "Nema podataka",
                infoFiltered: "(filtrirano od _MAX_ ukupno)",
                paginate: {
                    first: "Prva",
                    last: "Zadnja",
                    next: "Sljedeća",
                    previous: "Prethodna"
                },
                zeroRecords: "Nema rezultata pretrage"
            },
            order: [[1, 'asc']] // Sortiraj po datumu
        });
    }
});

function promeniStatus(terminId, noviStatus) {
    let poruka;
    switch(noviStatus) {
        case 'otkazan': poruka = 'otkazati'; break;
        case 'obavljen': poruka = 'označiti kao obavljen'; break;
        case 'zakazan': poruka = 'vratiti u zakazane'; break;
        default: poruka = 'promjeniti status';
    }
    
    if (confirm(`Da li ste sigurni da želite ${poruka} ovaj termin?`)) {
        // Kreiraj form i pošalji - dodaj return_url za povratak na istu stranicu
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
        
        // Dodaj return URL sa trenutnom stranicom i filterima
        const returnInput = document.createElement('input');
        returnInput.type = 'hidden';
        returnInput.name = 'return_url';
        returnInput.value = window.location.href;
        
        form.appendChild(idInput);
        form.appendChild(statusInput);
        form.appendChild(returnInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php if ($user['uloga'] !== 'terapeut'): ?>
<!-- Modal za brisanje - samo za admin/recepcioner -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<div id="brisanje-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <p>Da li ste sigurni da želite obrisati ovaj termin?</p>
        <form method="post" action="/termini/obrisi" style="margin-top: 20px;">
            <input type="hidden" name="id" id="id-brisanja">
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
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

document.getElementById('modal-overlay').addEventListener('click', function() {
    zatvoriModal();
    zatvoriModalTretman();
});
</script>

<?php else: ?>
<!-- Overlay za terapeute -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>
<script>
document.getElementById('modal-overlay').addEventListener('click', function() {
    zatvoriModalTretman();
});
</script>
<?php endif; ?>

<!-- Modal za dodavanje tretmana - dostupan svima sa dozvolom -->
<?php if (in_array($user['uloga'], ['admin', 'recepcioner']) || hasPermission($user, 'unos_tretmana')): ?>
<div id="tretman-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Dodaj tretman</h3>
        <p><strong>Pacijent:</strong> <span id="modal-pacijent-ime"></span></p>

        <form method="post" action="/kartoni/dodaj-tretman">
            <input type="hidden" name="karton_id" id="modal-karton-id">
            <input type="hidden" name="termin_id" id="modal-termin-id">
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
            
            <div class="form-group">
                <label for="terapeut_id">Terapeut</label>
                <select name="terapeut_id" id="modal-terapeut-select" required>
                    <option value="">-- Odaberi terapeuta --</option>
                    <?php foreach ($terapeuti as $terapeut): ?>
                        <option value="<?= $terapeut['id'] ?>"><?= htmlspecialchars($terapeut['ime'] . ' ' . $terapeut['prezime']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="datum_tretmana">Datum tretmana</label>
                <input type="date" name="datum_tretmana" id="modal-datum-tretmana" value="<?= date('Y-m-d') ?>" required>
                <small style="color: #7f8c8d;">Datum kada je tretman izvršen</small>
            </div>
            <hr>

            <div class="form-group">
                <label for="stanje_prije">Stanje prije tretmana</label>
                <textarea name="stanje_prije" rows="3" required placeholder="Opišite stanje pacijenta prije početka tretmana..."></textarea>
            </div>

            <div class="form-group">
                <label for="terapija">Sprovedena terapija</label>
                <textarea name="terapija" rows="4" required placeholder="Detaljno opišite sprovedenu terapiju, tehnike, vježbe..."></textarea>
            </div>

            <div class="form-group">
                <label for="stanje_poslije">Stanje nakon tretmana</label>
                <textarea name="stanje_poslije" rows="3" required placeholder="Opišite stanje pacijenta nakon tretmana..."></textarea>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="zatvoriModalTretman()">Otkaži</button>
                <button type="submit" class="btn btn-add">
                    <i class="fa-solid fa-save"></i> Snimi tretman
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function otvoriModalTretman(kartonId, pacijentIme, terminId, datumTermina, terapeutId) {
    document.getElementById('modal-karton-id').value = kartonId;
    document.getElementById('modal-pacijent-ime').textContent = pacijentIme;
    document.getElementById('modal-termin-id').value = terminId || '';
    document.getElementById('modal-datum-tretmana').value = datumTermina || new Date().toISOString().split('T')[0];
    
    // Postavi terapeuta ako je proslijeđen
    if (terapeutId) {
        document.getElementById('modal-terapeut-select').value = terapeutId;
    }
    
    document.getElementById('tretman-modal').style.display = 'block';
    document.getElementById('modal-overlay').style.display = 'block';
    
    // Focus na prvo polje
    setTimeout(() => {
        document.querySelector('#tretman-modal textarea[name="stanje_prije"]').focus();
    }, 100);
}

function zatvoriModalTretman() {
    document.getElementById('tretman-modal').style.display = 'none';
    document.getElementById('modal-overlay').style.display = 'none';
    document.querySelector('#tretman-modal form').reset();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        zatvoriModalTretman();
    }
});
</script>
<?php endif; ?>