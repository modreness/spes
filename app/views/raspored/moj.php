<div class="naslov-dugme">
    <h2>Moj raspored</h2>
    <a href="/dashboard" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content-fw">
    <!-- Statistike -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #255AA5);">
            <h3>Termina ove sedmice</h3>
            <div class="stat-number"><?= $ukupno_termina ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #289CC6);">
            <h3>Obavljeno</h3>
            <div class="stat-number"><?= $broj_obavljenih ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #289CC6);">
            <h3>Uspješnost</h3>
            <div class="stat-number"><?= $ukupno_termina > 0 ? round(($broj_obavljenih / $ukupno_termina) * 100) : 0 ?>%</div>
        </div>
    </div>

    <!-- Period info -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 25px; text-align: center;">
        <h4 style="margin: 0; color: #2c3e50;">
            <i class="fa-solid fa-calendar-alt"></i> 
            Sedmični raspored (<?= date('d.m.Y', strtotime('monday this week')) ?> - <?= date('d.m.Y', strtotime('sunday this week')) ?>)
        </h4>
    </div>

    <!-- Raspored tabela -->
    <?php if (empty($moj_raspored)): ?>
        <div style="background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; margin-bottom: 25px;">
            <i class="fa-solid fa-info-circle" style="font-size: 48px; color: #289CC6; margin-bottom: 15px; opacity: 0.3;"></i>
            <h4 style="color: #2c3e50; margin-bottom: 10px;">Nema definisanog rasporeda</h4>
            <p style="color: #7f8c8d; margin: 0;">Kontaktirajte administratora za definisanje radnog vremena.</p>
        </div>
    <?php else: ?>
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; margin-bottom: 25px;">
            <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
                <h3 style="margin: 0; color: #2c3e50;">
                    <i class="fa-solid fa-business-time"></i> Moje radno vreme
                </h3>
            </div>
            
            <table class="table-standard">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Ponedjeljak</th>
                        <th>Utorak</th>
                        <th>Srijeda</th>
                        <th>Četvrtak</th>
                        <th>Petak</th>
                        <th>Subota</th>
                        <th>Nedjelja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moj_raspored as $raspored): ?>
                    <tr>
                        <td><strong><?= $raspored['datum_od_format'] ?> - <?= $raspored['datum_do_format'] ?></strong></td>
                        <td><?= $raspored['ponedjeljak'] ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                        <td><?= $raspored['utorak'] ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                        <td><?= $raspored['srijeda'] ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                        <td><?= $raspored['cetvrtak'] ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                        <td><?= $raspored['petak'] ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                        <td><?= $raspored['subota'] ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                        <td><?= $raspored['nedjelja'] ?: '<span style="color: #bdc3c7;">-</span>' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Termini po danima -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">
                <i class="fa-solid fa-calendar-check"></i> Moji termini ove sedmice
            </h3>
        </div>
        
        <?php if (empty($termini_po_danima)): ?>
            <div style="padding: 40px; text-align: center; color: #7f8c8d;">
                <i class="fa-solid fa-calendar-xmark" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <p style="font-size: 18px; margin: 0;">
                    Nema zakazanih termina za ovu sedmicu.
                </p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1px; background: #ecf0f1;">
                <?php 
                $dani = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                $dani_naziv = ['Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota', 'Nedjelja'];
                
                for ($i = 0; $i < 7; $i++):
                    $dan_datum = date('Y-m-d', strtotime($dani[$i] . ' this week'));
                    $termini_dana = $termini_po_danima[$dan_datum] ?? [];
                    $je_danas = ($dan_datum === date('Y-m-d'));
                ?>
                <div style="background: #fff; padding: 15px; <?= $je_danas ? 'border: 3px solid #255AA5;' : '' ?>">
                    <div style="<?= $je_danas ? 'background: #255AA5; color: white;' : 'background: #f8f9fa; color: #2c3e50;' ?> padding: 10px; border-radius: 8px; text-align: center; margin-bottom: 15px;">
                        <div style="font-weight: 600; font-size: 1.1em;"><?= $dani_naziv[$i] ?></div>
                        <div style="font-size: 0.9em; opacity: 0.8;"><?= date('d.m.Y', strtotime($dan_datum)) ?></div>
                        <?= $je_danas ? '<div style="background: rgba(255,255,255,0.2); padding: 3px 8px; border-radius: 12px; font-size: 0.8em; margin-top: 5px; display: inline-block;">DANAS</div>' : '' ?>
                    </div>
                    
                    <?php if (empty($termini_dana)): ?>
                        <div style="text-align: center; color: #bdc3c7; font-style: italic; padding: 20px;">
                            Slobodan dan
                        </div>
                    <?php else: ?>
                        <?php foreach ($termini_dana as $termin): ?>
                        <div style="border: 1px solid #e9ecef; border-radius: 8px; padding: 12px; margin-bottom: 10px; <?= $termin['status'] === 'obavljen' ? 'background: #f8fff9; border-color: #27ae60;' : ($termin['status'] === 'zakazan' ? 'background: #f0f8ff; border-color: #255AA5;' : 'background: #fff8f0; border-color: #f39c12;') ?>">
                            <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 8px;">
                                <div style="font-weight: 600; color: #2c3e50;"><?= $termin['vrijeme'] ?></div>
                                <span style="background: <?= $termin['status'] === 'obavljen' ? '#27ae60' : ($termin['status'] === 'zakazan' ? '#255AA5' : '#f39c12') ?>; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 500;">
                                    <?= ucfirst($termin['status']) ?>
                                </span>
                            </div>
                            <div style="font-size: 0.9em; color: #2c3e50; margin-bottom: 5px;"><?= htmlspecialchars($termin['pacijent_ime']) ?></div>
                            <div style="font-size: 0.8em; color: #7f8c8d;"><?= htmlspecialchars($termin['usluga']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Brze akcije -->
    <div class="action-cards" style="margin-top: 30px;">
        <div class="action-card">
            <h3>Kalendar termina</h3>
            <p>Kalendarski prikaz svih termina</p>
            <a href="/termini/kalendar" class="btn btn-add">
                <i class="fa-solid fa-calendar-alt"></i> Otvori kalendar
            </a>
        </div>
        
        <div class="action-card">
            <h3>Moji pacijenti</h3>
            <p>Lista pacijenata sa kojima radim</p>
            <a href="/kartoni/moji" class="btn btn-add">
                <i class="fa-solid fa-users"></i> Pregled pacijenata
            </a>
        </div>
        
        <div class="action-card">
            <h3>Moji tretmani</h3>
            <p>Historija tretmana koje sam radio</p>
            <a href="/tretmani/moji" class="btn btn-add">
                <i class="fa-solid fa-notes-medical"></i> Pregled tretmana
            </a>
        </div>
    </div>

    <!-- Legenda -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-top: 25px;">
        <h4 style="margin: 0 0 15px 0; color: #2c3e50;">
            <i class="fa-solid fa-info-circle"></i> Legenda statusa
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="background: #27ae60; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">Obavljen</span>
                <span style="color: #7f8c8d;">Završen termin</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="background: #255AA5; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">Zakazan</span>
                <span style="color: #7f8c8d;">Predstojeci termin</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="background: #f39c12; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">U toku</span>
                <span style="color: #7f8c8d;">Trenutno izvršavanje</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="background: #e74c3c; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">Otkazan</span>
                <span style="color: #7f8c8d;">Otkazani termin</span>
            </div>
        </div>
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
            <small style="color: #7f8c8d;">
                Za izmejne rasporeda kontaktirajte administratora. Sedmica se računa od ponedeljka do nedelje.
            </small>
        </div>
    </div>
</div>