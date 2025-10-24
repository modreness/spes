<div class="naslov-dugme">
    <h2>Moji izvještaji</h2>
    <div>
        <button onclick="window.print()" class="btn btn-print">
            <i class="fa-solid fa-print"></i> Štampaj izvještaj
        </button>
        <a href="/dashboard" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
    </div>
</div>

<div class="main-content-fw">
    <!-- Osnovne statistike -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #255AA5);">
            <h3>Ukupno termina</h3>
            <div class="stat-number"><?= $ukupno_termina ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #255AA5, #289CC6);">
            <h3>Ukupno tretmana</h3>
            <div class="stat-number"><?= $ukupno_tretmana ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #289CC6);">
            <h3>Broj pacijenata</h3>
            <div class="stat-number"><?= $broj_pacijenata ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #289CC6, #289CC6);">
            <h3>Tretmani ovaj mjesec</h3>
            <div class="stat-number"><?= $tretmani_ovaj_mjesec ?></div>
        </div>
    </div>

    <!-- Grafikoni -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin: 25px 0;">
        <!-- Mesečne statistike -->
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
            <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
                <h3 style="margin: 0; color: #2c3e50;">
                    <i class="fa-solid fa-chart-area"></i> Aktivnost posljednih 6 mjeseci
                </h3>
            </div>
            <div style="padding: 20px;">
                <canvas id="mjesecniGrafik" width="100%" height="200"></canvas>
            </div>
        </div>
        
        <!-- Dnevne statistike -->
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
            <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
                <h3 style="margin: 0; color: #2c3e50;">
                    <i class="fa-solid fa-chart-bar"></i> Aktivnost posljednih 30 dana
                </h3>
            </div>
            <div style="padding: 20px;">
                <canvas id="dnevniGrafik" width="100%" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabele sa podacima -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
        <!-- Najčešće usluge -->
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
            <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
                <h3 style="margin: 0; color: #2c3e50;">
                    <i class="fa-solid fa-list-alt"></i> Najčešće usluge koje radim
                </h3>
            </div>
            <div style="padding: 20px;">
                <?php if (empty($top_usluge)): ?>
                    <div style="text-align: center; color: #7f8c8d; padding: 20px;">
                        <i class="fa-solid fa-info-circle" style="font-size: 24px; opacity: 0.3; margin-bottom: 10px;"></i>
                        <p style="margin: 0;">Nema podataka o uslugama.</p>
                    </div>
                <?php else: ?>
                    <table class="table-standard">
                        <thead>
                            <tr>
                                <th>Usluga</th>
                                <th>Broj termina</th>
                                <th>Procenat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $ukupno_usluga = array_sum(array_column($top_usluge, 'broj_termina'));
                            foreach ($top_usluge as $usluga): 
                                $procenat = $ukupno_usluga > 0 ? round(($usluga['broj_termina'] / $ukupno_usluga) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($usluga['naziv']) ?></td>
                                <td>
                                    <span style="background: #255AA5; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                        <?= $usluga['broj_termina'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="background: #f8f9fa; border-radius: 10px; overflow: hidden; position: relative; height: 20px;">
                                        <div style="background: linear-gradient(90deg, #255AA5, #289CC6); width: <?= $procenat ?>%; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
                                            <?= $procenat ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Top pacijenti -->
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
            <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
                <h3 style="margin: 0; color: #2c3e50;">
                    <i class="fa-solid fa-users"></i> Pacijenti sa kojima najčešće radim
                </h3>
            </div>
            <div style="padding: 20px;">
                <?php if (empty($top_pacijenti)): ?>
                    <div style="text-align: center; color: #7f8c8d; padding: 20px;">
                        <i class="fa-solid fa-info-circle" style="font-size: 24px; opacity: 0.3; margin-bottom: 10px;"></i>
                        <p style="margin: 0;">Nema podataka o pacijentima.</p>
                    </div>
                <?php else: ?>
                    <table class="table-standard">
                        <thead>
                            <tr>
                                <th>Pacijent</th>
                                <th>Termini</th>
                                <th>Tretmani</th>
                                <th>Posljednja aktivnost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($top_pacijenti, 0, 10) as $pacijent): ?>
                            <tr>
                                <td><?= htmlspecialchars($pacijent['pacijent_ime']) ?></td>
                                <td>
                                    <span style="background: #255AA5; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                        <?= $pacijent['broj_termina'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="background: #27ae60; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                        <?= $pacijent['broj_tretmana'] ?>
                                    </span>
                                </td>
                                <td>
                                    <small style="color: #7f8c8d;">
                                        <?php if ($pacijent['poslednja_aktivnost']): ?>
                                            <?= date('d.m.Y', strtotime($pacijent['poslednja_aktivnost'])) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mesečni pregled -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; margin-top: 25px;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; color: #2c3e50;">
                <i class="fa-solid fa-calendar-alt"></i> Detaljne mjesečne statistike
            </h3>
        </div>
        <div style="padding: 20px;">
            <table class="table-standard">
                <thead>
                    <tr>
                        <th>Mjesec</th>
                        <th>Broj termina</th>
                        <th>Broj tretmana</th>
                        <th>Razlika</th>
                        <th>Trend</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mjesecne_statistike as $index => $stat): ?>
                    <tr>
                        <td><strong><?= $stat['mjesec'] ?></strong></td>
                        <td>
                            <span style="background: #255AA5; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= $stat['termini'] ?>
                            </span>
                        </td>
                        <td>
                            <span style="background: #289CC6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= $stat['tretmani'] ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $razlika = $stat['termini'] - $stat['tretmani'];
                            $boja = $razlika == 0 ? '#95a5a6' : ($razlika > 0 ? '#27ae60' : '#e74c3c');
                            ?>
                            <span style="background: <?= $boja ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= $razlika > 0 ? '+' : '' ?><?= $razlika ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($index > 0): ?>
                                <?php 
                                $prethodni = $mjesecne_statistike[$index - 1]['termini'];
                                $trenutni = $stat['termini'];
                                if ($trenutni > $prethodni): ?>
                                    <i class="fa-solid fa-arrow-up" style="color: #27ae60;"></i>
                                <?php elseif ($trenutni < $prethodni): ?>
                                    <i class="fa-solid fa-arrow-down" style="color: #e74c3c;"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-minus" style="color: #95a5a6;"></i>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #bdc3c7;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

   
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Mjesečni grafik
const mjesecniCtx = document.getElementById('mjesecniGrafik');
const mjesecniData = <?= json_encode($mjesecne_statistike) ?>;

new Chart(mjesecniCtx, {
    type: 'line',
    data: {
        labels: mjesecniData.map(item => item.mjesec),
        datasets: [{
            label: 'Termini',
            data: mjesecniData.map(item => item.termini),
            borderColor: '#255AA5',
            backgroundColor: 'rgba(37, 90, 165, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Tretmani',
            data: mjesecniData.map(item => item.tretmani),
            borderColor: '#27ae60',
            backgroundColor: 'rgba(39, 174, 96, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f8f9fa'
                }
            },
            x: {
                grid: {
                    color: '#f8f9fa'
                }
            }
        },
        plugins: {
            legend: {
                position: 'top'
            }
        }
    }
});

// Dnevni grafik
const dnevniCtx = document.getElementById('dnevniGrafik');
const dnevniData = <?= json_encode($dnevne_statistike) ?>;

new Chart(dnevniCtx, {
    type: 'bar',
    data: {
        labels: dnevniData.map(item => item.dan),
        datasets: [{
            label: 'Termini po danima',
            data: dnevniData.map(item => item.termini),
            backgroundColor: 'rgba(37, 90, 165, 0.8)',
            borderColor: '#255AA5',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                },
                grid: {
                    color: '#f8f9fa'
                }
            },
            x: {
                grid: {
                    color: '#f8f9fa'
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>