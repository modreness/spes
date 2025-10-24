<div class="terapeut-dashboard">
    <!-- Uvodni naslov -->
    <div class="terapeut-header">
        <h2>Terapeut Dashboard</h2>
        <p>Dobro došli, <?= htmlspecialchars($user['ime'] . ' ' . $user['prezime']) ?> - Vaši termini i pacijenti</p>
    </div>

    <!-- Statisticki pregled -->
    <div class="terapeut-stats-grid">
        <div class="terapeut-stat-card terapeut-stat-blue">
            <div class="terapeut-stat-content">
                <div class="terapeut-stat-number"><?= $dashboard_data['broj_termina_danas'] ?? 0 ?></div>
                <div class="terapeut-stat-label">Termini danas</div>
                <div class="terapeut-stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            </div>
        </div>
        
        <div class="terapeut-stat-card terapeut-stat-green">
            <div class="terapeut-stat-content">
                <div class="terapeut-stat-number"><?= $dashboard_data['broj_termina_sutra'] ?? 0 ?></div>
                <div class="terapeut-stat-label">Termini sutra</div>
                <div class="terapeut-stat-icon"><i class="fa-solid fa-calendar-plus"></i></div>
            </div>
        </div>
        
        <div class="terapeut-stat-card terapeut-stat-purple">
            <div class="terapeut-stat-content">
                <div class="terapeut-stat-number"><?= $dashboard_data['broj_mojih_pacijenata'] ?? 0 ?></div>
                <div class="terapeut-stat-label">Moji pacijenti</div>
                <div class="terapeut-stat-icon"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
        
        <div class="terapeut-stat-card terapeut-stat-orange">
            <div class="terapeut-stat-content">
                <div class="terapeut-stat-number"><?= $dashboard_data['tretmani_ovaj_mesec'] ?? 0 ?></div>
                <div class="terapeut-stat-label">Tretmani ovaj mesec</div>
                <div class="terapeut-stat-icon"><i class="fa-solid fa-notes-medical"></i></div>
            </div>
        </div>
    </div>

    <!-- Glavne akcije -->
    <div class="admin-action-cards">
        <div class="admin-action-card">
            <h3>Moj raspored</h3>
            <p>Pregled rasporeda rada </p>
            <div class="admin-action-buttons">
                <a href="/raspored/moj" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-calendar-days"></i> Moj raspored
                </a>
                
            </div>
        </div>
        
        <div class="admin-action-card">
            <h3>Moji pacijenti</h3>
            <p>Lista svih pacijenata sa kojima radite</p>
            <div class="admin-action-buttons">
                <a href="/kartoni/moji" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-users"></i> Svi pacijenti
                </a>
                
            </div>
        </div>
        
        <div class="admin-action-card">
            <h3>Tretmani i izvještaji</h3>
            <p>Historija tretmana i statistike</p>
            <div class="admin-action-buttons">
                <a href="/tretmani/moji" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-notes-medical"></i> Moji tretmani
                </a>
                
            </div>
        </div>
        
        <div class="admin-action-card">
            <h3>Nalazi pacijenata</h3>
            <p>Pregled nalaza mojih pacijenata</p>
            <div class="admin-action-buttons">
                <a href="/nalazi/moji-pacijenti" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-file-medical"></i> Pregled nalaza
                </a>
                
            </div>
        </div>
    </div>

    <!-- Pregled aktivnosti -->
    <div class="admin-activity-grid">
        <!-- Moji termini danas -->
        <div class="admin-activity-section">
            <div class="admin-activity-header">
                <h3>Moji termini danas</h3>
                <div class="admin-header-actions">
                    <a href="/termini/kalendar" class="admin-btn admin-btn-outline admin-btn-xs">Kalendar</a>
                </div>
            </div>
            <div class="admin-activity-content">
                <?php if (empty($dashboard_data['moji_termini_danas'])): ?>
                    <div class="admin-empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        <p>Nema termina za danas</p>
                        <p style="font-size: 0.9em; color: #999;">Možete se fokusirati na administrativne zadatke</p>
                    </div>
                <?php else: ?>
                    <div class="admin-timeline">
                        <?php 
                        $now = new DateTime();
                        foreach ($dashboard_data['moji_termini_danas'] as $termin): 
                            $termin_time = new DateTime($termin['datum'] . ' ' . $termin['vrijeme']);
                            $is_current = ($termin_time <= $now && $now <= $termin_time->modify('+1 hour'));
                        ?>
                        <div class="admin-timeline-item admin-status-<?= $termin['status'] ?> <?= $is_current ? 'admin-current-termin' : '' ?>">
                            <div class="admin-timeline-time">
                                <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                                <?php if ($is_current): ?>
                                    <span style="background: #28a745; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7em; margin-left: 5px;">SADA</span>
                                <?php endif; ?>
                            </div>
                            <div class="admin-timeline-content">
                                <div class="admin-timeline-title">
                                    <?= htmlspecialchars($termin['pacijent_ime']) ?>
                                </div>
                                <div class="admin-timeline-details">
                                    <?= htmlspecialchars($termin['usluga']) ?>
                                </div>
                                <div class="admin-timeline-status">
                                    <span class="admin-status-badge admin-status-<?= $termin['status'] ?>">
                                        <?= ucfirst($termin['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="admin-timeline-actions">
                                <?php if ($termin['karton_id']): ?>
                                <a href="/kartoni/pregled?id=<?= $termin['karton_id'] ?>" class="admin-btn admin-btn-outline admin-btn-xs">
                                    <i class="fa-solid fa-folder-open"></i>
                                </a>
                                <?php endif; ?>

                                <?php if ($termin['status'] === 'zakazan'): ?>
                                <button onclick="promeniStatus(<?= $termin['id'] ?>, 'obavljen')" class="admin-btn admin-btn-primary admin-btn-xs">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <?php elseif ($termin['status'] === 'u_toku'): ?>
                                <button onclick="promeniStatus(<?= $termin['id'] ?>, 'obavljen')" class="admin-btn admin-btn-success admin-btn-xs">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($termin['status'] === 'obavljen' && $termin['karton_id'] && hasPermission($user, 'unos_tretmana')): ?>
                                <button type="button" 
                                        class="admin-btn admin-btn-success admin-btn-xs"  data-terapeut-id="<?= $termin['terapeut_id'] ?? '' ?>"
                                        data-terapeut-ime="<?= htmlspecialchars($termin['terapeut_ime'] ?? '') ?>"
                                        onclick="otvoriModalTretman(<?= $termin['id'] ?>, '<?= htmlspecialchars($termin['pacijent_ime']) ?>', <?= $termin['karton_id'] ?>)">
                                    <i class="fa-solid fa-notes-medical"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="admin-activity-footer">
                        <a href="/termini/lista" class="admin-btn admin-btn-outline admin-btn-sm">Svi moji termini</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Brzi pregled - ČISTA VERZIJA -->
        <div class="admin-activity-section">
            <div class="admin-activity-header">
                <h3>Moj raspored ove sedmice</h3>
            </div>
            <div class="admin-activity-content">
                
                <!-- Danas -->
                <?php if (!empty($dashboard_data['moja_smjena_danas'])): ?>
                <div style="margin-bottom: 20px; padding: 15px; background: #e7f3ff; border-radius: 8px; border-left: 4px solid #4e73df;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600; color: #4e73df; font-size: 0.9em;">DANAS</div>
                            <div style="font-size: 1.1em; color: #2c3e50;">
                                <?= ucfirst($dashboard_data['moja_smjena_danas']['smjena']) ?>
                            </div>
                        </div>
                        <div style="text-align: right; color: #666;">
                            <?php if ($dashboard_data['moja_smjena_danas']['pocetak'] && $dashboard_data['moja_smjena_danas']['kraj']): ?>
                                <?= date('H:i', strtotime($dashboard_data['moja_smjena_danas']['pocetak'])) ?> - 
                                <?= date('H:i', strtotime($dashboard_data['moja_smjena_danas']['kraj'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Raspored sedmice - jednostavno -->
                <div style="margin-bottom: 20px;">
                    <?php 
                    $week_schedule = $dashboard_data['raspored_ova_sedmica'] ?? [];
                    $dana_nazivi = ['pon' => 'Ponedjeljak', 'uto' => 'Utorak', 'sri' => 'Srijeda', 'cet' => 'Četvrtak', 'pet' => 'Petak', 'sub' => 'Subota', 'ned' => 'Nedjelja'];
                    
                    if (!empty($week_schedule)): ?>
                        <?php foreach ($week_schedule as $raspored): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; margin-bottom: 8px; background: #f8f9fa; border-radius: 6px; border-left: 3px solid #28a745;">
                            <div style="font-weight: 600; color: #2c3e50;">
                                <?= $dana_nazivi[$raspored['dan']] ?? ucfirst($raspored['dan']) ?>
                            </div>
                            <div style="color: #666;">
                                <?= ucfirst($raspored['smjena']) ?>
                                <?php if ($raspored['pocetak'] && $raspored['kraj']): ?>
                                    (<?= date('H:i', strtotime($raspored['pocetak'])) ?>-<?= date('H:i', strtotime($raspored['kraj'])) ?>)
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 30px; color: #999; font-style: italic;">
                            <i class="fa-solid fa-calendar-xmark"></i><br>
                            Nema rasporeda za ovu sedmicu
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Termini sutra -->
                <?php if (!empty($dashboard_data['termini_sutra'])): ?>
                <div style="margin-bottom: 20px; padding: 15px; background: #fff8e1; border-radius: 8px; border-left: 4px solid #f6c23e;">
                    <h4 style="color: #f6c23e; font-size: 0.9em; margin-bottom: 12px;">
                        <i class="fa-solid fa-calendar-plus"></i> Sutra (<?= count($dashboard_data['termini_sutra']) ?>)
                    </h4>
                    <?php foreach (array_slice($dashboard_data['termini_sutra'], 0, 4) as $termin): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div style="font-weight: 600; color: #2c3e50;">
                            <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                        </div>
                        <div style="font-size: 0.9em; color: #666; text-align: right;">
                            <?= htmlspecialchars($termin['pacijent_ime']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($dashboard_data['termini_sutra']) > 4): ?>
                    <div style="text-align: center; margin-top: 12px; padding-top: 8px; border-top: 1px solid #f6c23e;">
                        <small style="color: #f6c23e;">+<?= count($dashboard_data['termini_sutra']) - 4 ?> više termina</small>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Statistike -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="text-align: center; padding: 15px; background: #e3f2fd; border-radius: 8px;">
                        <div style="font-size: 1.5em; font-weight: 600; color: #1976d2; margin-bottom: 5px;">
                            <?= $dashboard_data['termini_ova_sedmica'] ?? 0 ?>
                        </div>
                        <div style="font-size: 0.85em; color: #666;">Termina ove sedmice</div>
                    </div>
                    <div style="text-align: center; padding: 15px; background: #e8f5e8; border-radius: 8px;">
                        <div style="font-size: 1.5em; font-weight: 600; color: #2e7d32; margin-bottom: 5px;">
                            <?= $dashboard_data['radnih_dana_sedmica'] ?? 0 ?>
                        </div>
                        <div style="font-size: 0.85em; color: #666;">Radnih dana</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sedmični pregled -->
    <?php if (!empty($dashboard_data['termini_sedmica'])): ?>
    <div class="admin-quick-nav">
        <div class="admin-quick-nav-header">
            <h3>Termini ove sedmice</h3>
        </div>
        
        <div class="admin-quick-nav-grid">
            <?php 
            $grouped_by_date = [];
            foreach ($dashboard_data['termini_sedmica'] as $termin) {
                $date = $termin['datum'];
                $grouped_by_date[$date][] = $termin;
            }
            
            // Generiši cijelu sedmicu
            $week_days = [];
            for ($i = 1; $i <= 7; $i++) {
                $day_date = date('Y-m-d', strtotime('this week +' . ($i-1) . ' days'));
                $week_days[$day_date] = $grouped_by_date[$day_date] ?? [];
            }
            ?>
            <?php foreach ($week_days as $date => $termini): ?>
    <a href="/termini/lista?datum_od=<?= urlencode($date) ?>&datum_do=<?= urlencode($date) ?>" 
       class="admin-quick-link"
       style="display: block; text-decoration: none; 
              <?= date('Y-m-d') === $date ? 'background: linear-gradient(135deg, #255AA5, #255AA5); color: white;' : '' ?> 
              <?= empty($termini) ? 'opacity: 0.6;' : '' ?>">
        <div style="text-align: center; color: inherit;">
            <i class="fa-solid fa-calendar-day"></i>
            <div style="font-weight: 600; margin: 5px 0;">
                <?= date('d.m', strtotime($date)) ?>
            </div>
            <div style="font-size: 0.8em;">
                <?= ['Nedjelja', 'Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota'][date('w', strtotime($date))] ?>
            </div>
            <div style="font-size: 0.9em; margin-top: 5px;">
                <?php if (empty($termini)): ?>
                    Slobodan
                <?php else: ?>
                    <?= count($termini) ?> termin<?= count($termini) > 1 ? 'a' : '' ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($termini) && count($termini) <= 2): ?>
                <?php foreach ($termini as $termin): ?>
                    <div style="font-size: 0.75em; margin-top: 3px; 
                                <?= date('Y-m-d') === $date ? 'color: #fff;' : 'color: #858796;' ?>">
                        <?= date('H:i', strtotime($termin['vrijeme'])) ?> 
                        <?= htmlspecialchars($termin['pacijent_ime']) ?> <?= htmlspecialchars($termin['pacijent_prezime'] ?? '') ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </a>
<?php endforeach; ?>

        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<!-- Modal za tretman -->
 <?php if (hasPermission($user, 'unos_tretmana')): ?>
<div id="tretman-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Dodaj tretman</h3>
        <p><strong>Pacijent:</strong> <span id="modal-ime"></span></p>
        <p><strong>Karton ID:</strong> <span id="modal-karton-id-display"></span></p>

        <form method="post" action="/kartoni/dodaj-tretman">
            <input type="hidden" name="karton_id" id="modal-karton-id">
            <input type="hidden" name="termin_id" id="modal-termin-id">

            <div class="form-group">
                <label for="terapeut_id">Terapeut</label>
                <select name="terapeut_id" id="modal-terapeut-select" required>
                    <option value="">-- Odaberi terapeuta --</option>
                    <?php foreach ($svi_terapeuti as $terapeut): ?>
                        <option value="<?= $terapeut['id'] ?>"><?= htmlspecialchars($terapeut['ime'] . ' ' . $terapeut['prezime']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <hr>

            <div class="form-group">
                <label for="stanje_prije">Stanje prije tretmana</label>
                <textarea name="stanje_prije" rows="3" required placeholder="Opišite stanje pacijenta prije početka tretmana..."></textarea>
            </div>

            <div class="form-group">
                <label for="terapija">Sprovedena terapija</label>
                <textarea name="terapija" rows="4" required placeholder="Detaljno opišite sprovedenu terapiju, tehnike, vežbe..."></textarea>
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
<?php endif; ?>
<script>

<?php if (hasPermission($user, 'unos_tretmana')): ?>
function otvoriModalTretman(terminId, imePrezime, kartonId) {
    // Dohvati terapeut_id iz data atributa dugmeta
    const dugme = event.target.closest('button');
    const terapeutId = dugme.getAttribute('data-terapeut-id');

    document.getElementById('modal-karton-id').value = kartonId;
    document.getElementById('modal-termin-id').value = terminId;
    document.getElementById('modal-ime').textContent = imePrezime;
    document.getElementById('modal-karton-id-display').textContent = kartonId;

    // Postavi odabranog terapeuta u select
    const terapeutSelect = document.getElementById('modal-terapeut-select');
    if (terapeutId) {
        terapeutSelect.value = terapeutId;
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

// Zatvaranje modala na click outside
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modal-overlay').addEventListener('click', function() {
        zatvoriModalTretman();
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        zatvoriModalTretman();
    }
});
<?php else: ?>
// Ako nema dozvolu, prikaži poruku
function otvoriModalTretman() {
    alert('Nemate dozvolu za dodavanje tretmana. Kontaktirajte administratora.');
}
<?php endif; ?>





function promeniStatus(terminId, noviStatus) {
    let poruka = '';
    switch(noviStatus) {
        case 'otkazan': poruka = 'otkazati'; break;
        case 'obavljen': poruka = 'označiti kao obavljen'; break;
        case 'zakazan': poruka = 'vratiti u zakazane'; break;
        default: poruka = 'promeniti status';
    }
    
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

// Zatvaranje modala na click outside
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modal-overlay').addEventListener('click', function() {
        zatvoriModalTretman();
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        zatvoriModalTretman();
    }
});
</script>