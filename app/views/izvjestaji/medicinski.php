<div class="naslov-dugme">
    <h2>Medicinski izvještaj</h2>
    <a href="/izvjestaji" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content-fw">
    <!-- Filteri -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <form method="get">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                
                <div class="form-group">
                    <label for="period">Period</label>
                    <select id="period" name="period" onchange="toggleCustomDates()">
                        <option value="ovaj_mesec" <?= $period === 'ovaj_mesec' ? 'selected' : '' ?>>Ovaj mesec</option>
                        <option value="prosli_mesec" <?= $period === 'prosli_mesec' ? 'selected' : '' ?>>Prošli mesec</option>
                        <option value="ova_godina" <?= $period === 'ova_godina' ? 'selected' : '' ?>>Ova godina</option>
                        <option value="custom" <?= $period === 'custom' ? 'selected' : '' ?>>Prilagođeno</option>
                    </select>
                </div>
                
                <div class="form-group" id="custom-dates" style="display: <?= $period === 'custom' ? 'block' : 'none' ?>;">
                    <label for="datum_od">Od - Do</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="datum_od" name="datum_od" value="<?= htmlspecialchars($datum_od) ?>">
                        <input type="date" id="datum_do" name="datum_do" value="<?= htmlspecialchars($datum_do) ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Generiši izvještaj</button>
            </div>
        </form>
    </div>

    <!-- Osnovne statistike -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #5dade2);">
            <h3>Ukupno kartona</h3>
            <div class="stat-number"><?= $ukupno_kartona ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
            <h3>Ukupno tretmana</h3>
            <div class="stat-number"><?= $ukupno_tretmana ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #f4d03f);">
            <h3>Prosek tretmana po kartonu</h3>
            <div class="stat-number">
                <?= $ukupno_kartona > 0 ? number_format($ukupno_tretmana / $ukupno_kartona, 1) : '0' ?>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #9b59b6, #bb6bd9);">
            <h3>Različitih dijagnoza</h3>
            <div class="stat-number"><?= count($dijagnoze) ?></div>
        </div>
    </div>

    <!-- Najčešće dijagnoze -->
    <?php if (!empty($dijagnoze)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">Najčešće dijagnoze</h3>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Dijagnoza</th>
                    <th>Broj slučajeva</th>
                    <th>Broj pacijenata</th>
                    <th>Procenat</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $ukupno_slucajeva = array_sum(array_column($dijagnoze, 'broj_slucajeva'));
                foreach ($dijagnoze as $d): 
                    $procenat = $ukupno_slucajeva > 0 ? ($d['broj_slucajeva'] / $ukupno_slucajeva) * 100 : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($d['dijagnoza']) ?></td>
                    <td><?= $d['broj_slucajeva'] ?></td>
                    <td><?= $d['broj_pacijenata'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="background: #ecf0f1; height: 8px; width: 100px; border-radius: 4px; overflow: hidden;">
                                <div style="background: #3498db; height: 100%; width: <?= $procenat ?>%; border-radius: 4px;"></div>
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

    <!-- Pacijenti sa najviše tretmana -->
    <?php if (!empty($pacijenti_tretmani)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">Pacijenti sa najviše tretmana</h3>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Pacijent</th>
                    <th>Broj tretmana</th>
                    <th>Prvi tretman</th>
                    <th>Poslednji tretman</th>
                    <th>Trajanje terapije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pacijenti_tretmani as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['pacijent']) ?></td>
                    <td style="font-weight: 600; color: #27ae60;"><?= $p['broj_tretmana'] ?></td>
                    <td><?= date('d.m.Y', strtotime($p['prvi_tretman'])) ?></td>
                    <td><?= date('d.m.Y', strtotime($p['poslednji_tretman'])) ?></td>
                    <td>
                        <?php 
                        $dani = (strtotime($p['poslednji_tretman']) - strtotime($p['prvi_tretman'])) / 86400;
                        echo round($dani) . ' dana';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Aktivnost terapeuta -->
    <?php if (!empty($terapeuti_tretmani)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">Aktivnost terapeuta u tretmanima</h3>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Terapeut</th>
                    <th>Broj tretmana</th>
                    <th>Broj kartona</th>
                    <th>Prosek tretmana po kartonu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($terapeuti_tretmani as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['terapeut']) ?></td>
                    <td style="font-weight: 600; color: #27ae60;"><?= $t['broj_tretmana'] ?></td>
                    <td><?= $t['broj_kartona'] ?></td>
                    <td>
                        <?= $t['broj_kartona'] > 0 ? number_format($t['broj_tretmana'] / $t['broj_kartona'], 1) : '0' ?>
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
    customDates.style.display = period === 'custom' ? 'block' : 'none';
}
</script>