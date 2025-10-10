<div class="admin-dashboard">
    <!-- Uvodni naslov -->
    <div class="admin-header">
        <h2>Administracija/h2>
        <p>Kompletni pregled i upravljanje sistemom</p>
    </div>

    <!-- Statisticki kartoni -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card admin-stat-blue">
            <div class="admin-stat-content">
                <div class="admin-stat-number"><?= $dashboard_data['ukupno_pacijenata'] ?? 0 ?></div>
                <div class="admin-stat-label">Ukupno pacijenata</div>
                <div class="admin-stat-icon"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
        
        <div class="admin-stat-card admin-stat-green">
            <div class="admin-stat-content">
                <div class="admin-stat-number"><?= $dashboard_data['ukupno_terapeuta'] ?? 0 ?></div>
                <div class="admin-stat-label">Aktivnih terapeuta</div>
                <div class="admin-stat-icon"><i class="fa-solid fa-user-doctor"></i></div>
            </div>
        </div>
        
        <div class="admin-stat-card admin-stat-red">
            <div class="admin-stat-content">
                <div class="admin-stat-number"><?= $dashboard_data['termini_danas'] ?? 0 ?></div>
                <div class="admin-stat-label">Termini danas</div>
                <div class="admin-stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            </div>
        </div>
        
        <div class="admin-stat-card admin-stat-orange">
            <div class="admin-stat-content">
                <div class="admin-stat-number"><?= number_format($dashboard_data['prihod_danas'] ?? 0, 2) ?> KM</div>
                <div class="admin-stat-label">Prihod danas</div>
                <div class="admin-stat-icon"><i class="fa-solid fa-coins"></i></div>
            </div>
        </div>
    </div>


    <!-- Mesečni pregled -->
    <div class="admin-monthly-overview">
        <h3>Pregled ovog mjeseca</h3>
        <div class="admin-monthly-stats">
            <div class="admin-monthly-stat">
                <div class="admin-monthly-number"><?= number_format($dashboard_data['prihod_mesec'] ?? 0, 2) ?> KM</div>
                <div class="admin-monthly-label">Ukupan prihod</div>
            </div>
            <div class="admin-monthly-stat">
                <div class="admin-monthly-number"><?= $dashboard_data['novi_kartoni_danas'] ?? 0 ?></div>
                <div class="admin-monthly-label">Novi kartoni danas</div>
            </div>
            <?php if (!empty($dashboard_data['top_terapeut'])): ?>
            <div class="admin-monthly-stat">
                <div class="admin-monthly-number"><?= $dashboard_data['top_terapeut']['broj_termina'] ?></div>
                <div class="admin-monthly-label">Termina - <?= htmlspecialchars($dashboard_data['top_terapeut']['ime']) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Glavne akcije -->
    <div class="admin-action-cards">
        <div class="admin-action-card">
            <h3>Upravljanje korisnicima</h3>
            <p>Dodaj novi profil, pregled svih pacijenata</p>
            <div class="admin-action-buttons">
                <a href="/profil/kreiraj" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-user-plus"></i> Novi korisnik
                </a>
                <a href="/profil/pacijent" class="admin-btn admin-btn-outline admin-btn-sm">
                    <i class="fa-solid fa-users"></i> Svi pacijenti
                </a>
            </div>
        </div>
        
        <div class="admin-action-card">
            <h3>Termini i raspored</h3>
            <p>Pregled svih termina i upravljanje rasporedom terapeuta</p>
            <div class="admin-action-buttons">
                <a href="/termini" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-calendar"></i> Termini
                </a>
                <a href="/raspored" class="admin-btn admin-btn-outline admin-btn-sm">
                    <i class="fa-solid fa-clock"></i> Raspored
                </a>
            </div>
        </div>
        
        <div class="admin-action-card">
            <h3>Izvještaji i analiza</h3>
            <p>Detaljni finansijski i medicinski izvještaji klinike</p>
            <div class="admin-action-buttons">
                <a href="/izvjestaji" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-chart-line"></i> Izvještaji
                </a>
                <a href="/izvjestaji/medicinski" class="admin-btn admin-btn-outline admin-btn-sm">
                    <i class="fa-solid fa-file-medical"></i> Medicinski
                </a>
            </div>
        </div>
        
        <div class="admin-action-card">
            <h3>Konfiguracija sistema</h3>
            <p>Cjenovnik, kategorije usluga i sistemske postavke</p>
            <div class="admin-action-buttons">
                <a href="/cjenovnik" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-money-bill"></i> Cjenovnik
                </a>
                <a href="/kategorije" class="admin-btn admin-btn-outline admin-btn-sm">
                    <i class="fa-solid fa-tags"></i> Kategorije
                </a>
            </div>
        </div>
    </div>


    <!-- Pregled aktivnosti -->
    <div class="admin-activity-grid">
        <!-- Predstojeci termini -->
        <div class="admin-activity-section">
            <div class="admin-activity-header">
                <h3>Termini danas</h3>
                <div class="admin-header-actions">
                    <a href="/termini/kreiraj" class="admin-btn admin-btn-primary admin-btn-xs">
                        <i class="fa-solid fa-plus"></i> Novi
                    </a>
                    <a href="/termini" class="admin-btn admin-btn-outline admin-btn-xs">Svi termini</a>
                </div>
            </div>
            <div class="admin-activity-content">
                <?php if (empty($dashboard_data['predstojeci_termini'])): ?>
                    <div class="admin-empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        <p>Nema termina za danas</p>
                        <a href="/termini/novi" class="admin-btn admin-btn-primary admin-btn-sm">Zakaži prvi termin</a>
                    </div>
                <?php else: ?>
                    <div class="admin-timeline">
                        <?php foreach ($dashboard_data['predstojeci_termini'] as $termin): ?>
                        <div class="admin-timeline-item admin-status-<?= $termin['status'] ?>">
                            <div class="admin-timeline-time">
                                <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                            </div>
                            <div class="admin-timeline-content">
                                <div class="admin-timeline-title">
                                    <?= htmlspecialchars($termin['pacijent_ime']) ?>
                                </div>
                                <div class="admin-timeline-details">
                                    <?= htmlspecialchars($termin['usluga']) ?> - <?= htmlspecialchars($termin['terapeut_ime']) ?>
                                </div>
                                <div class="admin-timeline-status">
                                    <span class="admin-status-badge admin-status-<?= $termin['status'] ?>">
                                        <?= ucfirst($termin['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="admin-timeline-actions">
                                <a href="/termini/uredi?id=<?= $termin['id'] ?>" class="admin-btn admin-btn-outline admin-btn-xs">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
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
                        <a href="/termini" class="admin-btn admin-btn-outline admin-btn-sm">Svi termini</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nedavni kartoni -->
        <div class="admin-activity-section">
            <div class="admin-activity-header">
                <h3>Nedavno kreirani kartoni</h3>
            </div>
            <div class="admin-activity-content">
                <?php if (empty($dashboard_data['nedavni_kartoni'])): ?>
                    <div class="admin-empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>Nema novih kartona</p>
                    </div>
                <?php else: ?>
                <?php foreach ($dashboard_data['nedavni_kartoni'] as $karton): ?>
                    <a href="/kartoni/pregled?id=<?= $karton['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div class="admin-list-item admin-list-vertical" style="cursor: pointer; transition: all 0.2s;" 
                            onmouseover="this.style.background='#f8f9fa'; this.style.transform='translateX(5px)';" 
                            onmouseout="this.style.background=''; this.style.transform='translateX(0)';">
                            <div>
                                <div class="admin-list-name">
                                    <?= htmlspecialchars($karton['pacijent_ime']) ?>
                                </div>
                                <div class="admin-list-details">
                                    Broj kartona: <?= htmlspecialchars($karton['broj_upisa']) ?>
                                </div>
                                <div class="admin-list-extra">
                                    Otvorio: <?= htmlspecialchars($karton['otvorio_ime']) ?>
                                </div>
                            </div>
                            <div class="admin-list-date">
                                <?= date('d.m.Y', strtotime($karton['datum_otvaranja'])) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                    <div class="admin-activity-footer">
                        <a href="/kartoni/lista" class="admin-btn admin-btn-outline admin-btn-sm">Svi kartoni</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Brza navigacija -->
    <div class="admin-quick-nav">
        <div class="admin-quick-nav-header">
            <h3>Brza navigacija</h3>
        </div>
        
        <div class="admin-quick-nav-grid">
            <a href="/kartoni/lista" class="admin-quick-link">
                <i class="fa-solid fa-folder-open"></i>
                <span>Kartoni pacijenata</span>
            </a>
            <a href="/kategorije" class="admin-quick-link">
                <i class="fa-solid fa-tags"></i>
                <span>Kategorije usluga</span>
            </a>
            <a href="/cjenovnik" class="admin-quick-link">
                <i class="fa-solid fa-money-bill"></i>
                <span>Cjenovnik</span>
            </a>
            <a href="/timetable" class="admin-quick-link">
                <i class="fa-solid fa-business-time"></i>
                <span>Radna vremena</span>
            </a>
            <a href="/backup" class="admin-quick-link">
                <i class="fa-solid fa-database"></i>
                <span>Backup sistema</span>
            </a>
            <a href="/logs" class="admin-quick-link">
                <i class="fa-solid fa-file-lines"></i>
                <span>System logs</span>
            </a>
        </div>
    </div>
</div>

<!-- Modal overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<!-- Modal za tretman -->
<div id="tretman-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Dodaj tretman</h3>
        <p><strong>Karton za:</strong> <span id="modal-ime"></span></p>
        <p><strong>Karton ID:</strong> <span id="modal-karton-id-display"></span></p>

        <form method="post" action="/kartoni/dodaj-tretman">
            <input type="hidden" name="karton_id" id="modal-karton-id">
            <input type="hidden" name="termin_id" id="modal-termin-id">

            <div class="form-group">
                <label for="stanje_prije">Stanje prije</label>
                <textarea name="stanje_prije" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="terapija">Terapija</label>
                <textarea name="terapija" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="stanje_poslije">Stanje poslije</label>
                <textarea name="stanje_poslije" rows="3" required></textarea>
            </div>

            <div style="text-align: center; margin-top: 15px;">
                <button type="button" class="btn btn-secondary" onclick="zatvoriModalTretman()">Otkaži</button>
                <button type="submit" class="btn btn-add">Snimi tretman</button>
            </div>
        </form>
    </div>
</div>

<script>
function otvoriModalTretman(terminId, imePrezime, kartonId) {
    // Potrebno je pronaći karton_id na osnovu pacijent_id
    // Za sada ću koristiti pacijent_id kao karton_id (možda treba AJAX poziv)
    document.getElementById('modal-karton-id').value = kartonId;
    document.getElementById('modal-termin-id').value = terminId;
    document.getElementById('modal-ime').textContent = imePrezime;
    document.getElementById('modal-karton-id-display').textContent = kartonId;

    document.getElementById('tretman-modal').style.display = 'block';
    document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriModalTretman() {
    document.getElementById('tretman-modal').style.display = 'none';
    document.getElementById('modal-overlay').style.display = 'none';
    
    // Resetuj formu
    document.querySelector('#tretman-modal form').reset();
}

// Zatvaranje na click outside
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modal-overlay').addEventListener('click', function() {
        zatvoriModalTretman();
    });
});
</script>