<div class="naslov-dugme">
    <h2>Operativni izvještaj</h2>
    <a href="/izvjestaji" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content-fw">
    <!-- Filteri -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <form method="get">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: center;">
                
                <div class="form-group">
                    <label for="period">Period</label>
                    <select id="period" name="period" onchange="toggleCustomDates()" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        <option value="ova_sedmica" <?= $period === 'ova_sedmica' ? 'selected' : '' ?>>Ova sedmica</option>
                        <option value="ovaj_mesec" <?= $period === 'ovaj_mesec' ? 'selected' : '' ?>>Ovaj mjesec</option>
                        <option value="prosli_mesec" <?= $period === 'prosli_mesec' ? 'selected' : '' ?>>Prošli mjesec</option>
                        <option value="custom" <?= $period === 'custom' ? 'selected' : '' ?>>Prilagođeno</option>
                    </select>
                </div>
                
                <div class="form-group" id="custom-dates" style="display: <?= $period === 'custom' ? 'flex' : 'none' ?>; align-items:center; column-gap:10px;">
                    <label for="datum_od">Od - Do</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="datum_od" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;" name="datum_od" value="<?= htmlspecialchars($datum_od) ?>">
                        <input type="date" id="datum_do" style="padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;" name="datum_do" value="<?= htmlspecialchars($datum_do) ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-search">Generiši izvještaj</button>
            </div>
        </form>
    </div>

    <!-- Osnovne statistike -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card" style="background: linear-gradient(90deg, #255AA5, #255AA5);">
            <h3><i class="fa-solid fa-user-plus"></i> Novi pacijenti</h3>
            <div class="stat-number"><?= $novi_pacijenti ?></div>
            <small style="opacity: 0.9;">u odabranom periodu</small>
        </div>
        <div class="stat-card" style="background: linear-gradient(90deg, #255AA5, #255AA5);">
            <h3><i class="fa-solid fa-users"></i> Ukupno pacijenata</h3>
            <div class="stat-number"><?= $ukupno_pacijenata ?></div>
            <small style="opacity: 0.9;">aktivnih</small>
        </div>
        <div class="stat-card" style="background: linear-gradient(90deg, #289CC6, #289CC6);">
            <h3><i class="fa-solid fa-calendar-check"></i> Ukupno termina</h3>
            <div class="stat-number"><?= $ukupno_termina ?></div>
            <small style="opacity: 0.9;"><?= date('d.m.Y', strtotime($datum_od)) ?> - <?= date('d.m.Y', strtotime($datum_do)) ?></small>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #255AA5);">
            <h3><i class="fa-solid fa-chart-line"></i> Prosječno vrijeme</h3>
            <div class="stat-number">
                <?php 
                $sati = floor($prosecno_vreme / 60);
                $minuti = $prosecno_vreme % 60;
                echo $sati > 0 ? $sati . 'h ' : '';
                echo round($minuti) . 'min';
                ?>
            </div>
            <small style="opacity: 0.9;">između termina</small>
        </div>
    </div>

    <!-- Statistike termina po statusu -->
    <?php if (!empty($statistike_statusa)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 35px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;"><i class="fa-solid fa-chart-pie"></i> Statistika termina po statusu</h3>
        </div>
        <div style="padding: 30px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <?php 
                $statusi_ikone = [
                    'zakazan' => ['ikona' => 'fa-calendar', 'boja' => '#3498db'],
                    'obavljen' => ['ikona' => 'fa-check-circle', 'boja' => '#27ae60'],
                    'otkazan' => ['ikona' => 'fa-times-circle', 'boja' => '#e74c3c'],
                    'propusten' => ['ikona' => 'fa-exclamation-triangle', 'boja' => '#f39c12']
                ];
                
                foreach ($statistike_statusa as $status => $broj): 
                    $ikona_info = $statusi_ikone[$status] ?? ['ikona' => 'fa-circle', 'boja' => '#95a5a6'];
                    $procenat = $ukupno_termina > 0 ? ($broj / $ukupno_termina) * 100 : 0;
                ?>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid <?= $ikona_info['boja'] ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="color: #7f8c8d; font-size: 14px; margin-bottom: 5px;">
                                <i class="fa-solid <?= $ikona_info['ikona'] ?>" style="color: <?= $ikona_info['boja'] ?>;"></i>
                                <?= ucfirst($status) ?>
                            </div>
                            <div style="font-size: 32px; font-weight: 700; color: <?= $ikona_info['boja'] ?>;">
                                <?= $broj ?>
                            </div>
                            <div style="color: #95a5a6; font-size: 13px;">
                                <?= number_format($procenat, 1) ?>% od ukupno
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Graf termina po danima -->
    <?php if (!empty($termini_po_danima)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 35px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;"><i class="fa-solid fa-chart-bar"></i> Broj termina po danima</h3>
        </div>
        <div style="padding: 20px;">
            <div style="display: flex; align-items: end; gap: 5px; height: 250px; overflow-x: auto; padding-bottom:10px;">
                <?php 
                $max_termina = max(array_column($termini_po_danima, 'broj_termina'));
                foreach ($termini_po_danima as $dan): 
                    $visina_ukupno = $max_termina > 0 ? ($dan['broj_termina'] / $max_termina) * 200 : 0;
                    $visina_obavljeno = $max_termina > 0 ? ($dan['obavljeno'] / $max_termina) * 200 : 0;
                ?>
                <div style="display: flex; flex-direction: column; align-items: center; min-width: 60px;">
                    <div style="color: #255AA5; font-size: 12px; margin-bottom: 5px; font-weight: 600;">
                        <?= $dan['broj_termina'] ?>
                    </div>
                    <div style="background: #e0e0e0; width: 40px; height: <?= $visina_ukupno ?>px; border-radius: 4px 4px 0 0; position: relative;">
                        <div style="background: #27ae60; width: 100%; height: <?= ($dan['obavljeno'] / max($dan['broj_termina'], 1)) * 100 ?>%; border-radius: 4px 4px 0 0; position: absolute; bottom: 0;"></div>
                    </div>
                    <div style="color: #666666; font-size: 11px; margin-top: 5px; transform: rotate(-45deg); white-space: nowrap;">
                        <?= date('d.m', strtotime($dan['dan'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: 20px; text-align: center; font-size: 13px; color: #7f8c8d;">
                <span style="display: inline-block; margin-right: 20px;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #e0e0e0; border-radius: 2px; margin-right: 5px;"></span>
                    Ukupno termina
                </span>
                <span style="display: inline-block;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #27ae60; border-radius: 2px; margin-right: 5px;"></span>
                    Obavljeni
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Iskorišćenost terapeuta -->
    <?php if (!empty($iskoriscenost_terapeuta)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 35px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;"><i class="fa-solid fa-user-doctor"></i> Iskorišćenost terapeuta</h3>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Terapeut</th>
                    <th>Ukupno termina</th>
                    <th>Obavljeni</th>
                    <th>Zakazani</th>
                    <th>Otkazani</th>
                    <th>Propušteni</th>
                    <th>Stopa uspješnosti</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($iskoriscenost_terapeuta as $t): 
                    $stopa = $t['ukupno_termina'] > 0 ? ($t['obavljeni'] / $t['ukupno_termina']) * 100 : 0;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['ime'] . ' ' . $t['prezime']) ?></strong></td>
                    <td style="font-weight: 600;"><?= $t['ukupno_termina'] ?></td>
                    <td style="color: #27ae60;"><?= $t['obavljeni'] ?></td>
                    <td style="color: #3498db;"><?= $t['zakazani'] ?></td>
                    <td style="color: #e74c3c;"><?= $t['otkazani'] ?></td>
                    <td style="color: #f39c12;"><?= $t['propusteni'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="background: #ecf0f1; height: 8px; width: 100px; border-radius: 4px; overflow: hidden;">
                                <div style="background: #27ae60; height: 100%; width: <?= $stopa ?>%; border-radius: 4px;"></div>
                            </div>
                            <span style="font-weight: 600;"><?= number_format($stopa, 1) ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Najpopularnije usluge -->
    <?php if (!empty($popularne_usluge)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 35px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;"><i class="fa-solid fa-star"></i> Najpopularnije usluge</h3>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Usluga</th>
                    <th>Kategorija</th>
                    <th>Broj zahtjeva</th>
                    <th>Obavljeno</th>
                    <th>Procenat uspješnosti</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($popularne_usluge as $u): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['usluga']) ?></strong></td>
                    <td><?= htmlspecialchars($u['kategorija'] ?? 'Bez kategorije') ?></td>
                    <td style="font-weight: 600; color: #289CC6;"><?= $u['broj_zahtjeva'] ?></td>
                    <td style="color: #27ae60;"><?= $u['obavljeno'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="background: #ecf0f1; height: 8px; width: 100px; border-radius: 4px; overflow: hidden;">
                                <div style="background: #289CC6; height: 100%; width: <?= $u['procenat_uspjeha'] ?>%; border-radius: 4px;"></div>
                            </div>
                            <span><?= number_format($u['procenat_uspjeha'], 1) ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Statistike po danima u sedmici -->
    <?php if (!empty($statistike_po_danima)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 35px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;"><i class="fa-solid fa-calendar-week"></i> Statistika po danima u sedmici</h3>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Dan</th>
                    <th>Ukupno termina</th>
                    <th>Obavljeno</th>
                    <th>Zakazano</th>
                    <th>Otkazano</th>
                    <th>Procenat obavljenih</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statistike_po_danima as $d): 
                    $procenat = $d['ukupno'] > 0 ? ($d['obavljeno'] / $d['ukupno']) * 100 : 0;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($d['dan']) ?></strong></td>
                    <td style="font-weight: 600;"><?= $d['ukupno'] ?></td>
                    <td style="color: #27ae60;"><?= $d['obavljeno'] ?></td>
                    <td style="color: #3498db;"><?= $d['zakazano'] ?></td>
                    <td style="color: #e74c3c;"><?= $d['otkazano'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="background: #ecf0f1; height: 8px; width: 100px; border-radius: 4px; overflow: hidden;">
                                <div style="background: #27ae60; height: 100%; width: <?= $procenat ?>%; border-radius: 4px;"></div>
                            </div>
                            <span><?= number_format($procenat, 1) ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<script>
function toggleCustomDates() {
    const period = document.getElementById('period').value;
    const customDates = document.getElementById('custom-dates');
    customDates.style.display = period === 'custom' ? 'flex' : 'none';
}
</script>