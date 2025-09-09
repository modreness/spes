<div class="main-content-fw">
    <!-- Uvodni naslov -->
    <div style="margin-bottom: 30px;">
        <h2 style="color: #2c3e50; margin: 0;">Admin Dashboard</h2>
        <p style="color: #7f8c8d; margin: 5px 0 0 0;">Kompletni pregled klinike i upravljanje sistemom</p>
    </div>

    <!-- Statisticki kartoni -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card" style="background: linear-gradient(135deg, #3498db, #2980b9);">
            <h3>Ukupno pacijenata</h3>
            <div class="stat-number"><?= $dashboard_data['ukupno_pacijenata'] ?? 0 ?></div>
                 <div class="admin-stat-icon"><i class="fa-solid fa-users"></i></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
            <h3>Aktivnih terapeuta</h3>
            <div class="stat-number"><?= $dashboard_data['ukupno_terapeuta'] ?? 0 ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
            <h3>Termini danas</h3>
            <div class="stat-number"><?= $dashboard_data['termini_danas'] ?? 0 ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
            <h3>Prihod danas</h3>
            <div class="stat-number"><?= number_format($dashboard_data['prihod_danas'] ?? 0, 2) ?> KM</div>
        </div>
    </div>

    <!-- Mesečni pregled -->
    <div class="main-content-stats" style="margin-bottom: 30px;">
        <h3 style="margin: 0 0 20px 0; color: #2c3e50;">Pregled ovog meseca</h3>
        <div class="stats-grid" style="margin-bottom: 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                <h3>Ukupan prihod</h3>
                <div class="stat-number"><?= number_format($dashboard_data['prihod_mesec'] ?? 0, 2) ?> KM</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #34495e, #2c3e50);">
                <h3>Novi kartoni danas</h3>
                <div class="stat-number"><?= $dashboard_data['novi_kartoni_danas'] ?? 0 ?></div>
            </div>
            <?php if (!empty($dashboard_data['top_terapeut'])): ?>
            <div class="stat-card" style="background: linear-gradient(135deg, #16a085, #1abc9c);">
                <h3>Top terapeut - <?= htmlspecialchars($dashboard_data['top_terapeut']['ime']) ?></h3>
                <div class="stat-number"><?= $dashboard_data['top_terapeut']['broj_termina'] ?> termina</div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Glavne akcije -->
    <div class="action-cards">
        <div class="action-card">
            <h3>Upravljanje korisnicima</h3>
            <p>Dodaj novi profil, uredi postojeće korisnike ili promeni uloge</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="/profil/kreiraj" class="btn btn-add">
                    <i class="fa-solid fa-user-plus"></i> Novi korisnik
                </a>
                <a href="/profil/admin" class="btn btn-secondary">
                    <i class="fa-solid fa-users"></i> Svi korisnici
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>Termini i raspored</h3>
            <p>Pregled svih termina i upravljanje rasporedom terapeuta</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="/termini" class="submit-button btn-no-margin">
                    <i class="fa-solid fa-calendar"></i> Termini
                </a>
                <a href="/raspored" class="btn btn-secondary">
                    <i class="fa-solid fa-clock"></i> Raspored
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>Izvještaji i analiza</h3>
            <p>Detaljni finansijski i operativni izvještaji klinike</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="/izvjestaji" class="submit-button btn-no-margin">
                    <i class="fa-solid fa-chart-line"></i> Izvještaji
                </a>
                <a href="/izvjestaji/medicinski" class="btn btn-secondary">
                    <i class="fa-solid fa-file-medical"></i> Medicinski
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>Konfiguracija sistema</h3>
            <p>Cjenovnik, kategorije usluga i sistemske postavke</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="/cjenovnik" class="btn btn-edit">
                    <i class="fa-solid fa-money-bill"></i> Cjenovnik
                </a>
                <a href="/kategorije" class="btn btn-secondary">
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
                <h3>Predstojeci termini danas</h3>
            </div>
            <div class="admin-activity-content">
                <?php if (empty($dashboard_data['predstojeci_termini'])): ?>
                    <div class="admin-empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        <p>Nema zakazanih termina danas</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($dashboard_data['predstojeci_termini'] as $termin): ?>
                    <div class="admin-list-item">
                        <div>
                            <div class="admin-list-name">
                                <?= htmlspecialchars($termin['pacijent_ime']) ?>
                            </div>
                            <div class="admin-list-details">
                                <?= htmlspecialchars($termin['usluga']) ?> - <?= htmlspecialchars($termin['terapeut_ime']) ?>
                            </div>
                        </div>
                        <div class="admin-list-time">
                            <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
                    <div class="admin-list-item admin-list-vertical">
                        <div>
                            <div class="admin-list-name">
                                <?= htmlspecialchars($karton['pacijent_ime']) ?>
                            </div>
                            <div class="admin-list-details">
                                <?= htmlspecialchars($karton['dijagnoza']) ?>
                            </div>
                            <div class="admin-list-extra">
                                Terapeut: <?= htmlspecialchars($karton['terapeut_ime']) ?>
                            </div>
                        </div>
                        <div class="admin-list-date">
                            <?= date('d.m.Y', strtotime($karton['datum_kreiranja'])) ?>
                        </div>
                    </div>
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