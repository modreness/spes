<div class="main-content-fw">
    <!-- Uvodni naslov -->
    <div style="margin-bottom: 30px;">
        <h2 style="color: #2c3e50; margin: 0;">Admin Dashboard</h2>
        <p style="color: #7f8c8d; margin: 5px 0 0 0;">Kompletni pregled klinike i upravljanje sistemom</p>
    </div>

    <!-- Statisticki kartoni -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #2980b9);">
            <h3>Ukupno pacijenata</h3>
            <div class="stat-number"><?= $dashboard_data['ukupno_pacijenata'] ?? 0 ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
            <h3>Aktivnih terapeuta</h3>
            <div class="stat-number"><?= $dashboard_data['ukupno_terapeuta'] ?? 0 ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
            <h3>Termini danas</h3>
            <div class="stat-number"><?= $dashboard_data['termini_danas'] ?? 0 ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
            <h3>Prihod danas</h3>
            <div class="stat-number"><?= number_format($dashboard_data['prihod_danas'] ?? 0, 2) ?> KM</div>
        </div>
    </div>

    <!-- Mesečni pregled -->
    <div class="main-content" style="margin-bottom: 30px;">
        <h3 style="margin: 0 0 20px 0; color: #2c3e50;">Pregled ovog meseca</h3>
        <div class="stats-grid" style="margin-bottom: 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                <h3>Ukupan prihod</h3>
                <div class="stat-number"><?= number_format($dashboard_data['prihod_mesec'] ?? 0, 2) ?> KM</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #34495e, #2c3e50);">
                <h3>Novi kartoni danas</h3>
                <div class="stat-number"><?= $dashboard_data['novi_kartoni_danas'] ?? 0 ?></div>
            </div>
            <?php if (!empty($dashboard_data['top_terapeut'])): ?>
            <div class="stat-card" style="background: linear-gradient(135deg, #16a085, #1abc9c);">
                <h3>Top terapeut - <?= htmlspecialchars($dashboard_data['top_terapeut']['ime']) ?></h3>
                <div class="stat-number"><?= $dashboard_data['top_terapeut']['broj_termina'] ?> termina</div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Glavne akcije -->
    <div class="action-cards">
        <div class="action-card">
            <h3>Upravljanje korisnicima</h3>
            <p>Dodaj novi profil, uredi postojeće korisnike ili promeni uloge</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="/profil/kreiraj" class="btn btn-add">
                    <i class="fa-solid fa-user-plus"></i> Novi korisnik
                </a>
                <a href="/profil/admin" class="btn btn-secondary">
                    <i class="fa-solid fa-users"></i> Svi korisnici
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>Termini i raspored</h3>
            <p>Pregled svih termina i upravljanje rasporedom terapeuta</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="/termini" class="submit-button btn-no-margin">
                    <i class="fa-solid fa-calendar"></i> Termini
                </a>
                <a href="/raspored" class="btn btn-secondary">
                    <i class="fa-solid fa-clock"></i> Raspored
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>Izvještaji i analiza</h3>
            <p>Detaljni finansijski i operativni izvještaji klinike</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="/izvjestaji" class="submit-button btn-no-margin">
                    <i class="fa-solid fa-chart-line"></i> Izvještaji
                </a>
                <a href="/izvjestaji/medicinski" class="btn btn-secondary">
                    <i class="fa-solid fa-file-medical"></i> Medicinski
                </a>
            </div>
        </div>
        
        <div class="action-card">
            <h3>Konfiguracija sistema</h3>
            <p>Cjenovnik, kategorije usluga i sistemske postavke</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="/cjenovnik" class="btn btn-edit">
                    <i class="fa-solid fa-money-bill"></i> Cjenovnik
                </a>
                <a href="/kategorije" class="btn btn-secondary">
                    <i class="fa-solid fa-tags"></i> Kategorije
                </a>
            </div>
        </div>
    </div>

    <!-- Pregled aktivnosti -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 30px;">
        <!-- Predstojeci termini -->
        <div class="main-content">
            <h3 style="margin: 0 0 20px 0; color: #2c3e50;">Predstojeci termini danas</h3>
            
            <?php if (empty($dashboard_data['predstojeci_termini'])): ?>
                <div style="text-align: center; color: #7f8c8d; padding: 40px;">
                    <i class="fa-solid fa-calendar-xmark" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>Nema zakazanih termina danas</p>
                </div>
            <?php else: ?>
                <table class="table-standard">
                    <thead>
                        <tr>
                            <th>Pacijent</th>
                            <th>Usluga</th>
                            <th>Terapeut</th>
                            <th>Vrijeme</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard_data['predstojeci_termini'] as $termin): ?>
                        <tr>
                            <td style="font-weight: 600;">
                                <?= htmlspecialchars($termin['pacijent_ime']) ?>
                            </td>
                            <td><?= htmlspecialchars($termin['usluga']) ?></td>
                            <td><?= htmlspecialchars($termin['terapeut_ime']) ?></td>
                            <td style="font-weight: 600; color: #255AA5;">
                                <?= date('H:i', strtotime($termin['vrijeme'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="bottom-foot">
                    <a href="/termini" class="btn btn-secondary btn-no-margin">Svi termini</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Nedavni kartoni -->
        <div class="main-content">
            <h3 style="margin: 0 0 20px 0; color: #2c3e50;">Nedavno kreirani kartoni</h3>
            
            <?php if (empty($dashboard_data['nedavni_kartoni'])): ?>
                <div style="text-align: center; color: #7f8c8d; padding: 40px;">
                    <i class="fa-solid fa-folder-open" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>Nema novih kartona</p>
                </div>
            <?php else: ?>
                <table class="table-standard">
                    <thead>
                        <tr>
                            <th>Pacijent</th>
                            <th>Dijagnoza</th>
                            <th>Terapeut</th>
                            <th>Datum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard_data['nedavni_kartoni'] as $karton): ?>
                        <tr>
                            <td style="font-weight: 600;">
                                <?= htmlspecialchars($karton['pacijent_ime']) ?>
                            </td>
                            <td><?= htmlspecialchars($karton['dijagnoza']) ?></td>
                            <td><?= htmlspecialchars($karton['terapeut_ime']) ?></td>
                            <td><?= date('d.m.Y', strtotime($karton['datum_kreiranja'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="bottom-foot">
                    <a href="/kartoni/lista" class="btn btn-secondary btn-no-margin">Svi kartoni</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Brza navigacija -->
    <div class="main-content" style="margin-top: 30px;">
        <h3 style="margin: 0 0 20px 0; color: #2c3e50;">Brza navigacija</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <a href="/kartoni/lista" class="openlink" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fa-solid fa-folder-open" style="color: #255AA5;"></i>
                <span>Kartoni pacijenata</span>
            </a>
            <a href="/kategorije" class="openlink" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fa-solid fa-tags" style="color: #255AA5;"></i>
                <span>Kategorije usluga</span>
            </a>
            <a href="/cjenovnik" class="openlink" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fa-solid fa-money-bill" style="color: #255AA5;"></i>
                <span>Cjenovnik</span>
            </a>
            <a href="/timetable" class="openlink" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fa-solid fa-business-time" style="color: #255AA5;"></i>
                <span>Radna vremena</span>
            </a>
            <a href="/backup" class="openlink" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fa-solid fa-database" style="color: #255AA5;"></i>
                <span>Backup sistema</span>
            </a>
            <a href="/logs" class="openlink" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fa-solid fa-file-lines" style="color: #255AA5;"></i>
                <span>System logs</span>
            </a>
        </div>
    </div>
</div>