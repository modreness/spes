<div class="container-fluid px-4">
    <h1 class="mt-4">Moji izvještaji</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Moji izvještaji</li>
    </ol>
    
    <!-- Osnovne statistike -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Ukupno termina</div>
                            <div class="h5 mb-0"><?= $ukupno_termina ?></div>
                        </div>
                        <div><i class="fas fa-calendar-check fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Ukupno tretmana</div>
                            <div class="h5 mb-0"><?= $ukupno_tretmana ?></div>
                        </div>
                        <div><i class="fas fa-notes-medical fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Broj pacijenata</div>
                            <div class="h5 mb-0"><?= $broj_pacijenata ?></div>
                        </div>
                        <div><i class="fas fa-users fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Tretmani ovaj mesec</div>
                            <div class="h5 mb-0"><?= $tretmani_ovaj_mesec ?></div>
                        </div>
                        <div><i class="fas fa-calendar-month fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafikoni -->
    <div class="row">
        <!-- Mesečne statistike -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-area me-1"></i>
                    Aktivnost poslednih 6 meseci
                </div>
                <div class="card-body">
                    <canvas id="mesecniGrafik" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Dnevne statistike -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Aktivnost poslednih 30 dana
                </div>
                <div class="card-body">
                    <canvas id="dnevniGrafik" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabele sa podacima -->
    <div class="row">
        <!-- Najčešće usluge -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list-alt me-1"></i>
                    Najčešće usluge koje radim
                </div>
                <div class="card-body">
                    <?php if (empty($top_usluge)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Nema podataka o uslugama.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
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
                                            <span class="badge bg-primary"><?= $usluga['broj_termina'] ?></span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?= $procenat ?>%">
                                                    <?= $procenat ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Top pacijenti -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Pacijenti sa kojima najčešće radim
                </div>
                <div class="card-body">
                    <?php if (empty($top_pacijenti)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Nema podataka o pacijentima.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Pacijent</th>
                                        <th>Termini</th>
                                        <th>Tretmani</th>
                                        <th>Poslednja aktivnost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($top_pacijenti, 0, 10) as $pacijent): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pacijent['pacijent_ime']) ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?= $pacijent['broj_termina'] ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?= $pacijent['broj_tretmana'] ?></span>
                                        </td>
                                        <td>
                                            <small>
                                                <?php if ($pacijent['poslednja_aktivnost']): ?>
                                                    <?= date('d.m.Y', strtotime($pacijent['poslednja_aktivnost'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mesečni pregled -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-1"></i>
            Detaljne mesečne statistike
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Mesec</th>
                            <th>Broj termina</th>
                            <th>Broj tretmana</th>
                            <th>Razlika</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mesecne_statistike as $index => $stat): ?>
                        <tr>
                            <td><strong><?= $stat['mesec'] ?></strong></td>
                            <td>
                                <span class="badge bg-primary"><?= $stat['termini'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-success"><?= $stat['tretmani'] ?></span>
                            </td>
                            <td>
                                <?php 
                                $razlika = $stat['termini'] - $stat['tretmani'];
                                $boja = $razlika == 0 ? 'secondary' : ($razlika > 0 ? 'warning' : 'info');
                                ?>
                                <span class="badge bg-<?= $boja ?>"><?= $razlika > 0 ? '+' : '' ?><?= $razlika ?></span>
                            </td>
                            <td>
                                <?php if ($index > 0): ?>
                                    <?php 
                                    $prethodni = $mesecne_statistike[$index - 1]['termini'];
                                    $trenutni = $stat['termini'];
                                    if ($trenutni > $prethodni): ?>
                                        <i class="fas fa-arrow-up text-success"></i>
                                    <?php elseif ($trenutni < $prethodni): ?>
                                        <i class="fas fa-arrow-down text-danger"></i>
                                    <?php else: ?>
                                        <i class="fas fa-minus text-secondary"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Brze akcije -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Brze akcije
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="/tretmani/moji" class="btn btn-outline-primary">
                                    <i class="fas fa-notes-medical"></i> Moji tretmani
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="/kartoni/moji" class="btn btn-outline-success">
                                    <i class="fas fa-users"></i> Moji pacijenti
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="/termini/kalendar" class="btn btn-outline-info">
                                    <i class="fas fa-calendar-alt"></i> Kalendar
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <button onclick="window.print()" class="btn btn-outline-secondary">
                                    <i class="fas fa-print"></i> Štampaj izvještaj
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Mesečni grafik
const mesecniCtx = document.getElementById('mesecniGrafik');
const mesecniData = <?= json_encode($mesecne_statistike) ?>;

new Chart(mesecniCtx, {
    type: 'line',
    data: {
        labels: mesecniData.map(item => item.mesec),
        datasets: [{
            label: 'Termini',
            data: mesecniData.map(item => item.termini),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Tretmani',
            data: mesecniData.map(item => item.tretmani),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
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
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>