<div class="naslov-dugme">
    <h2>Izvještaji i statistike</h2>
</div>

<div class="main-content-fw">
    <!-- Brze statistike -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(90deg, #255AA5, #255AA5);">
            <h3>Prihod danas</h3>
            <div class="stat-number"><?= number_format($prihod_danas, 2) ?> KM</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(90deg, #255AA5, #255AA5);">
            <h3>Prihod ovaj mjesec</h3>
            <div class="stat-number"><?= number_format($ukupan_prihod_mesec, 2) ?> KM</div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">
                Termini: <?= number_format($prihod_mesec, 2) ?> KM | Paketi: <?= number_format($prihod_paketi_mesec, 2) ?> KM
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(90deg, #255AA5, #255AA5);">
            <h3>Termini ovaj mjesec</h3>
            <div class="stat-number"><?= $termini_mesec ?></div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">
                <?= $termini_iz_paketa_mesec ?> iz paketa
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(90deg, #289CC6, #289CC6);">
            <h3>Prodato paketa (mjesec)</h3>
            <div class="stat-number"><?= $paketi_prodati_mesec ?></div>
        </div>
         <!--
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #255AA5);">
            <h3>Prihod od paketa (mjesec)</h3>
            <div class="stat-number"><?= number_format($prihod_paketi_mesec, 2) ?> KM</div>
        </div>
       -->
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #255AA5);">
            <h3>Top terapeut</h3>
            <div class="stat-number" style="font-size: 18px;">
                <?= $top_terapeut ? htmlspecialchars($top_terapeut['ime']) : 'N/A' ?>
            </div>
            <?php if ($top_terapeut): ?>
                <div style="font-size: 14px; opacity: 0.9; margin-top: 5px;">
                    <?= $top_terapeut['broj_termina'] ?> termina
                </div>
            <?php endif; ?>
        </div>
            
        
        
    </div>

    <!-- Tipovi izvještaja -->
    <div class="action-cards">
        <div class="action-card">
            <h3>Finansijski izvještaji</h3>
            <p>Prihodi, troškovi i finansijske analize po periodima</p>
            <a href="/izvjestaji/finansijski" class="btn btn-add">
                <i class="fa-solid fa-chart-line"></i> Finansijski
            </a>
        </div>
        
        <div class="action-card">
            <h3>Operativni izvještaji</h3>
            <p>Statistike termina, terapeuta i radnih pokazatelja</p>
            <a href="/izvjestaji/operativni" class="btn btn-add">
                <i class="fa-solid fa-chart-bar"></i> Operativni
            </a>
        </div>
        
        <div class="action-card">
            <h3>Medicinski izvještaji</h3>
            <p>Statistike tretmana, dijagnoza i napretka pacijenata</p>
            <a href="/izvjestaji/medicinski" class="btn btn-add">
                <i class="fa-solid fa-notes-medical"></i> Medicinski
            </a>
        </div>
    </div>

    <!-- Brzi pregled - poslednji podaci -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-top: 30px; overflow: hidden;">
        <div style="background: #c2c5c9; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #666666;">
                <i class="fa-solid fa-clock" style="margin-right: 10px; color: #666666;"></i>
                Brzi pregled - posljednjih 7 dana
            </h3>
        </div>
        
        <div style="padding: 20px;">
            <?php
            // Brze statistike za poslednih 7 dana
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE(t.datum_vrijeme) as dan,
                        COUNT(*) as broj_termina,
                        SUM(CASE WHEN t.status = 'obavljen' THEN c.cijena ELSE 0 END) as prihod
                    FROM termini t
                    LEFT JOIN cjenovnik c ON t.usluga_id = c.id
                    WHERE DATE(t.datum_vrijeme) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY DATE(t.datum_vrijeme)
                    ORDER BY dan DESC
                ");
                $stmt->execute();
                $poslednji_dani = $stmt->fetchAll();
            } catch (PDOException $e) {
                $poslednji_dani = [];
            }
            ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                <?php foreach ($poslednji_dani as $dan): ?>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-weight: 600; color: #255AA5; margin-bottom: 5px;">
                        <?= date('d.m', strtotime($dan['dan'])) ?>
                    </div>
                    <div style="color: #27ae60; font-size: 14px; margin-bottom: 2px;">
                        <?= number_format($dan['prihod'], 0) ?> KM
                    </div>
                    <div style="color: #7f8c8d; font-size: 12px;">
                        <?= $dan['broj_termina'] ?> termina
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>