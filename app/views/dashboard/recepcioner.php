<div class="recepcioner-dashboard">
    <!-- Uvodni naslov -->
    <div class="recepcioner-header">
        <h2>Recepcioner Dashboard</h2>
        <p>Kompletni pregled klinike i upravljanje operativnim aktivnostima</p>
    </div>

    <!-- Statisticki pregled -->
    <div class="recepcioner-stats-grid">
        <div class="recepcioner-stat-card recepcioner-stat-blue">
            <div class="recepcioner-stat-content">
                <div class="recepcioner-stat-number"><?= $dashboard_data['ukupno_pacijenata'] ?? 0 ?></div>
                <div class="recepcioner-stat-label">Ukupno pacijenata</div>
                <div class="recepcioner-stat-icon"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
        
        <div class="recepcioner-stat-card recepcioner-stat-green">
            <div class="recepcioner-stat-content">
                <div class="recepcioner-stat-number"><?= $dashboard_data['broj_termina_danas'] ?? 0 ?></div>
                <div class="recepcioner-stat-label">Termini danas</div>
                <div class="recepcioner-stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            </div>
        </div>
        
        <div class="recepcioner-stat-card recepcioner-stat-orange">
            <div class="recepcioner-stat-content">
                <div class="recepcioner-stat-number"><?= count($dashboard_data['termini_danas'] ?? []) ?></div>
                <div class="recepcioner-stat-label">Aktivni termini</div>
                <div class="recepcioner-stat-icon"><i class="fa-solid fa-clock"></i></div>
            </div>
        </div>
        
        <div class="recepcioner-stat-card recepcioner-stat-purple">
            <div class="recepcioner-stat-content">
                <div class="recepcioner-stat-number"><?= count($dashboard_data['nedavni_kartoni'] ?? []) ?></div>
                <div class="recepcioner-stat-label">Novi kartoni</div>
                <div class="recepcioner-stat-icon"><i class="fa-solid fa-folder-open"></i></div>
            </div>
        </div>
    </div>

    <!-- Glavne akcije -->
    <div class="recepcioner-action-cards">
        <div class="recepcioner-action-card">
            <h3>Termini i zakazivanje</h3>
            <p>Upravljanje terminima, zakazivanje i otkazivanje</p>
            <div class="recepcioner-action-buttons">
                <a href="/termini/kreiraj" class="recepcioner-btn recepcioner-btn-primary recepcioner-btn-sm">
                    <i class="fa-solid fa-plus"></i> Novi termin
                </a>
                <a href="/termini" class="recepcioner-btn recepcioner-btn-outline recepcioner-btn-sm">
                    <i class="fa-solid fa-calendar"></i> Svi termini
                </a>
            </div>
        </div>
        
        <div class="recepcioner-action-card">
            <h3>Pacijenti i kartoni</h3>
            <p>Upravljanje kartonima pacijenata i registracija</p>
            <div class="recepcioner-action-buttons">
                <a href="/profil/kreiraj" class="recepcioner-btn recepcioner-btn-success recepcioner-btn-sm">
                    <i class="fa-solid fa-user-plus"></i> Novi pacijent
                </a>
                <a href="/kartoni/lista" class="recepcioner-btn recepcioner-btn-outline recepcioner-btn-sm">
                    <i class="fa-solid fa-folder-open"></i> Svi kartoni
                </a>
            </div>
        </div>
        
        <div class="recepcioner-action-card">
            <h3>Izvještaji i pregled</h3>
            <p>Finansijski i operativni izvještaji klinike</p>
            <div class="recepcioner-action-buttons">
                <a href="/izvjestaji" class="recepcioner-btn recepcioner-btn-info recepcioner-btn-sm">
                    <i class="fa-solid fa-chart-line"></i> Izvještaji
                </a>
                <a href="/izvjestaji/medicinski" class="recepcioner-btn recepcioner-btn-outline recepcioner-btn-sm">
                    <i class="fa-solid fa-file-medical"></i> Medicinski
                </a>
            </div>
        </div>
        
        <div class="recepcioner-action-card">
            <h3>Upravljanje korisnicima</h3>
            <p>Pregled korisnika i raspored terapeuta</p>
            <div class="recepcioner-action-buttons">
                <a href="/profil/admin" class="recepcioner-btn recepcioner-btn-warning recepcioner-btn-sm">
                    <i class="fa-solid fa-users"></i> Korisnici
                </a>
                <a href="/raspored" class="recepcioner-btn recepcioner-btn-outline recepcioner-btn-sm">
                    <i class="fa-solid fa-clock"></i> Raspored
                </a>
            </div>
        </div>
    </div>

    <!-- Glavni sadržaj -->
    <div class="recepcioner-main-content">
        <!-- Termini danas -->
        <div class="recepcioner-content-section recepcioner-termini-section">
            <div class="recepcioner-section-header">
                <h3>Termini danas</h3>
                <div class="recepcioner-header-actions">
                    <a href="/termini/novi" class="recepcioner-btn recepcioner-btn-primary recepcioner-btn-xs">
                        <i class="fa-solid fa-plus"></i> Novi
                    </a>
                    <a href="/termini" class="recepcioner-btn recepcioner-btn-outline recepcioner-btn-xs">Svi termini</a>
                </div>
            </div>
            
            <div class="recepcioner-section-content">
                <?php if (empty($dashboard_data['termini_danas'])): ?>
                    <div class="recepcioner-empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        <p>Nema termina za danas</p>
                        <a href="/termini/novi" class="recepcioner-btn recepcioner-btn-primary recepcioner-btn-sm">Zakaži prvi termin</a>
                    </div>
                <?php else: ?>
                    <div class="recepcioner-timeline">
                        <?php foreach ($dashboard_data['termini_danas'] as $termin): ?>
                        <div class="recepcioner-timeline-item recepcioner-status-<?= $termin['status'] ?>">
                            <div class="recepcioner-timeline-time">
                                <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                            </div>
                            <div class="recepcioner-timeline-content">
                                <div class="recepcioner-timeline-title">
                                    <?= htmlspecialchars($termin['pacijent_ime']) ?>
                                </div>
                                <div class="recepcioner-timeline-details">
                                    <?= htmlspecialchars($termin['usluga']) ?> - <?= htmlspecialchars($termin['terapeut_ime']) ?>
                                </div>
                                <div class="recepcioner-timeline-status">
                                    <span class="recepcioner-status-badge recepcioner-status-<?= $termin['status'] ?>">
                                        <?= ucfirst($termin['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="recepcioner-timeline-actions">
                                <a href="/termini/uredi/id=<?= $termin['id'] ?>" class="recepcioner-btn recepcioner-btn-outline recepcioner-btn-xs">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <?php if ($termin['status'] === 'obavljen' && $termin['karton_id']): ?>
                                <button type="button" 
                                        class="recepcioner-btn recepcioner-btn-success recepcioner-btn-xs" 
                                        onclick="otvoriModalTretman(<?= $termin['id'] ?>, '<?= htmlspecialchars($termin['pacijent_ime']) ?>', <?= $termin['karton_id'] ?>)">
                                    <i class="fa-solid fa-notes-medical"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nedavni kartoni -->
        <div class="recepcioner-content-section">
            <div class="recepcioner-section-header">
                <h3>Nedavno kreirani kartoni</h3>
                <div class="recepcioner-header-actions">
                    <a href="/kartoni/lista" class="recepcioner-btn recepcioner-btn-outline recepcioner-btn-xs">Svi kartoni</a>
                </div>
            </div>
            
            <div class="recepcioner-section-content">
                <?php if (empty($dashboard_data['nedavni_kartoni'])): ?>
                    <div class="recepcioner-empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>Nema novih kartona</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($dashboard_data['nedavni_kartoni'] as $karton): ?>
                    <div class="recepcioner-karton-item">
                        <div class="recepcioner-karton-content">
                            <div class="recepcioner-karton-name">
                                <?= htmlspecialchars($karton['pacijent_ime']) ?>
                            </div>
                            <div class="recepcioner-karton-details">
                                <?= htmlspecialchars($karton['dijagnoza']) ?>
                            </div>
                            <div class="recepcioner-karton-extra">
                                Otvorio: <?= htmlspecialchars($karton['otvorio_ime']) ?>
                            </div>
                        </div>
                        <div class="recepcioner-karton-date">
                            <?= date('d.m.Y', strtotime($karton['datum_otvaranja'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Brza pretraga i navigacija -->
    <div class="recepcioner-search-section">
        <div class="recepcioner-search-card">
            <h3>Brza pretraga</h3>
            <form action="/kartoni/pretraga" method="GET" class="recepcioner-search">
                <div class="recepcioner-search-input-group">
                    <input type="text" name="q" placeholder="Pretraži po imenu, prezimenu, JMBG..." class="recepcioner-search-input">
                    <button type="submit" class="recepcioner-search-btn">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="recepcioner-nav-card">
            <h3>Brza navigacija</h3>
            <div class="recepcioner-nav-grid">
                <a href="/cjenovnik" class="recepcioner-quick-link">
                    <i class="fa-solid fa-money-bill"></i>
                    <span>Cjenovnik</span>
                </a>
                <a href="/kategorije" class="recepcioner-quick-link">
                    <i class="fa-solid fa-tags"></i>
                    <span>Kategorije</span>
                </a>
                <a href="/raspored" class="recepcioner-quick-link">
                    <i class="fa-solid fa-clock"></i>
                    <span>Raspored</span>
                </a>
                <a href="/backup" class="recepcioner-quick-link">
                    <i class="fa-solid fa-database"></i>
                    <span>Backup</span>
                </a>
            </div>
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