<div class="naslov-dugme">
    <h2>Kalendar termina</h2>
    <a href="/termini" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content-fw">
    <!-- Filter -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <form method="get" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
            <div class="form-group">
                <label for="mesec">Mesec</label>
                <select id="mesec" name="mesec">
                    <?php 
                    $meseci = [
                        1 => 'Januar', 2 => 'Februar', 3 => 'Mart', 4 => 'April',
                        5 => 'Maj', 6 => 'Jun', 7 => 'Jul', 8 => 'Avgust',
                        9 => 'Septembar', 10 => 'Oktobar', 11 => 'Novembar', 12 => 'Decembar'
                    ];
                    foreach ($meseci as $br => $naziv): ?>
                        <option value="<?= $br ?>" <?= $mesec == $br ? 'selected' : '' ?>><?= $naziv ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="godina">Godina</label>
                <select id="godina" name="godina">
                    <?php for ($g = 2024; $g <= 2030; $g++): ?>
                        <option value="<?= $g ?>" <?= $godina == $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="terapeut">Terapeut</label>
                <select id="terapeut" name="terapeut">
                    <option value="">Svi terapeuti</option>
                    <?php foreach ($terapeuti as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $terapeut_filter == $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['ime'] . ' ' . $t['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Prikaži</button>
        </form>
    </div>

    <!-- Kalendar -->
    <?php
    $broj_dana = date('t', strtotime($prvi_dan));
    $prvi_dan_nedelje = date('w', strtotime($prvi_dan));
    $prvi_dan_nedelje = $prvi_dan_nedelje == 0 ? 7 : $prvi_dan_nedelje;
    ?>
    
    <div class="calendar-grid" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div style="background: #2c3e50; color: white; padding: 20px; text-align: center;">
            <h3 style="margin: 0; font-size: 24px;"><?= $meseci[$mesec] ?> <?= $godina ?></h3>
        </div>

        <div style="display: grid; grid-template-columns: repeat(7, 1fr); background: #34495e;">
            <?php foreach (['Pon', 'Uto', 'Sre', 'Čet', 'Pet', 'Sub', 'Ned'] as $dan): ?>
                <div style="padding: 12px; text-align: center; color: white; font-weight: 600; font-size: 14px;">
                    <?= $dan ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #ecf0f1;">
            <?php for ($i = 1; $i < $prvi_dan_nedelje; $i++): ?>
                <div style="background: #bdc3c7; min-height: 120px;"></div>
            <?php endfor; ?>

            <?php for ($dan = 1; $dan <= $broj_dana; $dan++): ?>
                <?php 
                $danas = date('j') == $dan && date('m') == $mesec && date('Y') == $godina;
                $termini_dana = $termini_po_danu[$dan] ?? [];
                ?>
                <div style="background: #fff; min-height: 120px; padding: 8px; <?= $danas ? 'border: 2px solid #3498db;' : '' ?>">
                    <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                        <?= $dan ?>
                    </div>
                    
                    <?php foreach (array_slice($termini_dana, 0, 3) as $termin): ?>
                        <div style="background: #27ae60; color: white; padding: 2px 6px; margin: 2px 0; border-radius: 3px; font-size: 11px;">
                            <?= date('H:i', strtotime($termin['datum_vrijeme'])) ?> 
                            <?= htmlspecialchars($termin['pacijent_ime']) ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($termini_dana) > 3): ?>
                        <div style="font-size: 10px; color: #7f8c8d; text-align: center;">
                            +<?= count($termini_dana) - 3 ?> više
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>