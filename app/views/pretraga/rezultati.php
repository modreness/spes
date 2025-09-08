<div class="naslov-dugme">
    <h2>Pretraga sistema</h2>
</div>

<div class="main-content-fw">
    <!-- Forma za pretragu -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <form method="get" action="/pretraga">
            <div style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 300px;">
                    <label for="q">Pretražite pacijente, kartone, termine...</label>
                    <input type="text" id="q" name="q" value="<?= htmlspecialchars($query) ?>" 
                           placeholder="Unesite ime, prezime, JMBG, broj kartona..." 
                           style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px;">
                </div>
                
                <div class="form-group">
                    <label for="tip">Tip pretrage</label>
                    <select id="tip" name="tip" style="padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        <option value="sve" <?= $tip === 'sve' ? 'selected' : '' ?>>Sve</option>
                        <option value="pacijenti" <?= $tip === 'pacijenti' ? 'selected' : '' ?>>Pacijenti</option>
                        <option value="kartoni" <?= $tip === 'kartoni' ? 'selected' : '' ?>>Kartoni</option>
                        <option value="termini" <?= $tip === 'termini' ? 'selected' : '' ?>>Termini</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 12px 25px;">
                    <i class="fa-solid fa-search"></i> Pretraži
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($query)): ?>
        <?php if (strlen($query) < 2): ?>
            <div class="alert alert-warning">Unesite najmanje 2 karaktera za pretragu.</div>
        <?php else: ?>
            
            <!-- Rezultati pretrage -->
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h4 style="margin: 0; color: #2c3e50;">
                    Rezultati pretrage za: "<strong><?= htmlspecialchars($query) ?></strong>"
                </h4>
                <p style="margin: 5px 0 0 0; color: #7f8c8d;">
                    Pronađeno: <?= count($pacijenti) ?> pacijenata, <?= count($kartoni) ?> kartona, <?= count($termini) ?> termina
                </p>
            </div>

            <!-- Pacijenti -->
            <?php if (!empty($pacijenti)): ?>
            <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
                <div style="background: #3498db; color: white; padding: 15px;">
                    <h3 style="margin: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-users" style="margin-right: 10px;"></i>
                        Pacijenti (<?= count($pacijenti) ?>)
                    </h3>
                </div>
                <div style="padding: 0;">
                    <?php foreach ($pacijenti as $p): ?>
                    <div style="padding: 15px 20px; border-bottom: 1px solid #f8f9fa; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: #2c3e50;"><?= htmlspecialchars($p['ime'] . ' ' . $p['prezime']) ?></strong>
                            <div style="color: #7f8c8d; font-size: 14px; margin-top: 2px;">
                                <?= htmlspecialchars($p['email']) ?> • 
                                Kreiran: <?= date('d.m.Y', strtotime($p['datum_kreiranja'])) ?>
                            </div>
                        </div>
                        <div>
                            <span style="background: <?= $p['aktivan'] ? '#27ae60' : '#e74c3c' ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-right: 10px;">
                                <?= $p['aktivan'] ? 'Aktivan' : 'Neaktivan' ?>
                            </span>
                            <a href="/profil/<?= $p['uloga'] ?>?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Profil</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Kartoni -->
            <?php if (!empty($kartoni)): ?>
            <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
                <div style="background: #27ae60; color: white; padding: 15px;">
                    <h3 style="margin: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-folder-open" style="margin-right: 10px;"></i>
                        Kartoni (<?= count($kartoni) ?>)
                    </h3>
                </div>
                <div style="padding: 0;">
                    <?php foreach ($kartoni as $k): ?>
                    <div style="padding: 15px 20px; border-bottom: 1px solid #f8f9fa; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: #2c3e50;">Karton #<?= htmlspecialchars($k['broj_upisa']) ?></strong>
                            <div style="color: #7f8c8d; font-size: 14px; margin-top: 2px;">
                                Pacijent: <?= htmlspecialchars($k['pacijent_ime']) ?> • 
                                JMBG: <?= htmlspecialchars($k['jmbg']) ?> •
                                Otvoren: <?= date('d.m.Y', strtotime($k['datum_otvaranja'])) ?>
                            </div>
                        </div>
                        <a href="/kartoni/pregled?id=<?= $k['id'] ?>" class="btn btn-sm btn-primary">Otvori</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Termini -->
            <?php if (!empty($termini)): ?>
            <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px; overflow: hidden;">
                <div style="background: #f39c12; color: white; padding: 15px;">
                    <h3 style="margin: 0; display: flex; align-items: center;">
                        <i class="fa-solid fa-calendar-check" style="margin-right: 10px;"></i>
                        Termini (<?= count($termini) ?>)
                    </h3>
                </div>
                <div style="padding: 0;">
                    <?php foreach ($termini as $t): ?>
                        <?php 
                        $status_colors = [
                            'zakazan' => '#27ae60',
                            'otkazan' => '#e74c3c',
                            'obavljen' => '#95a5a6',
                            'slobodan' => '#f39c12'
                        ];
                        ?>
                    <div style="padding: 15px 20px; border-bottom: 1px solid #f8f9fa; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: #2c3e50;"><?= htmlspecialchars($t['pacijent_ime']) ?></strong>
                            <div style="color: #7f8c8d; font-size: 14px; margin-top: 2px;">
                                <?= date('d.m.Y H:i', strtotime($t['datum_vrijeme'])) ?> • 
                                Terapeut: <?= htmlspecialchars($t['terapeut_ime']) ?> •
                                Usluga: <?= htmlspecialchars($t['usluga_naziv']) ?>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="background: <?= $status_colors[$t['status']] ?? '#95a5a6' ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                <?= ucfirst($t['status']) ?>
                            </span>
                            <a href="/termini/uredi?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">Uredi</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($pacijenti) && empty($kartoni) && empty($termini)): ?>
                <div style="background: #fff; padding: 50px; text-align: center; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <i class="fa-solid fa-search" style="font-size: 48px; color: #bdc3c7; margin-bottom: 20px;"></i>
                    <h3 style="color: #7f8c8d; margin: 0;">Nema rezultata</h3>
                    <p style="color: #95a5a6; margin: 10px 0 0 0;">Pokušajte sa drugim terminima pretrage.</p>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    <?php else: ?>
        <!-- Početni ekran -->
        <div style="background: #fff; padding: 50px; text-align: center; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
            <i class="fa-solid fa-search" style="font-size: 64px; color: #3498db; margin-bottom: 20px;"></i>
            <h3 style="color: #2c3e50; margin: 0 0 15px 0;">Pretražite sistem</h3>
            <p style="color: #7f8c8d; margin: 0;">Pronađite pacijente, kartone ili termine koristeći pretragu iznad.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-focus na search input
document.getElementById('q').focus();

// Enter za submit
document.getElementById('q').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});
</script>