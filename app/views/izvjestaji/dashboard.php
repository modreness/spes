<div class="naslov-dugme">
    <h2>Izvještaji i statistike</h2>
</div>

<div class="main-content">
    <!-- Brze statistike - Opšte -->
    <div style="margin-bottom: 20px;">
        <h3 style="color: #2c3e50; margin-bottom: 15px;">
            <i class="fa-solid fa-chart-line" style="color: #3498db;"></i> Opšte statistike
        </h3>
    </div>
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
            <h3>Prihod danas</h3>
            <div class="stat-number"><?= number_format($prihod_danas, 2) ?> KM</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #5dade2);">
            <h3>Ukupan prihod ovaj mesec</h3>
            <div class="stat-number"><?= number_format($ukupan_prihod_mesec, 2) ?> KM</div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">
                Termini: <?= number_format($prihod_mesec, 2) ?> KM | 
                Paketi: <?= number_format($prihod_paketi_mesec, 2) ?> KM
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #f4d03f);">
            <h3>Termini ovaj mesec</h3>
            <div class="stat-number"><?= $termini_mesec ?></div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">
                <?= $termini_iz_paketa_mesec ?> iz paketa
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #ec7063);">
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

    <!-- Statistike paketa -->
    <div style="margin: 30px 0 20px 0;">
        <h3 style="color: #2c3e50; margin-bottom: 15px;">
            <i class="fa-solid fa-box" style="color: #9b59b6;"></i> Statistike paketa
        </h3>
    </div>
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #9b59b6, #be7fd3);">
            <h3>Aktivni paketi</h3>
            <div class="stat-number"><?= $aktivni_paketi ?></div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">
                Trenutno u upotrebi
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #1abc9c, #48c9b0);">
            <h3>Prodato paketa (mesec)</h3>
            <div class="stat-number"><?= $paketi_prodati_mesec ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #16a085, #1abc9c);">
            <h3>Prihod od paketa (mesec)</h3>
            <div class="stat-number"><?= number_format($prihod_paketi_mesec, 2) ?> KM</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #8e44ad, #a569bd);">
            <h3>Najkorišteniji paket</h3>
            <div class="stat-number" style="font-size: 16px;">
                <?= $top_paket ? htmlspecialchars($top_paket['naziv']) : 'N/A' ?>
            </div>
            <?php if ($top_paket): ?>
                <div style="font-size: 14px; opacity: 0.9; margin-top: 5px;">
                    <?= $top_paket['broj_prodaja'] ?> prodaja
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tipovi izvještaja -->
    <div style="margin-top: 40px;">
        <h3 style="color: #2c3e50; margin-bottom: 15px;">
            <i class="fa-solid fa-file-alt" style="color: #34495e;"></i> Detaljni izvještaji
        </h3>
    </div>
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
            <a href="/izvjestaji/operativni" class="btn btn-primary">
                <i class="fa-solid fa-chart-bar"></i> Operativni
            </a>
        </div>
        
        <div class="action-card">
            <h3>Medicinski izvještaji</h3>
            <p>Statistike tretmana, dijagnoza i napretka pacijenata</p>
            <a href="/izvjestaji/medicinski" class="btn btn-success">
                <i class="fa-solid fa-notes-medical"></i> Medicinski
            </a>
        </div>
    </div>

    <!-- Brzi pregled - poslednji podaci -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-top: 30px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">
                <i class="fa-solid fa-clock" style="margin-right: 10px; color: #3498db;"></i>
                Brzi pregled - poslednja 7 dana
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
                    <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
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