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
                <div class="terapeut-stat-number"><?= $dashboard_data['broj_mojih_pacijenata'] ?? 0 ?></div>
                <div class="terapeut-stat-label">Moji pacijenti</div>
                <div class="terapeut-stat-icon"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
        
        <div class="terapeut-stat-card terapeut-stat-purple">
            <div class="terapeut-stat-content">
                <div class="terapeut-stat-number"><?= $dashboard_data['termini_ova_sedmica'] ?? 0 ?></div>
                <div class="terapeut-stat-label">Termini ove sedmice</div>
                <div class="terapeut-stat-icon"><i class="fa-solid fa-calendar-week"></i></div>
            </div>
        </div>
    </div>

    <!-- Brze akcije -->
    <div class="terapeut-action-cards">
        <div class="terapeut-action-card">
            <h3>Moj raspored</h3>
            <p>Pregled rasporeda rada i dostupnih termina</p>
            <a href="/raspored/moj" class="terapeut-btn terapeut-btn-primary">
                <i class="fa-solid fa-calendar-days"></i> Moj raspored
            </a>
        </div>
        
        <div class="terapeut-action-card">
            <h3>Moji pacijenti</h3>
            <p>Lista svih pacijenata sa kojima radite</p>
            <a href="/kartoni/moji" class="terapeut-btn terapeut-btn-success">
                <i class="fa-solid fa-users"></i> Pregled pacijenata
            </a>
        </div>
        
        <div class="terapeut-action-card">
            <h3>Tretmani</h3>
            <p>História tretmana i statistike</p>
            <a href="/tretmani/moji" class="terapeut-btn terapeut-btn-info">
                <i class="fa-solid fa-notes-medical"></i> Moji tretmani
            </a>
        </div>
    </div>

    <!-- Glavni sadržaj -->
    <div class="terapeut-main-content">
        <!-- Moji termini danas -->
        <div class="terapeut-content-section">
            <div class="terapeut-section-header">
                <h3>Moji termini danas</h3>
                <div class="terapeut-header-actions">
                    <a href="/termini/kalendar" class="terapeut-btn terapeut-btn-outline terapeut-btn-xs">Kalendar</a>
                </div>
            </div>
            
            <div class="terapeut-section-content">
                <?php if (empty($dashboard_data['moji_termini_danas'])): ?>
                    <div class="terapeut-empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        <p>Nema termina za danas</p>
                        <p style="font-size: 0.9em; color: #999;">Možete se fokusirati na administrativne zadatke</p>
                    </div>
                <?php else: ?>
                    <div class="terapeut-timeline">
                        <?php foreach ($dashboard_data['moji_termini_danas'] as $termin): ?>
                        <div class="terapeut-timeline-item terapeut-status-<?= $termin['status'] ?>">
                            <div class="terapeut-timeline-time">
                                <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                            </div>
                            <div class="terapeut-timeline-content">
                                <div class="terapeut-timeline-title">
                                    <?= htmlspecialchars($termin['pacijent_ime']) ?>
                                </div>
                                <div class="terapeut-timeline-details">
                                    <?= htmlspecialchars($termin['usluga']) ?>
                                </div>
                                <div class="terapeut-timeline-status">
                                    <span class="terapeut-status-badge terapeut-status-<?= $termin['status'] ?>">
                                        <?= ucfirst($termin['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="terapeut-timeline-actions">
                                <?php if ($termin['karton_id']): ?>
                                <a href="/kartoni/pregled?id=<?= $termin['karton_id'] ?>" class="terapeut-btn terapeut-btn-outline terapeut-btn-xs">
                                    <i class="fa-solid fa-folder-open"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($termin['status'] === 'obavljen' && $termin['karton_id']): ?>
                                <button type="button" 
                                        class="terapeut-btn terapeut-btn-success terapeut-btn-xs" 
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

        <!-- Moji pacijenti -->
        <div class="terapeut-content-section">
            <div class="terapeut-section-header">
                <h3>Moji pacijenti</h3>
                <div class="terapeut-header-actions">
                    <a href="/kartoni/moji" class="terapeut-btn terapeut-btn-outline terapeut-btn-xs">Svi pacijenti</a>
                </div>
            </div>
            
            <div class="terapeut-section-content">
                <?php if (empty($dashboard_data['moji_pacijenti'])): ?>
                    <div class="terapeut-empty-state">
                        <i class="fa-solid fa-users"></i>
                        <p>Nema dodeljenih pacijenata</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($dashboard_data['moji_pacijenti'] as $pacijent): ?>
                    <div class="terapeut-patient-item">
                        <div class="terapeut-patient-content">
                            <div class="terapeut-patient-name">
                                <a href="/kartoni/pregled?id=<?= $pacijent['id'] ?>">
                                    <?= htmlspecialchars($pacijent['pacijent_ime']) ?>
                                </a>
                            </div>
                            <div class="terapeut-patient-details">
                                Broj kartona: <?= htmlspecialchars($pacijent['broj_upisa']) ?>
                            </div>
                            <div class="terapeut-patient-stats">
                                Tretmani: <?= $pacijent['broj_tretmana'] ?>
                                <?php if ($pacijent['poslednji_tretman']): ?>
                                    | Poslednji: <?= date('d.m.Y', strtotime($pacijent['poslednji_tretman'])) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="terapeut-patient-actions">
                            <a href="/kartoni/pregled?id=<?= $pacijent['id'] ?>" class="terapeut-btn terapeut-btn-outline terapeut-btn-xs">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="/kartoni/tretmani?id=<?= $pacijent['id'] ?>" class="terapeut-btn terapeut-btn-success terapeut-btn-xs">
                                <i class="fa-solid fa-notes-medical"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sedmični pregled -->
    <?php if (!empty($dashboard_data['termini_sedmica'])): ?>
    <div class="terapeut-weekly-section">
        <div class="terapeut-section-header">
            <h3>Termini ove sedmice</h3>
            <div class="terapeut-header-actions">
                <a href="/termini/kalendar" class="terapeut-btn terapeut-btn-outline terapeut-btn-xs">Pun kalendar</a>
            </div>
        </div>
        
        <div class="terapeut-weekly-grid">
            <?php 
            $grouped_by_date = [];
            foreach ($dashboard_data['termini_sedmica'] as $termin) {
                $date = $termin['datum'];
                $grouped_by_date[$date][] = $termin;
            }
            ?>
            <?php foreach ($grouped_by_date as $date => $termini): ?>
            <div class="terapeut-daily-card <?= date('Y-m-d') === $date ? 'terapeut-today' : '' ?>">
                <div class="terapeut-daily-header">
                    <div class="terapeut-daily-date">
                        <?= date('d.m', strtotime($date)) ?>
                    </div>
                    <div class="terapeut-daily-day">
                        <?= strftime('%a', strtotime($date)) ?>
                    </div>
                </div>
                <div class="terapeut-daily-termini">
                    <?php foreach ($termini as $termin): ?>
                    <div class="terapeut-daily-termin">
                        <div class="terapeut-termin-time"><?= date('H:i', strtotime($termin['vrijeme'])) ?></div>
                        <div class="terapeut-termin-patient"><?= htmlspecialchars($termin['pacijent_ime']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<!-- Modal za tretman (isti kao admin) -->
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
    
    document.querySelector('#tretman-modal form').reset();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modal-overlay').addEventListener('click', function() {
        zatvoriModalTretman();
    });
});
</script>