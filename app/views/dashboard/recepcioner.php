<div class="admin-dashboard">
    <!-- Uvodni naslov -->
    <div class="admin-header">
        <h2>Recepcioner Dashboard</h2>
        <p>Kompletni pregled i upravljanje klinikom</p>
    </div>

    <!-- Statisticki kartoni (BEZ FINANCIJSKIH PODATAKA) -->
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
                <div class="admin-stat-number"><?= $dashboard_data['broj_termina_danas'] ?? 0 ?></div>
                <div class="admin-stat-label">Termini danas</div>
                <div class="admin-stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            </div>
        </div>
        
        <div class="admin-stat-card admin-stat-red">
            <div class="admin-stat-content">
                <div class="admin-stat-number"><?= count($dashboard_data['termini_danas'] ?? []) ?></div>
                <div class="admin-stat-label">Aktivni termini</div>
                <div class="admin-stat-icon"><i class="fa-solid fa-clock"></i></div>
            </div>
        </div>
        
        <div class="admin-stat-card admin-stat-orange">
            <div class="admin-stat-content">
                <div class="admin-stat-number"><?= $dashboard_data['novi_kartoni_danas'] ?? 0 ?></div>
                <div class="admin-stat-label">Novi kartoni danas</div>
                <div class="admin-stat-icon"><i class="fa-solid fa-folder-plus"></i></div>
            </div>
        </div>
    </div>

    <!-- Mesečni pregled (BEZ FINANSIJSKIH PODATAKA) -->
    <div class="admin-monthly-overview">
        <h3>Pregled ovog mjeseca</h3>
        <div class="admin-monthly-stats">
            <div class="admin-monthly-stat">
                <div class="admin-monthly-number"><?= count($dashboard_data['nedavni_kartoni'] ?? []) ?></div>
                <div class="admin-monthly-label">Novi kartoni</div>
            </div>
            
            <?php if (!empty($dashboard_data['top_terapeut'])): ?>
            <div class="admin-monthly-stat">
                <div class="admin-monthly-number"><?= $dashboard_data['top_terapeut']['broj_termina'] ?></div>
                <div class="admin-monthly-label">Termina - <?= htmlspecialchars($dashboard_data['top_terapeut']['ime']) ?></div>
            </div>
            <?php endif; ?>
            
            <div class="admin-monthly-stat">
                <div class="admin-monthly-number"><?= count($dashboard_data['poslednji_nalazi'] ?? []) ?></div>
                <div class="admin-monthly-label">Nedavni nalazi</div>
            </div>
        </div>
    </div>

    <!-- Glavne akcije (ISTA LOGIKA KAO ADMIN ALI BEZ ADMIN KREIRANJA) -->
    <div class="admin-action-cards">
        <div class="admin-action-card">
            <h3>Upravljanje korisnicima</h3>
            <p>Dodaj novi profil, pregled pacijenata i terapeuta</p>
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
            <h3>Kartoni i tretmani</h3>
            <p>Upravljanje kartonima pacijenata i medicinskim tretmanima</p>
            <div class="admin-action-buttons">
                <a href="/kartoni/kreiraj" class="admin-btn admin-btn-primary admin-btn-sm">
                    <i class="fa-solid fa-folder-plus"></i> Novi karton
                </a>
                <a href="/kartoni/lista" class="admin-btn admin-btn-outline admin-btn-sm">
                    <i class="fa-solid fa-folder-open"></i> Svi kartoni
                </a>
            </div>
        </div>
        
        <div class="admin-action-card">
            <h3>Konfiguracija sistema</h3>
            <p>Cjenovnik, kategorije usluga i osnovne sistemske postavke</p>
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

    <!-- Pregled aktivnosti (ISTA LOGIKA KAO ADMIN) -->
    <div class="admin-activity-grid">
        <!-- Termini danas -->
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
                <?php if (empty($dashboard_data['termini_danas'])): ?>
                    <div class="admin-empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        <p>Nema termina za danas</p>
                        <a href="/termini/kreiraj" style="margin-top:10px;" class="admin-btn admin-btn-primary admin-btn-sm">Zakaži prvi termin</a>
                    </div>
                <?php else: ?>
                    <div class="admin-timeline">
                        <?php foreach ($dashboard_data['termini_danas'] as $termin): ?>
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
                                        data-terapeut-id="<?= $termin['terapeut_id'] ?? '' ?>"
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

    

    <!-- Brza navigacija (BEZ IZVJEŠTAJA) -->
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
            <a href="/paketi" class="admin-quick-link">
                <i class="fa-solid fa-box"></i>
                <span>Paketi</span>
            </a>
            <a href="/dijagnoze" class="admin-quick-link">
                <i class="fa-solid fa-notes-medical"></i>
                <span>Dijagnoze</span>
            </a>
            <a href="/timetable" class="admin-quick-link">
                <i class="fa-solid fa-business-time"></i>
                <span>Radna vremena</span>
            </a>
        </div>
    </div>
</div>

<!-- Modal overlay (ISTI KAO ADMIN) -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<!-- Modal za tretman (ISTI KAO ADMIN) -->
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
                    <?php if (isset($svi_terapeuti) && !empty($svi_terapeuti)): ?>
                        <?php foreach ($svi_terapeuti as $terapeut): ?>
                            <option value="<?= $terapeut['id'] ?>"><?= htmlspecialchars($terapeut['ime'] . ' ' . $terapeut['prezime']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

<!-- ISTI JAVASCRIPT KAO ADMIN -->
<script>
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
    
    // Focus na prvo textarea polje
    setTimeout(() => {
        document.querySelector('#tretman-modal textarea[name="stanje_prije"]').focus();
    }, 100);
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

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        zatvoriModalTretman();
    }
});
</script>