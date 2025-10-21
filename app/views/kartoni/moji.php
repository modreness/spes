<div class="naslov-dugme">
    <h2>Moji pacijenti</h2>
    <a href="/dashboard" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content-fw">
    <!-- Statistike -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #255AA5);">
            <h3>Ukupno pacijenata</h3>
            <div class="stat-number"><?= $ukupno_pacijenata ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #289CC6);">
            <h3>Ukupno tretmana</h3>
            <div class="stat-number"><?= $ukupno_tretmana ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #289CC6);">
            <h3>Termini (30 dana)</h3>
            <div class="stat-number"><?= $termini_30_dana ?></div>
        </div>
    </div>

    <!-- Follow-up upozorenja -->
    <?php if (!empty($potreban_followup)): ?>
    <div style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white; padding: 20px; border-radius: 12px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0;">
            <i class="fa-solid fa-exclamation-triangle"></i> Pacijenti kojima je potreban follow-up
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <?php foreach ($potreban_followup as $pacijent): ?>
            <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px;">
                <div style="font-weight: 600; font-size: 1.1em;">
                    <a href="/kartoni/pregled?id=<?= $pacijent['id'] ?>" style="color: white; text-decoration: none;">
                        <?= htmlspecialchars($pacijent['pacijent_ime']) ?>
                    </a>
                </div>
                <div style="opacity: 0.9; margin-top: 5px;">
                    Poslednji tretman: <strong><?= $pacijent['dana_od_tretmana'] ?> dana ago</strong>
                </div>
                <div style="margin-top: 10px;">
                    <a href="/kartoni/pregled?id=<?= $pacijent['id'] ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-eye"></i> Pregled
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista pacijenata -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">
                <i class="fa-solid fa-users"></i> Lista mojih pacijenata
            </h3>
        </div>
        
        <?php if (empty($moji_kartoni)): ?>
            <div style="padding: 40px; text-align: center; color: #7f8c8d;">
                <i class="fa-solid fa-users" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <p style="font-size: 18px; margin: 0;">
                    Nemate dodeljenih pacijenata ili niste još uvek radili ni sa kim.
                </p>
            </div>
        <?php else: ?>
            <table class="table-standard">
                <thead>
                    <tr>
                        <th>Pacijent</th>
                        <th>Broj kartona</th>
                        <th>Email</th>
                        <th>JMBG</th>
                        <th>Broj termina</th>
                        <th>Broj tretmana</th>
                        <th>Poslednja aktivnost</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moji_kartoni as $karton): ?>
                    <tr>
                        <td>
                            <a class="openlink" href="/kartoni/pregled?id=<?= $karton['id'] ?>">
                                <strong><?= htmlspecialchars($karton['pacijent_ime']) ?></strong>
                            </a>
                            <?php if ($karton['dijagnoza']): ?>
                                <br><small style="color: #7f8c8d;"><?= htmlspecialchars($karton['dijagnoza']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="background: #289CC6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= htmlspecialchars($karton['broj_upisa']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($karton['email']) ?></td>
                        <td><?= htmlspecialchars($karton['jmbg']) ?></td>
                        <td>
                            <span style="background: #255AA5; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= $karton['broj_termina'] ?>
                            </span>
                        </td>
                        <td>
                            <span style="background: #27ae60; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= $karton['broj_tretmana'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($karton['poslednji_tretman']): ?>
                                <small>Tretman: <?= date('d.m.Y', strtotime($karton['poslednji_tretman'])) ?></small>
                            <?php elseif ($karton['poslednji_termin']): ?>
                                <small>Termin: <?= date('d.m.Y H:i', strtotime($karton['poslednji_termin'])) ?></small>
                            <?php else: ?>
                                <small style="color: #7f8c8d;">Nema aktivnosti</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/kartoni/pregled?id=<?= $karton['id'] ?>" class="btn btn-sm btn-view" title="Pregled kartona">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="/kartoni/tretmani?id=<?= $karton['id'] ?>" class="btn btn-sm btn-add" title="Tretmani">
                                <i class="fa-solid fa-notes-medical"></i>
                            </a>
                            <a href="/kartoni/nalazi?id=<?= $karton['id'] ?>" class="btn btn-sm btn-edit" title="Nalazi">
                                <i class="fa-solid fa-file-medical"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Brze akcije -->
    <div class="action-cards" style="margin-top: 30px;">
        <div class="action-card">
            <h3>Moji tretmani</h3>
            <p>Pregled svih tretmana koje sam radio</p>
            <a href="/tretmani/moji" class="btn btn-add">
                <i class="fa-solid fa-notes-medical"></i> Pregled tretmana
            </a>
        </div>
        
        <div class="action-card">
            <h3>Kalendar termina</h3>
            <p>Kalendarski prikaz mojih termina</p>
            <a href="/termini/kalendar" class="btn btn-add">
                <i class="fa-solid fa-calendar-alt"></i> Otvori kalendar
            </a>
        </div>
        
        <div class="action-card">
            <h3>Moje statistike</h3>
            <p>Detaljni izvještaji i analiza rada</p>
            <a href="/izvjestaji/terapeut" class="btn btn-add">
                <i class="fa-solid fa-chart-line"></i> Statistike
            </a>
        </div>
        
        <div class="action-card">
            <h3>Moj raspored</h3>
            <p>Pregled radnog vremena i rasporeda</p>
            <a href="/raspored/moj" class="btn btn-add">
                <i class="fa-solid fa-calendar-days"></i> Moj raspored
            </a>
        </div>
    </div>

    <!-- Legenda -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-top: 25px;">
        <h4 style="margin: 0 0 15px 0; color: #2c3e50;">
            <i class="fa-solid fa-info-circle"></i> Legenda
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; color: #7f8c8d;">
            <div><i class="fa-solid fa-eye" style="color: #255AA5;"></i> Pregled kartona pacijenta</div>
            <div><i class="fa-solid fa-notes-medical" style="color: #27ae60;"></i> Historie tretmana</div>
            <div><i class="fa-solid fa-file-medical" style="color: #f39c12;"></i> Nalazi pacijenta</div>
            <div><i class="fa-solid fa-exclamation-triangle" style="color: #e74c3c;"></i> Follow-up potreban (>14 dana)</div>
        </div>
        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e9ecef;">
            <small style="color: #7f8c8d;">
                Prikazani su samo pacijenti sa kojima ste radili (imali termine ili tretmane).
            </small>
        </div>
    </div>
</div>