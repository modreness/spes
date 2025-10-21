<div class="container-fluid px-4">
    <h1 class="mt-4">Moji pacijenti</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Moji pacijenti</li>
    </ol>
    
    <!-- Statistike -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Ukupno pacijenata</div>
                            <div class="h5 mb-0"><?= $ukupno_pacijenata ?></div>
                        </div>
                        <div><i class="fas fa-users fa-2x"></i></div>
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
                            <div class="small text-white-50">Termini (30 dana)</div>
                            <div class="h5 mb-0"><?= $termini_30_dana ?></div>
                        </div>
                        <div><i class="fas fa-calendar-check fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Prosek tretmana</div>
                            <div class="h5 mb-0"><?= $ukupno_pacijenata > 0 ? round($ukupno_tretmana / $ukupno_pacijenata, 1) : 0 ?></div>
                        </div>
                        <div><i class="fas fa-chart-line fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-up potreban -->
    <?php if (!empty($potreban_followup)): ?>
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Pacijenti kojima je potreban follow-up
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($potreban_followup as $pacijent): ?>
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="alert alert-warning mb-2">
                        <strong><?= htmlspecialchars($pacijent['pacijent_ime']) ?></strong><br>
                        <small>
                            Poslednji tretman: <?= $pacijent['dana_od_tretmana'] ?> dana ago<br>
                            <a href="/kartoni/pregled?id=<?= $pacijent['id'] ?>" class="btn btn-outline-warning btn-sm mt-1">
                                <i class="fas fa-eye"></i> Pregled
                            </a>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista pacijenata -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i>
            Lista mojih pacijenata
        </div>
        <div class="card-body">
            <?php if (empty($moji_kartoni)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Nemate dodeljenih pacijenata ili niste još uvek radili ni sa kim.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Pacijent</th>
                                <th>Broj kartona</th>
                                <th>Email</th>
                                <th>JMBG</th>
                                <th>Broj termina</th>
                                <th>Broj tretmana</th>
                                <th>Poslednja aktivnost</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($moji_kartoni as $karton): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($karton['pacijent_ime']) ?></strong>
                                    <?php if ($karton['dijagnoza']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($karton['dijagnoza']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($karton['broj_upisa']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($karton['email']) ?></td>
                                <td><?= htmlspecialchars($karton['jmbg']) ?></td>
                                <td>
                                    <span class="badge bg-primary"><?= $karton['broj_termina'] ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?= $karton['broj_tretmana'] ?></span>
                                </td>
                                <td>
                                    <?php if ($karton['poslednji_tretman']): ?>
                                        <small>Tretman: <?= date('d.m.Y', strtotime($karton['poslednji_tretman'])) ?></small>
                                    <?php elseif ($karton['poslednji_termin']): ?>
                                        <small>Termin: <?= date('d.m.Y H:i', strtotime($karton['poslednji_termin'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Nema aktivnosti</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/kartoni/pregled?id=<?= $karton['id'] ?>" class="btn btn-outline-primary btn-sm" title="Pregled kartona">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/kartoni/tretmani?id=<?= $karton['id'] ?>" class="btn btn-outline-success btn-sm" title="Tretmani">
                                            <i class="fas fa-notes-medical"></i>
                                        </a>
                                        <?php if (isset($karton['karton_id']) && $karton['karton_id']): ?>
                                        <a href="/kartoni/nalazi?id=<?= $karton['id'] ?>" class="btn btn-outline-info btn-sm" title="Nalazi">
                                            <i class="fas fa-file-medical"></i>
                                        </a>
                                        <?php endif; ?>
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

    <!-- Brze akcije -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Brze akcije
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/tretmani/moji" class="btn btn-outline-primary">
                            <i class="fas fa-notes-medical"></i> Pregled svih tretmana
                        </a>
                        <a href="/termini/kalendar" class="btn btn-outline-success">
                            <i class="fas fa-calendar-alt"></i> Kalendar termina
                        </a>
                        <a href="/izvjestaji/terapeut" class="btn btn-outline-info">
                            <i class="fas fa-chart-line"></i> Moje statistike
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Legenda
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-eye text-primary"></i> Pregled kartona</li>
                        <li><i class="fas fa-notes-medical text-success"></i> Historie tretmana</li>
                        <li><i class="fas fa-file-medical text-info"></i> Nalazi pacijenta</li>
                        <li><i class="fas fa-exclamation-triangle text-warning"></i> Follow-up potreban (>14 dana)</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        Prikazani su samo pacijenti sa kojima ste radili.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>