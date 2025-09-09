<?php
// Dohvati admin statistike
try {
    // Osnovne brojke
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE uloga = 'pacijent'");
    $stmt->execute();
    $ukupno_pacijenata = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE uloga = 'terapeut'");
    $stmt->execute();
    $ukupno_terapeuta = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM termini WHERE DATE(datum_vrijeme) = CURDATE()");
    $stmt->execute();
    $termini_danas = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM kartoni WHERE DATE(datum_kreiranja) = CURDATE()");
    $stmt->execute();
    $novi_kartoni_danas = $stmt->fetchColumn();
    
    // Finansijski podaci
    $stmt = $pdo->prepare("
        SELECT SUM(c.cijena) 
        FROM termini t 
        JOIN cjenovnik c ON t.usluga_id = c.id 
        WHERE DATE(t.datum_vrijeme) = CURDATE() AND t.status = 'obavljen'
    ");
    $stmt->execute();
    $prihod_danas = $stmt->fetchColumn() ?: 0;
    
    $stmt = $pdo->prepare("
        SELECT SUM(c.cijena) 
        FROM termini t 
        JOIN cjenovnik c ON t.usluga_id = c.id 
        WHERE MONTH(t.datum_vrijeme) = MONTH(CURDATE()) 
        AND YEAR(t.datum_vrijeme) = YEAR(CURDATE()) 
        AND t.status = 'obavljen'
    ");
    $stmt->execute();
    $prihod_mesec = $stmt->fetchColumn() ?: 0;
    
    // Najaktivniji terapeut ovaj mesec
    $stmt = $pdo->prepare("
        SELECT CONCAT(u.ime, ' ', u.prezime) as ime, COUNT(*) as broj_termina
        FROM termini t
        JOIN users u ON t.terapeut_id = u.id
        WHERE MONTH(t.datum_vrijeme) = MONTH(CURDATE()) 
        AND YEAR(t.datum_vrijeme) = YEAR(CURDATE())
        AND t.status = 'obavljen'
        GROUP BY t.terapeut_id
        ORDER BY broj_termina DESC
        LIMIT 1
    ");
    $stmt->execute();
    $top_terapeut = $stmt->fetch();
    
    // Predstojeci termini danas
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
               CONCAT(te.ime, ' ', te.prezime) as terapeut_ime,
               c.naziv as usluga,
               TIME(t.datum_vrijeme) as vrijeme
        FROM termini t
        JOIN users p ON t.pacijent_id = p.id
        JOIN users te ON t.terapeut_id = te.id
        JOIN cjenovnik c ON t.usluga_id = c.id
        WHERE DATE(t.datum_vrijeme) = CURDATE()
        AND t.status = 'zakazan'
        ORDER BY t.datum_vrijeme ASC
        LIMIT 10
    ");
    $stmt->execute();
    $predstojeci_termini = $stmt->fetchAll();
    
    // Nedavni kartoni
    $stmt = $pdo->prepare("
        SELECT k.*, 
               CONCAT(p.ime, ' ', p.prezime) as pacijent_ime,
               CONCAT(t.ime, ' ', t.prezime) as terapeut_ime
        FROM kartoni k
        JOIN users p ON k.pacijent_id = p.id
        JOIN users t ON k.terapeut_id = t.id
        ORDER BY k.datum_kreiranja DESC
        LIMIT 5
    ");
    $stmt->execute();
    $nedavni_kartoni = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Greška pri dohvaćanju admin statistika: " . $e->getMessage());
    $ukupno_pacijenata = $ukupno_terapeuta = $termini_danas = $novi_kartoni_danas = 0;
    $prihod_danas = $prihod_mesec = 0;
    $top_terapeut = null;
    $predstojeci_termini = $nedavni_kartoni = [];
}
?>

<div class="admin-dashboard">
    <!-- Uvodni naslov -->
    <div style="margin-bottom: 30px;">
        <h2 style="color: #2c3e50; margin: 0;">Admin Dashboard</h2>
        <p style="color: #7f8c8d; margin: 5px 0 0 0;">Kompletni pregled klinike i upravljanje sistemom</p>
    </div>

    <!-- Statisticki kartoni -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #2980b9);">
            <div class="stat-content">
                <div class="stat-number"><?= $ukupno_pacijenata ?></div>
                <div class="stat-label">Ukupno pacijenata</div>
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
            <div class="stat-content">
                <div class="stat-number"><?= $ukupno_terapeuta ?></div>
                <div class="stat-label">Aktivnih terapeuta</div>
                <div class="stat-icon"><i class="fa-solid fa-user-doctor"></i></div>
            </div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
            <div class="stat-content">
                <div class="stat-number"><?= $termini_danas ?></div>
                <div class="stat-label">Termini danas</div>
                <div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            </div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
            <div class="stat-content">
                <div class="stat-number"><?= number_format($prihod_danas, 2) ?> KM</div>
                <div class="stat-label">Prihod danas</div>
                <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
            </div>
        </div>
    </div>

    <!-- Mesečni pregled -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 25px; margin-bottom: 30px;">
        <h3 style="margin: 0 0 20px 0; color: #2c3e50;">Pregled ovog meseca</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div class="monthly-stat">
                <div class="monthly-number"><?= number_format($prihod_mesec, 2) ?> KM</div>
                <div class="monthly-label">Ukupan prihod</div>
            </div>
            <div class="monthly-stat">
                <div class="monthly-number"><?= $novi_kartoni_danas ?></div>
                <div class="monthly-label">Novi kartoni danas</div>
            </div>
            <?php if ($top_terapeut): ?>
            <div class="monthly-stat">
                <div class="monthly-number"><?= $top_terapeut['broj_termina'] ?></div>
                <div class="monthly-label">Termina - <?= htmlspecialchars($top_terapeut['ime']) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Glavne akcije -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="action-card">
            <h3>Upravljanje korisnicima</h3>
            <p>Dodaj novi profil, uredi postojeće korisnike ili promeni uloge</p>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <a href="/profil/kreiraj" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-user-plus"></i> Novi korisnik
                </a>
                <a href="/profil/admin" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-users"></i> Svi korisnici
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>Termini i raspored</h3>
            <p>Pregled svih termina i upravljanje rasporedom terapeuta</p>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <a href="/termini" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-calendar"></i> Termini
                </a>
                <a href="/raspored" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-clock"></i> Raspored
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>Izvještaji i analiza</h3>
            <p>Detaljni finansijski i operativni izvještaji klinike</p>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <a href="/izvjestaji" class="btn btn-info btn-sm">
                    <i class="fa-solid fa-chart-line"></i> Izvještaji
                </a>
                <a href="/izvjestaji/medicinski" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-file-medical"></i> Medicinski
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>Konfiguracija sistema</h3>
            <p>Cjenovnik, kategorije usluga i sistemske postavke</p>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <a href="/cjenovnik" class="btn btn-warning btn-sm">
                    <i class="fa-solid fa-money-bill"></i> Cjenovnik
                </a>
                <a href="/kategorije" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-tags"></i> Kategorije
                </a>
            </div>
        </div>
    </div>

    <!-- Pregled aktivnosti -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
        <!-- Predstojeci termini -->
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
            <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
                <h3 style="margin: 0; color: #2c3e50;">Predstojeci termini danas</h3>
            </div>
            <div style="padding: 0;">
                <?php if (empty($predstojeci_termini)): ?>
                    <div style="padding: 20px; text-align: center; color: #7f8c8d;">
                        <i class="fa-solid fa-calendar-xmark" style="font-size: 2em; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p>Nema zakazanih termina danas</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($predstojeci_termini as $termin): ?>
                    <div style="padding: 15px 20px; border-bottom: 1px solid #f8f9fa; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600; color: #2c3e50;">
                                <?= htmlspecialchars($termin['pacijent_ime']) ?>
                            </div>
                            <div style="font-size: 0.9em; color: #7f8c8d;">
                                <?= htmlspecialchars($termin['usluga']) ?> - <?= htmlspecialchars($termin['terapeut_ime']) ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 600; color: #3498db;">
                                <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div style="padding: 15px 20px; text-align: center;">
                        <a href="/termini" class="btn btn-outline btn-sm">Svi termini</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nedavni kartoni -->
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
            <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
                <h3 style="margin: 0; color: #2c3e50;">Nedavno kreirani kartoni</h3>
            </div>
            <div style="padding: 0;">
                <?php if (empty($nedavni_kartoni)): ?>
                    <div style="padding: 20px; text-align: center; color: #7f8c8d;">
                        <i class="fa-solid fa-folder-open" style="font-size: 2em; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p>Nema novih kartona</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($nedavni_kartoni as $karton): ?>
                    <div style="padding: 15px 20px; border-bottom: 1px solid #f8f9fa;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <div style="font-weight: 600; color: #2c3e50;">
                                    <?= htmlspecialchars($karton['pacijent_ime']) ?>
                                </div>
                                <div style="font-size: 0.9em; color: #7f8c8d;">
                                    <?= htmlspecialchars($karton['dijagnoza']) ?>
                                </div>
                                <div style="font-size: 0.85em; color: #95a5a6;">
                                    Terapeut: <?= htmlspecialchars($karton['terapeut_ime']) ?>
                                </div>
                            </div>
                            <div style="font-size: 0.85em; color: #7f8c8d;">
                                <?= date('d.m.Y', strtotime($karton['datum_kreiranja'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div style="padding: 15px 20px; text-align: center;">
                        <a href="/kartoni/lista" class="btn btn-outline btn-sm">Svi kartoni</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Brza navigacija -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-top: 30px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">Brza navigacija</h3>
        </div>
        
        <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <a href="/kartoni/lista" class="quick-link">
                <i class="fa-solid fa-folder-open"></i>
                <span>Kartoni pacijenata</span>
            </a>
            <a href="/kategorije" class="quick-link">
                <i class="fa-solid fa-tags"></i>
                <span>Kategorije usluga</span>
            </a>
            <a href="/cjenovnik" class="quick-link">
                <i class="fa-solid fa-money-bill"></i>
                <span>Cjenovnik</span>
            </a>
            <a href="/timetable" class="quick-link">
                <i class="fa-solid fa-business-time"></i>
                <span>Radna vremena</span>
            </a>
            <a href="/backup" class="quick-link">
                <i class="fa-solid fa-database"></i>
                <span>Backup sistema</span>
            </a>
            <a href="/logs" class="quick-link">
                <i class="fa-solid fa-file-lines"></i>
                <span>System logs</span>
            </a>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    max-width: 1400px;
    margin: 0 auto;
}

.stat-card {
    border-radius: 12px;
    padding: 0;
    color: white;
    overflow: hidden;
    position: relative;
}

.stat-content {
    padding: 25px;
    position: relative;
    z-index: 2;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
    line-height: 1;
}

.stat-label {
    font-size: 1rem;
    opacity: 0.9;
    font-weight: 500;
}

.stat-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 2rem;
    opacity: 0.3;
}

.monthly-stat {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.monthly-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.monthly-label {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.action-card {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border-left: 4px solid #3498db;
}

.action-card h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.action-card p {
    color: #7f8c8d;
    margin: 0 0 15px 0;
    font-size: 0.95rem;
    line-height: 1.4;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.quick-link:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    border-color: #3498db;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
}

.quick-link i {
    color: #3498db;
    font-size: 1.2rem;
    width: 20px;
    text-align: center;
}

.quick-link span {
    font-weight: 500;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85rem;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-success {
    background: #2ecc71;
    color: white;
}

.btn-success:hover {
    background: #27ae60;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
}

.btn-warning {
    background: #f39c12;
    color: white;
}

.btn-warning:hover {
    background: #e67e22;
}

.btn-outline {
    background: transparent;
    color: #6c757d;
    border-color: #dee2e6;
}

.btn-outline:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
}
</style>