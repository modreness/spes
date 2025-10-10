<div class="naslov-dugme">
    <h2>Zakazani termini</h2>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'kreiran'): ?>
    <div class="alert alert-success">Termin je uspješno kreiran.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-warning">Greška pri kreiranju termina.</div>
<?php endif; ?>

<div class="main-content-fw">
    <!-- Statistike -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #255AA5);">
            <h3>Termini danas</h3>
            <div class="stat-number"><?= $termini_danas ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #289CC6);">
            <h3>Termini ove sedmice</h3>
            <div class="stat-number"><?= $termini_sedmica ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #289CC6);">
            <h3>Ukupno aktivnih</h3>
            <div class="stat-number"><?= $ukupno_termina ?></div>
        </div>
    </div>

    <!-- Akcije -->
    <div class="action-cards">
        <div class="action-card">
            <h3>Kalendar termina</h3>
            <p>Kalendarski prikaz termina</p>
            <a href="/termini/kalendar" class="btn btn-add">
                <i class="fa-solid fa-calendar-days"></i> Otvori kalendar
            </a>
        </div>
        
        <div class="action-card">
            <h3>Novi termin</h3>
            <p>Zakaži novi termin za pacijenta</p>
            <a href="/termini/kreiraj" class="btn btn-add">
                <i class="fa-solid fa-plus"></i> Kreiraj termin
            </a>
        </div>
        <div class="action-card">
            <h3>Lista termina</h3>
            <p>Tabelarni prikaz sa filterima</p>
            <a href="/termini/lista" class="btn btn-add">
                <i class="fa-solid fa-list"></i> Lista termina
            </a>
        </div>
    </div>

    <!-- Najnoviji termini -->
    <?php if (!empty($najnoviji_termini)): ?>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-top: 30px; overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">
                <i class="fa-solid fa-clock" style="margin-right: 10px; color: #3498db;"></i>
                Najnoviji zakazani termini
            </h3>
        </div>
        
        <div style="padding: 0;">
            <?php foreach ($najnoviji_termini as $termin): ?>
            <div style="padding: 15px 20px; border-bottom: 1px solid #f8f9fa; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #2c3e50;"><?= htmlspecialchars($termin['pacijent_ime']) ?></strong>
                    <div style="color: #7f8c8d; font-size: 14px; margin-top: 2px;">
                        <?= htmlspecialchars($termin['usluga_naziv']) ?> • 
                        <?= htmlspecialchars($termin['terapeut_ime']) ?>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="color: #3498db; font-weight: 500;">
                        <?= date('d.m.Y', strtotime($termin['datum_vrijeme'])) ?>
                    </div>
                    <div style="color: #7f8c8d; font-size: 14px;">
                        <?= date('H:i', strtotime($termin['datum_vrijeme'])) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>