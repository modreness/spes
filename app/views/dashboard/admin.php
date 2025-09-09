<section class="cards">
    <div class="card">
        <a href="/profil/admin" style="text-decoration: none; color: inherit;">
            Pogledaj profile
        </a>
    </div>
    <div class="card">
        Današnji broj termina<br>
        <strong><?= $termini_danas ?></strong><br>
        <small><?= date('j. F Y') ?></small>
    </div>
    <div class="card">
        <a href="/kartoni/lista" style="text-decoration: none; color: inherit;">
            Pogledaj kartone
        </a>
    </div>
    <div class="card backup">
        Prihod danas<br>
        <strong><?= number_format($prihod_danas, 0) ?> KM</strong>
    </div>
</section>

<section class="layout-grid">
    <div class="termini">
        <h3>Nadolazeći termini</h3>
        <?php
        // Dohvati današnje termine
        try {
            $stmt = $pdo->prepare("
                SELECT t.*, 
                       CONCAT(u_pacijent.ime, ' ', u_pacijent.prezime) as pacijent,
                       CONCAT(u_terapeut.ime, ' ', u_terapeut.prezime) as terapeut,
                       c.naziv as usluga
                FROM termini t
                LEFT JOIN users u_pacijent ON t.pacijent_id = u_pacijent.id
                LEFT JOIN users u_terapeut ON t.terapeut_id = u_terapeut.id
                LEFT JOIN cjenovnik c ON t.usluga_id = c.id
                WHERE DATE(t.datum_vrijeme) = CURDATE() 
                AND t.status = 'zakazan'
                ORDER BY t.datum_vrijeme ASC
                LIMIT 10
            ");
            $stmt->execute();
            $dagens_termini = $stmt->fetchAll();
        } catch (PDOException $e) {
            $dagens_termini = [];
        }
        ?>
        
        <?php if (!empty($dagens_termini)): ?>
            <?php foreach ($dagens_termini as $termin): ?>
            <div style="padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 8px;">
                <strong><?= date('H:i', strtotime($termin['datum_vrijeme'])) ?></strong> - 
                <?= htmlspecialchars($termin['pacijent']) ?><br>
                <small><?= htmlspecialchars($termin['terapeut']) ?> • <?= htmlspecialchars($termin['usluga']) ?></small>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nema zakazanih termina danas.</p>
        <?php endif; ?>
    </div>
    
    <div class="kalendar">
        <h3>Brze akcije</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="/termini/kreiraj" class="btn btn-add">Novi termin</a>
            <a href="/izvjestaji" class="btn btn-primary">Izvještaji</a>
            <a href="/pretraga" class="btn btn-secondary">Pretraga</a>
        </div>
    </div>
</section>

<section class="raspored">
    <h3>Aktivni terapeuti (<?= $aktivni_terapeuti ?>)</h3>
    <p><?= date('j. F Y') ?></p>
    
    <?php
    try {
        $stmt = $pdo->prepare("
            SELECT CONCAT(ime, ' ', prezime) as ime, 
                   COUNT(t.id) as termini_danas
            FROM users u
            LEFT JOIN termini t ON u.id = t.terapeut_id 
                AND DATE(t.datum_vrijeme) = CURDATE()
                AND t.status = 'zakazan'
            WHERE u.uloga = 'terapeut' AND u.aktivan = 1
            GROUP BY u.id, u.ime, u.prezime
            ORDER BY termini_danas DESC
        ");
        $stmt->execute();
        $terapeuti_danas = $stmt->fetchAll();
    } catch (PDOException $e) {
        $terapeuti_danas = [];
    }
    ?>
    
    <?php foreach ($terapeuti_danas as $terapeut): ?>
        <div style="display: inline-block; margin: 5px; padding: 8px 12px; background: #f0f0f0; border-radius: 6px;">
            <?= htmlspecialchars($terapeut['ime']) ?> 
            <span style="color: #666;">(<?= $terapeut['termini_danas'] ?>)</span>
        </div>
    <?php endforeach; ?>
</section>