<div class="naslov-dugme">
    <h2>Finansijski izvještaj</h2>
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
                        <option value="danas" <?= $period === 'danas' ? 'selected' : '' ?>>Danas</option>
                        <option value="ova_sedmica" <?= $period === 'ova_sedmica' ? 'selected' : '' ?>>Ova sedmica</option>
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

    <!-- Osnovne statistike - AŽURIRANO -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card" style="background: linear-gradient(90deg, #255AA5, #255AA5);">
            <h3>Ukupni prihod</h3>
            <div class="stat-number"><?= number_format($ukupni_prihodi['ukupno'], 2) ?> KM</div>
            <small style="opacity: 0.9;">Paketi + Termini</small>
        </div>
        <div class="stat-card" style="background: linear-gradient(90deg, #255AA5, #255AA5);">
            <h3>Prihod od paketa</h3>
            <div class="stat-number"><?= number_format($ukupni_prihodi['paketi_prihod'], 2) ?> KM</div>
            <small style="opacity: 0.9;"><?= $ukupni_prihodi['broj_paketa'] ?> prodatih</small>
        </div>
        <div class="stat-card" style="background: linear-gradient(90deg, #289CC6, #289CC6);">
            <h3>Prihod od termina</h3>
            <div class="stat-number"><?= number_format($ukupni_prihodi['termini_prihod'], 2) ?> KM</div>
            <small style="opacity: 0.9;"><?= $ukupni_prihodi['broj_termina'] ?> pojedinačnih</small>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #255AA5);">
            <h3>Broj termina</h3>
            <div class="stat-number">
                <div class="stat-number"><?php $ukupan_broj = $ukupni_prihodi['broj_termina'] + $ukupni_prihodi['broj_paketa']; echo $ukupan_broj; ?></div>
            </div>
            <small style="opacity: 0.9;"><?= date('d.m.Y', strtotime($datum_od)) ?> do 
                <?= date('d.m.Y', strtotime($datum_do)) ?></small>
        </div>
    </div>

  

    <!-- Graf prihoda po danima -->
    <?php if (!empty($prihodi_po_danima)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">Prihodi po danima</h3>
        </div>
        <div style="padding: 20px;">
            <div style="display: flex; align-items: end; gap: 5px; height: 250px; overflow-x: auto; padding-bottom:10px;">
                <?php 
                $max_prihod = max(array_column($prihodi_po_danima, 'prihod'));
                foreach ($prihodi_po_danima as $dan): 
                    $visina = $max_prihod > 0 ? ($dan['prihod'] / $max_prihod) * 160 : 0;
                ?>
                <div style="display: flex; flex-direction: column; align-items: center; min-width: 60px;">
                    <div style="color: #255AA5; font-size: 12px; margin-bottom: 5px; font-weight: 600;">
                        <?= number_format($dan['prihod'], 0) ?>
                    </div>
                    <div style="background: #289CC6; width: 40px; height: <?= $visina ?>px; border-radius: 4px 4px 0 0;"></div>
                    <div style="color: #666666; font-size: 11px; margin-top: 5px; transform: rotate(-45deg); white-space: nowrap;">
                        <?= date('d.m', strtotime($dan['dan'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Prihodi po uslugama -->
    <?php if (!empty($prihodi_po_uslugama)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">Prihodi po uslugama</h3>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Usluga</th>
                    <th>Kategorija</th>
                    <th>Broj termina</th>
                    <th>Ukupan prihod</th>
                    <th>Prosečan prihod</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prihodi_po_uslugama as $usluga): ?>
                <tr>
                    <td><?= htmlspecialchars($usluga['usluga']) ?></td>
                    <td><?= htmlspecialchars($usluga['kategorija'] ?? 'Bez kategorije') ?></td>
                    <td><?= $usluga['broj_termina'] ?></td>
                    <td style="font-weight: 600; color: #27ae60;"><?= number_format($usluga['ukupno'], 2) ?> KM</td>
                    <td><?= number_format($usluga['prosek'], 2) ?> KM</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Prihodi po terapeutima -->
    <?php if (!empty($prihodi_po_terapeutima)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">Prihodi po terapeutima</h3>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Terapeut</th>
                    <th>Broj termina</th>
                    <th>Ukupan prihod</th>
                    <th>Prosečan prihod</th>
                    <th>Učešće u ukupnom prihodu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prihodi_po_terapeutima as $terapeut): ?>
                <tr>
                    <td><?= htmlspecialchars($terapeut['terapeut']) ?></td>
                    <td><?= $terapeut['broj_termina'] ?></td>
                    <td style="font-weight: 600; color: #27ae60;"><?= number_format($terapeut['ukupno'], 2) ?> KM</td>
                    <td><?= number_format($terapeut['prosek'], 2) ?> KM</td>
                    <td>
                        <?php 
                        $procenat = $ukupni_prihodi['ukupno'] > 0 ? ($terapeut['ukupno'] / $ukupni_prihodi['ukupno']) * 100 : 0;
                        echo number_format($procenat, 1) . '%';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

<!-- Prodati paketi - NOVO -->
    <?php if (!empty($prodati_paketi)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #667eea, #764ba2); padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: white;">
                <i class="fa-solid fa-box"></i> Prodati paketi
            </h3>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Paket</th>
                    <th>Kategorija</th>
                    <th>Broj prodatih</th>
                    <th>Ukupan prihod</th>
                    <th>Prosečna cijena</th>
                    <th>Učešće u prihodu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prodati_paketi as $paket): ?>
                <tr>
                    <td>
                        <i class="fa-solid fa-box" style="color: #667eea; margin-right: 8px;"></i>
                        <?= htmlspecialchars($paket['paket']) ?>
                    </td>
                    <td><?= htmlspecialchars($paket['kategorija'] ?? 'Bez kategorije') ?></td>
                    <td style="font-weight: 600;"><?= $paket['broj_prodatih'] ?></td>
                    <td style="font-weight: 600; color: #27ae60;"><?= number_format($paket['ukupno'], 2) ?> KM</td>
                    <td><?= number_format($paket['prosek'], 2) ?> KM</td>
                    <td>
                        <?php 
                        $procenat = $ukupni_prihodi['ukupno'] > 0 ? ($paket['ukupno'] / $ukupni_prihodi['ukupno']) * 100 : 0;
                        ?>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="background: #ecf0f1; height: 8px; width: 100px; border-radius: 4px; overflow: hidden;">
                                <div style="background: #667eea; height: 100%; width: <?= $procenat ?>%; border-radius: 4px;"></div>
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
    customDates.style.display = period === 'custom' ? 'block' : 'none';
}
</script>