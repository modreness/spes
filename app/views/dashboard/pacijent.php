<?php require_once __DIR__ . '/../../helpers/permissions.php'; ?>

<div class="pacijent-dashboard">
    <!-- Uvodni naslov -->
    <div class="pacijent-header">
        <h2>Moj zdravstveni karton</h2>
        <p>Dobro došli, <?= htmlspecialchars($user['ime'] . ' ' . $user['prezime']) ?> - Pregled Vaših termina i tretmana</p>
        
        <?php if ($dashboard_data['sljedeci_termin']): ?>
        <div style="background: #fff; padding: 15px; border-radius: 8px; margin-top: 15px; border-left: 4px solid #255aa5;">
            <div style="font-weight: 600; color: #255aa5; font-size: 0.9em;">SLJEDEĆI TERMIN</div>
            <div style="font-size: 1.1em; color: #2c3e50;"><?= $dashboard_data['sljedeci_termin'] ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Statistički pregled -->
    <div class="pacijent-stats-grid">
        <div class="pacijent-stat-card pacijent-stat-blue">
            <div class="pacijent-stat-content">
                <div class="pacijent-stat-number"><?= $dashboard_data['predstojeci_termini_broj'] ?? 0 ?></div>
                <div class="pacijent-stat-label">Predstojeci termini</div>
                <div class="pacijent-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
            </div>
        </div>
        
        <div class="pacijent-stat-card pacijent-stat-green">
            <div class="pacijent-stat-content">
                <div class="pacijent-stat-number"><?= $dashboard_data['ukupno_tretmana'] ?? 0 ?></div>
                <div class="pacijent-stat-label">Ukupno tretmana</div>
                <div class="pacijent-stat-icon"><i class="fa-solid fa-notes-medical"></i></div>
            </div>
        </div>
        
        <div class="pacijent-stat-card pacijent-stat-purple">
            <div class="pacijent-stat-content">
                <div class="pacijent-stat-number"><?= $dashboard_data['broj_nalaza'] ?? 0 ?></div>
                <div class="pacijent-stat-label">Moji nalazi</div>
                <div class="pacijent-stat-icon"><i class="fa-solid fa-file-medical"></i></div>
            </div>
        </div>
        
        <?php if ($dashboard_data['zadnji_termin']): ?>
        <div class="pacijent-stat-card pacijent-stat-orange">
            <div class="pacijent-stat-content">
                <div class="pacijent-stat-number"><?= $dashboard_data['zadnji_termin'] ?></div>
                <div class="pacijent-stat-label">Zadnji posjet</div>
                <div class="pacijent-stat-icon"><i class="fa-solid fa-clock"></i></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Brze akcije -->
    <div class="pacijent-action-cards">
        <?php if ($dashboard_data['aktivan_karton'] && hasPermission($user, 'pregled_vlastiti_karton')): ?>
        <div class="pacijent-action-card">
            <h3>Moj karton</h3>
            <p>Pregled osnovnih podataka kartona</p>
            <a href="/kartoni/pregled?id=<?= $dashboard_data['aktivan_karton']['id'] ?>" class="pacijent-btn pacijent-btn-primary pacijent-btn-sm">
                <i class="fa-solid fa-folder-open"></i> Otvori karton
            </a>
        </div>
        <?php endif; ?>
        
        <div class="pacijent-action-card">
            <h3>Moji nalazi</h3>
            <p>Pregled uploadovanih nalaza</p>
            <?php if ($dashboard_data['aktivan_karton'] && hasPermission($user, 'pregled_vlastiti_nalazi')): ?>
            <a href="/kartoni/nalazi?id=<?= $dashboard_data['aktivan_karton']['id'] ?>" class="pacijent-btn pacijent-btn-primary pacijent-btn-sm">
                <i class="fa-solid fa-file-medical"></i> Pogledaj nalaze
            </a>
            <?php else: ?>
            <span class="pacijent-btn-disabled">Nema aktivnog kartona</span>
            <?php endif; ?>
        </div>
        
        <div class="pacijent-action-card">
            <h3>Moji tretmani</h3>
            <p>Historija provedenih tretmana</p>
            <?php if ($dashboard_data['aktivan_karton'] && hasPermission($user, 'pregled_vlastiti_tretmani')): ?>
            <a href="/kartoni/tretmani?id=<?= $dashboard_data['aktivan_karton']['id'] ?>" class="pacijent-btn pacijent-btn-primary pacijent-btn-sm">
                <i class="fa-solid fa-notes-medical"></i> Pogledaj tretmane
            </a>
            <?php else: ?>
            <span class="pacijent-btn-disabled">Nema aktivnog kartona</span>
            <?php endif; ?>
        </div>
        
        
    </div>

    <!-- Glavni sadržaj -->
    <div class="pacijent-main-content">
        <!-- Predstojeci termini -->
        <div class="pacijent-content-section">
            <div class="pacijent-section-header">
                <h3>Moji predstojeci termini</h3>
            </div>
            
            <div class="pacijent-section-content">
                <?php if (empty($dashboard_data['predstojeci_termini'])): ?>
                    <div class="pacijent-empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        <p>Nema zakazanih termina</p>
                        <p style="font-size: 0.9em; color: #999;">Kontaktirajte kliniku za zakazivanje</p>
                    </div>
                <?php else: ?>
                    <div class="pacijent-timeline">
                        <?php foreach ($dashboard_data['predstojeci_termini'] as $termin): ?>
                        <div class="pacijent-timeline-item pacijent-status-<?= $termin['status'] ?>">
                            <div class="pacijent-timeline-date">
                                <div class="pacijent-date-day"><?= date('d', strtotime($termin['datum'])) ?></div>
                                <div class="pacijent-date-month"><?= date('M', strtotime($termin['datum'])) ?></div>
                            </div>
                            <div class="pacijent-timeline-content">
                                <div class="pacijent-timeline-time">
                                    <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                                </div>
                                <div class="pacijent-timeline-service">
                                    <?= htmlspecialchars($termin['usluga']) ?>
                                </div>
                                <div class="pacijent-timeline-therapist">
                                    Dr. <?= htmlspecialchars($termin['terapeut_ime']) ?>
                                </div>
                                <div class="pacijent-timeline-status">
                                    <span class="pacijent-status-badge pacijent-status-<?= $termin['status'] ?>">
                                        <?= ucfirst($termin['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar informacije -->
        <div class="pacijent-sidebar">
            <!-- Moj karton info -->
            <?php if ($dashboard_data['aktivan_karton']): ?>
            <div class="pacijent-info-card">
                <div class="pacijent-info-header">
                    <h4>Informacije o kartonu</h4>
                </div>
                <div class="pacijent-info-content">
                    <div class="pacijent-info-item">
                        <label>Broj kartona:</label>
                        <span><?= htmlspecialchars($dashboard_data['aktivan_karton']['broj_upisa']) ?></span>
                    </div>
                    <div class="pacijent-info-item">
                        <label>Datum otvaranja:</label>
                        <span><?= $dashboard_data['aktivan_karton']['datum_otvaranja_format'] ?></span>
                    </div>
                    <?php if ($dashboard_data['aktivan_karton']['dijagnoze_lista'] && $dashboard_data['aktivan_karton']['dijagnoze_lista'] !== 'Nema dodanih dijagnoza'): ?>
                    <div class="pacijent-info-item">
                        <label>Dijagnoza:</label>
                        <span><?= htmlspecialchars($dashboard_data['aktivan_karton']['dijagnoze_lista']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (hasPermission($user, 'pregled_vlastiti_karton')): ?>
                <div class="pacijent-info-footer">
                    <a href="/kartoni/pregled?id=<?= $dashboard_data['aktivan_karton']['id'] ?>" class="pacijent-btn pacijent-btn-outline pacijent-btn-sm">
                        Detaljan pregled
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Poslednji tretmani -->
            <?php if (!empty($dashboard_data['poslednji_tretmani']) && hasPermission($user, 'pregled_vlastiti_tretmani')): ?>
            <div class="pacijent-info-card">
                <div class="pacijent-info-header">
                    <h4>Poslednji tretmani</h4>
                </div>
                <div class="pacijent-info-content">
                    <?php foreach ($dashboard_data['poslednji_tretmani'] as $tretman): ?>
                    <div class="pacijent-treatment-item">
                        <div class="pacijent-treatment-date">
                            <?= $tretman['datum_format'] ?>
                        </div>
                        <div class="pacijent-treatment-therapist">
                            <?= htmlspecialchars($tretman['terapeut_ime']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="pacijent-info-footer">
                    <a href="/kartoni/tretmani?id=<?= $dashboard_data['aktivan_karton']['id'] ?>" class="pacijent-btn pacijent-btn-outline pacijent-btn-sm">
                        Svi tretmani
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Moji nalazi -->
            <?php if (!empty($dashboard_data['moji_nalazi']) && hasPermission($user, 'pregled_vlastiti_nalazi')): ?>
            <div class="pacijent-info-card">
                <div class="pacijent-info-header">
                    <h4>Poslednji nalazi</h4>
                </div>
                <div class="pacijent-info-content">
                    <?php foreach ($dashboard_data['moji_nalazi'] as $nalaz): ?>
                    <div class="pacijent-finding-item">
                        <div class="pacijent-finding-name">
                            <?= htmlspecialchars($nalaz['naziv']) ?>
                        </div>
                        <div class="pacijent-finding-date">
                            <?= $nalaz['datum_upload_format'] ?>
                        </div>
                        <div class="pacijent-finding-link">
                            <a href="/<?= $nalaz['file_path'] ?>" target="_blank">
                                <i class="fa-solid fa-download"></i> Preuzmi
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="pacijent-info-footer">
                    <a href="/kartoni/nalazi?id=<?= $dashboard_data['aktivan_karton']['id'] ?>" class="pacijent-btn pacijent-btn-outline pacijent-btn-sm">
                        Svi nalazi
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kontakt informacije -->
    <div class="pacijent-contact-section">
        <div class="pacijent-contact-card">
            <h3>Kontakt informacije</h3>
            <div class="pacijent-contact-grid">
                <div class="pacijent-contact-item">
                    <i class="fa-solid fa-phone"></i>
                    <div>
                        <strong>Telefon</strong>
                        <span>063/123-456</span>
                    </div>
                </div>
                <div class="pacijent-contact-item">
                    <i class="fa-solid fa-envelope"></i>
                    <div>
                        <strong>Email</strong>
                        <span>info@spes.ba</span>
                    </div>
                </div>
                <div class="pacijent-contact-item">
                    <i class="fa-solid fa-clock"></i>
                    <div>
                        <strong>Radno vrijeme</strong>
                        <span>Pon-Pet: 08:00-20:00</span>
                    </div>
                </div>
                <div class="pacijent-contact-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <div>
                        <strong>Adresa</strong>
                        <span>Sarajevo, BiH</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>