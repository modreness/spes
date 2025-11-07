<?php require_once __DIR__ . '/../../helpers/permissions.php'; ?>

<div class="pacijent-termini">
    <!-- Header -->
    <div class="naslov-dugme">
        <h2>Moji termini</h2>
        <a href="/dashboard" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Povratak na dashboard
        </a>
    </div>

    <!-- Statistike -->
    <div class="pacijent-stats-grid" style="margin-bottom: 30px;">
        <div class="pacijent-stat-card pacijent-stat-blue">
            <div class="pacijent-stat-content">
                <div class="pacijent-stat-number"><?= $statistike['buduci_termini'] ?></div>
                <div class="pacijent-stat-label">Budući termini</div>
                <div class="pacijent-stat-icon"><i class="fa-solid fa-calendar-plus"></i></div>
            </div>
        </div>
        
        <div class="pacijent-stat-card pacijent-stat-green">
            <div class="pacijent-stat-content">
                <div class="pacijent-stat-number"><?= $statistike['obavljeni_termini'] ?></div>
                <div class="pacijent-stat-label">Obavljeni termini</div>
                <div class="pacijent-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
            </div>
        </div>
        
        <div class="pacijent-stat-card pacijent-stat-purple">
            <div class="pacijent-stat-content">
                <div class="pacijent-stat-number"><?= $statistike['ukupno_termina'] ?></div>
                <div class="pacijent-stat-label">Ukupno termina</div>
                <div class="pacijent-stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
            </div>
        </div>
        
        <?php if ($statistike['otkazani_termini'] > 0): ?>
        <div class="pacijent-stat-card pacijent-stat-orange">
            <div class="pacijent-stat-content">
                <div class="pacijent-stat-number"><?= $statistike['otkazani_termini'] ?></div>
                <div class="pacijent-stat-label">Otkazani termini</div>
                <div class="pacijent-stat-icon"><i class="fa-solid fa-calendar-xmark"></i></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick info cards -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <?php if ($sljedeci_termin): ?>
        <div style="background: #e7f3ff; padding: 20px; border-radius: 12px; border-left: 4px solid #4e73df;">
            <h4 style="margin: 0 0 10px 0; color: #4e73df;">
                <i class="fa-solid fa-clock"></i> Sljedeći termin
            </h4>
            <div style="color: #2c3e50; font-weight: 600; font-size: 1.1em;">
                <?= $sljedeci_termin['datum_vrijeme_format'] ?>
            </div>
            <div style="color: #666; margin-top: 5px;">
                <?= htmlspecialchars($sljedeci_termin['usluga_naziv']) ?> - Dr. <?= htmlspecialchars($sljedeci_termin['terapeut_ime']) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($zadnji_termin): ?>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; border-left: 4px solid #6c757d;">
            <h4 style="margin: 0 0 10px 0; color: #6c757d;">
                <i class="fa-solid fa-history"></i> Zadnji termin
            </h4>
            <div style="color: #2c3e50; font-weight: 600; font-size: 1.1em;">
                <?= $zadnji_termin['datum_vrijeme_format'] ?>
            </div>
            <div style="color: #666; margin-top: 5px;">
                <?= htmlspecialchars($zadnji_termin['usluga_naziv']) ?> - Dr. <?= htmlspecialchars($zadnji_termin['terapeut_ime']) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filteri -->
    <div class="filters-section" style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 30px;">
        <h4 style="margin-bottom: 15px;"><i class="fa-solid fa-filter"></i> Filteri</h4>
        
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            <div>
                <label>Prikaži:</label>
                <select name="show" class="form-control">
                    <option value="all" <?= $show === 'all' ? 'selected' : '' ?>>Svi termini</option>
                    <option value="budući" <?= $show === 'budući' ? 'selected' : '' ?>>Budući termini</option>
                    <option value="prošli" <?= $show === 'prošli' ? 'selected' : '' ?>>Prošli termini</option>
                </select>
            </div>
            
            <div>
                <label>Status:</label>
                <select name="status" class="form-control">
                    <option value="">Svi statusi</option>
                    <option value="zakazan" <?= $status_filter === 'zakazan' ? 'selected' : '' ?>>Zakazani</option>
                    <option value="obavljen" <?= $status_filter === 'obavljen' ? 'selected' : '' ?>>Obavljeni</option>
                    <option value="otkazan" <?= $status_filter === 'otkazan' ? 'selected' : '' ?>>Otkazani</option>
                </select>
            </div>
            
            <div>
                <label>Datum od:</label>
                <input type="date" name="datum_od" value="<?= htmlspecialchars($datum_od) ?>" class="form-control">
            </div>
            
            <div>
                <label>Datum do:</label>
                <input type="date" name="datum_do" value="<?= htmlspecialchars($datum_do) ?>" class="form-control">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-search"></i> Filtriraj
                </button>
                <a href="/moji-termini" class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i> Očisti
                </a>
            </div>
        </form>
    </div>

    <!-- Lista termina -->
    <div class="main-content-fw">
        <?php if (empty($moji_termini)): ?>
            <div style="text-align: center; padding: 60px 20px; color: #666;">
                <i class="fa-solid fa-calendar-xmark" style="font-size: 48px; margin-bottom: 20px; color: #ddd;"></i>
                <h3 style="margin-bottom: 10px;">Nema termina</h3>
                <p>Nema termina koji odgovaraju odabranim kriterijima.</p>
            </div>
        <?php else: ?>
            <table class="table-standard">
                <thead>
                    <tr>
                        <th>Datum i vrijeme</th>
                        <th>Usluga</th>
                        <th>Terapeut</th>
                        <th>Status</th>
                        <th>Cijena</th>
                        <th>Napomene</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moji_termini as $termin): ?>
                    <tr class="termin-row <?= $termin['tip_termina'] ?> status-<?= $termin['status'] ?>">
                        <td>
                            <div style="font-weight: 600; color: #2c3e50;">
                                <?= $termin['datum_format'] ?>
                            </div>
                            <div style="color: #666; font-size: 0.9em;">
                                <?= $termin['vrijeme_format'] ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500;">
                                <?= htmlspecialchars($termin['usluga_naziv']) ?>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-user-doctor" style="color: #4e73df;"></i>
                                Dr. <?= htmlspecialchars($termin['terapeut_ime']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $termin['status'] ?>">
                                <?php 
                                $statusi = [
                                    'zakazan' => 'Zakazan',
                                    'obavljen' => 'Obavljen', 
                                    'otkazan' => 'Otkazan',
                                    'u_toku' => 'U toku'
                                ];
                                echo $statusi[$termin['status']] ?? ucfirst($termin['status']);
                                ?>
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #28a745;">
                                <?= number_format($termin['stvarna_cijena'], 2) ?> KM
                            </div>
                            <?php if ($termin['placeno_iz_paketa']): ?>
                            <small style="color: #666; font-style: italic;">
                                <i class="fa-solid fa-box"></i> Iz paketa
                            </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($termin['napomene']): ?>
                            <div style="font-size: 0.9em; color: #666;">
                                <?= htmlspecialchars(mb_strimwidth($termin['napomene'], 0, 50, '...')) ?>
                            </div>
                            <?php else: ?>
                            <span style="color: #ccc; font-style: italic;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.termin-row.budući {
    background-color: #f8f9ff;
    border-left: 3px solid #4e73df;
}

.termin-row.prošli {
    background-color: #f8f9fa;
    border-left: 3px solid #6c757d;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.status-zakazan {
    background: #e3f2fd;
    color: #1976d2;
}

.status-badge.status-obavljen {
    background: #e8f5e8;
    color: #2e7d32;
}

.status-badge.status-otkazan {
    background: #ffebee;
    color: #c62828;
}

.status-badge.status-u_toku {
    background: #fff3e0;
    color: #f57c00;
}

.filters-section label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #555;
}

.filters-section .form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
}
</style>