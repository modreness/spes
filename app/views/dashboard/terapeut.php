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
        
        <div class="terapeut-stat-card terapeut-stat-orange">
            <div class="terapeut-stat-content">
                <div class="terapeut-stat-number"><?= $dashboard_data['broj_termina_sutra'] ?? 0 ?></div>
                <div class="terapeut-stat-label">Termini sutra</div>
                <div class="terapeut-stat-icon"><i class="fa-solid fa-calendar-plus"></i></div>
            </div>
        </div>
        
        <div class="terapeut-stat-card terapeut-stat-green">
            <div class="terapeut-stat-content">
                <div class="terapeut-stat-number"><?= $dashboard_data['broj_mojih_pacijenata'] ?? 0 ?></div>
                <div class="terapeut-stat-label">Moji pacijenti</div>
                <div class="terapeut-stat-icon"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
        
        <div class="terapeut-stat-card terapeut-stat-purple">
            <div class="terapeut-stat-content">
                <div class="terapeut-stat-number"><?= $dashboard_data['tretmani_ovaj_mesec'] ?? 0 ?></div>
                <div class="terapeut-stat-label">Tretmani ovaj mesec</div>
                <div class="terapeut-stat-icon"><i class="fa-solid fa-notes-medical"></i></div>
            </div>
        </div>
    </div>

    <!-- Mesečni pregled (kao admin) -->
    <?php if (!empty($dashboard_data['radno_vreme_danas']) || !empty($dashboard_data['top_usluge'])): ?>
    <div class="admin-monthly-overview">
        <h3>Pregled danas</h3>
        <div class="admin-monthly-stats">
            <?php if (!empty($dashboard_data['radno_vreme_danas'])): ?>
            <div class="admin-monthly-stat">
                <div class="admin-monthly-number">
                    <?= date('H:i', strtotime($dashboard_data['radno_vreme_danas'][0]['pocetak'])) ?> - 
                    <?= date('H:i', strtotime($dashboard_data['radno_vreme_danas'][0]['kraj'])) ?>
                </div>
                <div class="admin-monthly-label">Radno vreme</div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($dashboard_data['top_usluge'])): ?>
            <div class="admin-monthly-stat">
                <div class="admin-monthly-number"><?= $dashboard_data['top_usluge'][0]['broj_termina'] ?></div>
                <div class="admin-monthly-label">Top usluga - <?= htmlspecialchars($dashboard_data['top_usluge'][0]['naziv']) ?></div>
            </div>
            <?php endif; ?>
            
            <div class="admin-monthly-stat">
                <div class="admin-monthly-number"><?= $dashboard_data['termini_ova_sedmica'] ?? 0 ?></div>
                <div class="admin-monthly-label">Termini ove sedmice</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Glavne akcije (kao admin) -->
    <div class="admin-action-cards">
        <div class="admin-action-card">
            <h3>Moj raspored</h3>
            <p>Pregled rasporeda rada i dostupnih termina</p>
            <div class="admin-action-buttons">
                <a href="/raspored/moj" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-calendar-days"></i> Moj raspored
                </a>
                <a href="/termini/kalendar" class="admin-btn admin-btn-outline admin-btn-sm">
                    <i class="fa-solid fa-calendar"></i> Kalendar
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
                <a href="/kartoni/pretraga" class="admin-btn admin-btn-outline admin-btn-sm">
                    <i class="fa-solid fa-search"></i> Pretraži
                </a>
            </div>
        </div>
        
        <div class="admin-action-card">
            <h3>Tretmani i izvještaji</h3>
            <p>História tretmana i statistike</p>
            <div class="admin-action-buttons">
                <a href="/tretmani/moji" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-notes-medical"></i> Moji tretmani
                </a>
                <a href="/izvjestaji/terapeut" class="admin-btn admin-btn-outline admin-btn-sm">
                    <i class="fa-solid fa-chart-line"></i> Statistike
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
                <a href="/nalazi/upload" class="admin-btn admin-btn-outline admin-btn-sm">
                    <i class="fa-solid fa-upload"></i> Upload
                </a>
            </div>
        </div>
    </div>

    <!-- Pregled aktivnosti (kao admin) -->
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
                                <button onclick="promeniStatus(<?= $termin['id'] ?>, 'u_toku')" class="admin-btn admin-btn-primary admin-btn-xs">
                                    <i class="fa-solid fa-play"></i>
                                </button>
                                <?php elseif ($termin['status'] === 'u_toku'): ?>
                                <button onclick="promeniStatus(<?= $termin['id'] ?>, 'obavljen')" class="admin-btn admin-btn-success admin-btn-xs">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($termin['status'] === 'obavljen' && $termin['karton_id']): ?>
                                <button type="button" 
                                        class="admin-btn admin-btn-success admin-btn-xs" 
                                        onclick="otvoriModalTretman(<?= $termin['id'] ?>, '<?= htmlspecialchars($termin['pacijent_ime']) ?>', <?= $termin['karton_id'] ?>)">
                                    <i class="fa-solid fa-notes-medical"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="admin-activity-footer">
                        <a href="/termini/moji" class="admin-btn admin-btn-outline admin-btn-sm">Svi moji termini</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar sa informacijama -->
        <div class="admin-activity-section">
            <div class="admin-activity-header">
                <h3>Brzi pregled</h3>
            </div>
            <div class="admin-activity-content">
                
                <!-- Termini sutra -->
                <?php if (!empty($dashboard_data['termini_sutra'])): ?>
                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e3e6f0;">
                    <h4 style="color: #5a5c69; font-size: 0.9em; margin-bottom: 10px;">
                        <i class="fa-solid fa-calendar-plus"></i> Sutra (<?= count($dashboard_data['termini_sutra']) ?>)
                    </h4>
                    <?php foreach (array_slice($dashboard_data['termini_sutra'], 0, 3) as $termin): ?>
                    <div class="admin-list-item admin-list-vertical">
                        <div>
                            <div class="admin-list-name">
                                <?= date('H:i', strtotime($termin['vrijeme'])) ?> - <?= htmlspecialchars($termin['pacijent_ime']) ?>
                            </div>
                            <div class="admin-list-details">
                                <?= htmlspecialchars($termin['usluga']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($dashboard_data['termini_sutra']) > 3): ?>
                    <div style="text-align: center; margin-top: 10px;">
                        <small style="color: #858796;">+<?= count($dashboard_data['termini_sutra']) - 3 ?> više termina</small>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Follow-up potreban -->
                <?php if (!empty($dashboard_data['potreban_followup'])): ?>
                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e3e6f0;">
                    <h4 style="color: #e74a3b; font-size: 0.9em; margin-bottom: 10px;">
                        <i class="fa-solid fa-exclamation-triangle"></i> Potreban follow-up
                    </h4>
                    <?php foreach (array_slice($dashboard_data['potreban_followup'], 0, 3) as $pacijent): ?>
                    <div class="admin-list-item admin-list-vertical">
                        <div>
                            <div class="admin-list-name">
                                <a href="/kartoni/pregled?id=<?= $pacijent['id'] ?>" style="color: #e74a3b;">
                                    <?= htmlspecialchars($pacijent['pacijent_ime']) ?>
                                </a>
                            </div>
                            <div class="admin-list-details">
                                <?= $pacijent['dana_od_tretmana'] ?> dana od poslednjeg tretmana
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Poslednji tretmani -->
                <?php if (!empty($dashboard_data['poslednji_tretmani'])): ?>
                <div>
                    <h4 style="color: #5a5c69; font-size: 0.9em; margin-bottom: 10px;">
                        <i class="fa-solid fa-history"></i> Poslednji tretmani
                    </h4>
                    <?php foreach (array_slice($dashboard_data['poslednji_tretmani'], 0, 3) as $tretman): ?>
                    <div class="admin-list-item admin-list-vertical">
                        <div>
                            <div class="admin-list-name">
                                <?= htmlspecialchars($tretman['pacijent_ime']) ?>
                            </div>
                            <div class="admin-list-details">
                                <?= date('d.m.Y', strtotime($tretman['datum'])) ?> - Karton #<?= $tretman['broj_upisa'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="admin-activity-footer">
                        <a href="/tretmani/moji" class="admin-btn admin-btn-outline admin-btn-sm">Svi tretmani</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Moji pacijenti -->
    <div class="admin-activity-grid">
        <div class="admin-activity-section" style="grid-column: 1 / -1;">
            <div class="admin-activity-header">
                <h3>Moji pacijenti</h3>
                <div class="admin-header-actions">
                    <a href="/kartoni/moji" class="admin-btn admin-btn-outline admin-btn-xs">Svi pacijenti</a>
                </div>
            </div>
            <div class="admin-activity-content">
                <?php if (empty($dashboard_data['moji_pacijenti'])): ?>
                    <div class="admin-empty-state">
                        <i class="fa-solid fa-users"></i>
                        <p>Nema dodeljenih pacijenata</p>
                    </div>
                <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                    <?php foreach (array_slice($dashboard_data['moji_pacijenti'], 0, 6) as $pacijent): ?>
                    <div style="border: 1px solid #e3e6f0; border-radius: 8px; padding: 15px; background: white;">
                        <div class="admin-list-item admin-list-vertical">
                            <div>
                                <div class="admin-list-name">
                                    <a href="/kartoni/pregled?id=<?= $pacijent['id'] ?>">
                                        <?= htmlspecialchars($pacijent['pacijent_ime']) ?>
                                    </a>
                                </div>
                                <div class="admin-list-details">
                                    Karton: #<?= htmlspecialchars($pacijent['broj_upisa']) ?>
                                </div>
                                <div class="admin-list-extra">
                                    <?= $pacijent['broj_tretmana'] ?> tretmana
                                    <?php if ($pacijent['poslednji_tretman']): ?>
                                        | Poslednji: <?= date('d.m.Y', strtotime($pacijent['poslednji_tretman'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 10px; text-align: center;">
                            <a href="/kartoni/pregled?id=<?= $pacijent['id'] ?>" class="admin-btn admin-btn-outline admin-btn-xs">
                                <i class="fa-solid fa-eye"></i> Karton
                            </a>
                            <a href="/kartoni/tretmani?id=<?= $pacijent['id'] ?>" class="admin-btn admin-btn-success admin-btn-xs">
                                <i class="fa-solid fa-notes-medical"></i> Tretmani
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($dashboard_data['moji_pacijenti']) > 6): ?>
                    <div style="border: 2px dashed #e3e6f0; border-radius: 8px; padding: 30px; background: #f8f9fa; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                        <div style="font-size: 1.5em; font-weight: 600; color: #858796; margin-bottom: 5px;">
                            +<?= count($dashboard_data['moji_pacijenti']) - 6 ?>
                        </div>
                        <div style="color: #858796; margin-bottom: 15px;">više pacijenata</div>
                        <a href="/kartoni/moji" class="admin-btn admin-btn-primary admin-btn-sm">Vidi sve</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
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
            
            // Generiši celu sedmicu
            $week_days = [];
            for ($i = 1; $i <= 7; $i++) {
                $day_date = date('Y-m-d', strtotime('this week +' . ($i-1) . ' days'));
                $week_days[$day_date] = $grouped_by_date[$day_date] ?? [];
            }
            ?>
            <?php foreach ($week_days as $date => $termini): ?>
            <div class="admin-quick-link" style="<?= date('Y-m-d') === $date ? 'background: #4e73df; color: white;' : '' ?> <?= empty($termini) ? 'opacity: 0.6;' : '' ?>">
                <div style="text-align: center;">
                    <i class="fa-solid fa-calendar-day"></i>
                    <div style="font-weight: 600; margin: 5px 0;">
                        <?= date('d.m', strtotime($date)) ?>
                    </div>
                    <div style="font-size: 0.8em;">
                        <?= ['Ned', 'Pon', 'Uto', 'Sre', 'Čet', 'Pet', 'Sub'][date('w', strtotime($date))] ?>
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
                        <div style="font-size: 0.75em; margin-top: 3px; <?= date('Y-m-d') === $date ? 'color: #fff;' : 'color: #858796;' ?>">
                            <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<!-- Modal za tretman -->
<div id="tretman-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Dodaj tretman</h3>
        <p><strong>Pacijent:</strong> <span id="modal-ime"></span></p>
        <p><strong>Karton ID:</strong> <span id="modal-karton-id-display"></span></p>

        <form method="post" action="/kartoni/dodaj-tretman">
            <input type="hidden" name="karton_id" id="modal-karton-id">
            <input type="hidden" name="termin_id" id="modal-termin-id">

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

            <div class="form-group">
                <label for="napomene">Napomene i preporuke</label>
                <textarea name="napomene" rows="2" placeholder="Dodatne napomene, preporuke za sledeći tretman..."></textarea>
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
function otvoriModalTretman(terminId, imePrezime, kartonId) {
    document.getElementById('modal-karton-id').value = kartonId;
    document.getElementById('modal-termin-id').value = terminId;
    document.getElementById('modal-ime').textContent = imePrezime;
    document.getElementById('modal-karton-id-display').textContent = kartonId;

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

function promeniStatus(terminId, noviStatus) {
    if (confirm('Da li ste sigurni da želite da promenite status termina?')) {
        // Moguće dodati AJAX poziv za promenu statusa
        // Za sada samo refresh
        window.location.href = '/termini/promeni-status?id=' + terminId + '&status=' + noviStatus;
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