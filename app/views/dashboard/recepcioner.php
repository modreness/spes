<div class="recepcioner-dashboard">
    <!-- Uvodni naslov -->
    <div class="recepcioner-header">
        <h2>Recepcioner Dashboard</h2>
        <p>Upravljanje terminima, zakazivanje i rad sa pacijentima</p>
    </div>

    <!-- Statisticki pregled -->
    <div class="recepcioner-stats-grid">
        <div class="recepcioner-stat-card recepcioner-stat-blue">
            <div class="recepcioner-stat-content">
                <div class="recepcioner-stat-number"><?= $dashboard_data['broj_termina_danas'] ?? 0 ?></div>
                <div class="recepcioner-stat-label">Termini danas</div>
                <div class="recepcioner-stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            </div>
        </div>
        
        <div class="recepcioner-stat-card recepcioner-stat-green">
            <div class="recepcioner-stat-content">
                <div class="recepcioner-stat-number"><?= $dashboard_data['ukupno_pacijenata'] ?? 0 ?></div>
                <div class="recepcioner-stat-label">Ukupno pacijenata</div>
                <div class="recepcioner-stat-icon"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
        
        <div class="recepcioner-stat-card recepcioner-stat-orange">
            <div class="recepcioner-stat-content">
                <div class="recepcioner-stat-number"><?= count($dashboard_data['termini_danas'] ?? []) ?></div>
                <div class="recepcioner-stat-label">Aktivni termini</div>
                <div class="recepcioner-stat-icon"><i class="fa-solid fa-clock"></i></div>
            </div>
        </div>
    </div>

    <!-- Brze akcije -->
    <div class="recepcioner-quick-actions">
        <div class="recepcioner-action-card">
            <h3>Novi termin</h3>
            <p>Zakažite novi termin za pacijenta</p>
            <a href="/termini/novi" class="recepcioner-btn recepcioner-btn-primary">
                <i class="fa-solid fa-plus"></i> Zakaži termin
            </a>
        </div>
        
        <div class="recepcioner-action-card">
            <h3>Pretraga pacijenata</h3>
            <p>Pronađite karton pacijenta</p>
            <a href="/kartoni/lista" class="recepcioner-btn recepcioner-btn-success">
                <i class="fa-solid fa-search"></i> Pretraži kartone
            </a>
        </div>
        
        <div class="recepcioner-action-card">
            <h3>Novi pacijent</h3>
            <p>Registruj novog pacijenta</p>
            <a href="/profil/kreiraj" class="recepcioner-btn recepcioner-btn-info">
                <i class="fa-solid fa-user-plus"></i> Dodaj pacijenta
            </a>
        </div>
    </div>

    <!-- Glavni sadržaj -->
    <div class="recepcioner-main-content">
        <!-- Termini danas -->
        <div class="recepcioner-content-section recepcioner-termini-section">
            <div class="recepcioner-section-header">
                <h3>Termini danas</h3>
                <div class="recepcioner-header-actions">
                    <a href="/termini" class="recepcioner-btn recepcioner-btn-outline recepcioner-btn-sm">Svi termini</a>
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
                                <a href="/termini/uredi/<?= $termin['id'] ?>" class="recepcioner-btn recepcioner-btn-outline recepcioner-btn-xs">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Brza pretraga -->
        <div class="recepcioner-content-section">
            <div class="recepcioner-section-header">
                <h3>Brza pretraga</h3>
            </div>
            
            <div class="recepcioner-section-content">
                <div class="recepcioner-search-form">
                    <form action="/kartoni/pretraga" method="GET" class="recepcioner-search">
                        <div class="recepcioner-search-input-group">
                            <input type="text" name="q" placeholder="Pretraži po imenu, prezimenu, JMBG..." class="recepcioner-search-input">
                            <button type="submit" class="recepcioner-search-btn">
                                <i class="fa-solid fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="recepcioner-quick-links">
                    <h4>Brze akcije</h4>
                    <div class="recepcioner-links-grid">
                        <a href="/kartoni/lista" class="recepcioner-quick-link">
                            <i class="fa-solid fa-folder-open"></i>
                            <span>Svi kartoni</span>
                        </a>
                        <a href="/termini/kalendar" class="recepcioner-quick-link">
                            <i class="fa-solid fa-calendar"></i>
                            <span>Kalendar termina</span>
                        </a>
                        <a href="/cjenovnik" class="recepcioner-quick-link">
                            <i class="fa-solid fa-money-bill"></i>
                            <span>Cjenovnik</span>
                        </a>
                        <a href="/raspored" class="recepcioner-quick-link">
                            <i class="fa-solid fa-clock"></i>
                            <span>Raspored</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Korisne informacije -->
    <div class="recepcioner-info-section">
        <div class="recepcioner-info-card">
            <h4>Korisni linkovi</h4>
            <ul class="recepcioner-info-list">
                <li><a href="/profil/admin">Upravljanje korisnicima</a></li>
                <li><a href="/izvjestaji">Izvještaji</a></li>
                <li><a href="/backup">Backup sistema</a></li>
            </ul>
        </div>
        
        <div class="recepcioner-info-card">
            <h4>Brze tipke</h4>
            <ul class="recepcioner-info-list">
                <li><kbd>Ctrl</kbd> + <kbd>N</kbd> - Novi termin</li>
                <li><kbd>Ctrl</kbd> + <kbd>F</kbd> - Pretraga</li>
                <li><kbd>Ctrl</kbd> + <kbd>K</kbd> - Kartoni</li>
            </ul>
        </div>
    </div>
</div>