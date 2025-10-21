<div class="container-fluid px-4">
    <h1 class="mt-4">Moj raspored</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Moj raspored</li>
    </ol>
    
    <!-- Statistike -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Termina ove sedmice</div>
                            <div class="h5 mb-0"><?= $ukupno_termina ?></div>
                        </div>
                        <div><i class="fas fa-calendar-week fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Obavljeno</div>
                            <div class="h5 mb-0"><?= $broj_obavljenih ?></div>
                        </div>
                        <div><i class="fas fa-check-circle fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Period</div>
                            <div class="h6 mb-0"><?= date('d.m') ?> - <?= date('d.m', strtotime('sunday this week')) ?></div>
                        </div>
                        <div><i class="fas fa-calendar-alt fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Uspešnost</div>
                            <div class="h5 mb-0"><?= $ukupno_termina > 0 ? round(($broj_obavljenih / $ukupno_termina) * 100) : 0 ?>%</div>
                        </div>
                        <div><i class="fas fa-chart-line fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Raspored tabela -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Sedmični raspored (<?= date('d.m.Y', strtotime('monday this week')) ?> - <?= date('d.m.Y', strtotime('sunday this week')) ?>)
        </div>
        <div class="card-body">
            <?php if (empty($moj_raspored)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Nema definisanog rasporeda za ovu sedmicu. Kontaktirajte administratora.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Ponedeljak</th>
                                <th>Utorak</th>
                                <th>Sreda</th>
                                <th>Četvrtak</th>
                                <th>Petak</th>
                                <th>Subota</th>
                                <th>Nedelja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($moj_raspored as $raspored): ?>
                            <tr>
                                <td><strong><?= $raspored['datum_od_format'] ?> - <?= $raspored['datum_do_format'] ?></strong></td>
                                <td><?= $raspored['ponedeljak'] ?: '-' ?></td>
                                <td><?= $raspored['utorak'] ?: '-' ?></td>
                                <td><?= $raspored['sreda'] ?: '-' ?></td>
                                <td><?= $raspored['cetvrtak'] ?: '-' ?></td>
                                <td><?= $raspored['petak'] ?: '-' ?></td>
                                <td><?= $raspored['subota'] ?: '-' ?></td>
                                <td><?= $raspored['nedelja'] ?: '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Termini po danima -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-check me-1"></i>
            Moji termini ove sedmice
        </div>
        <div class="card-body">
            <?php if (empty($termini_po_danima)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Nema zakazanih termina za ovu sedmicu.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php 
                    $dani = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    $dani_naziv = ['Ponedeljak', 'Utorak', 'Sreda', 'Četvrtak', 'Petak', 'Subota', 'Nedelja'];
                    
                    for ($i = 0; $i < 7; $i++):
                        $dan_datum = date('Y-m-d', strtotime($dani[$i] . ' this week'));
                        $termini_dana = $termini_po_danima[$dan_datum] ?? [];
                        $je_danas = ($dan_datum === date('Y-m-d'));
                    ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card <?= $je_danas ? 'border-primary' : '' ?>">
                            <div class="card-header <?= $je_danas ? 'bg-primary text-white' : '' ?>">
                                <strong><?= $dani_naziv[$i] ?></strong>
                                <small class="d-block"><?= date('d.m.Y', strtotime($dan_datum)) ?></small>
                                <?= $je_danas ? '<span class="badge bg-light text-dark">DANAS</span>' : '' ?>
                            </div>
                            <div class="card-body p-2">
                                <?php if (empty($termini_dana)): ?>
                                    <small class="text-muted">Slobodan dan</small>
                                <?php else: ?>
                                    <?php foreach ($termini_dana as $termin): ?>
                                    <div class="border-bottom py-2">
                                        <div class="d-flex justify-content-between">
                                            <small><strong><?= $termin['vrijeme'] ?></strong></small>
                                            <span class="badge bg-<?= $termin['status'] === 'obavljen' ? 'success' : ($termin['status'] === 'zakazan' ? 'primary' : 'warning') ?>">
                                                <?= ucfirst($termin['status']) ?>
                                            </span>
                                        </div>
                                        <div class="small"><?= htmlspecialchars($termin['pacijent_ime']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($termin['usluga']) ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
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
                        <a href="/termini/kalendar" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-alt"></i> Kalendar termina
                        </a>
                        <a href="/kartoni/moji" class="btn btn-outline-success">
                            <i class="fas fa-users"></i> Moji pacijenti
                        </a>
                        <a href="/tretmani/moji" class="btn btn-outline-info">
                            <i class="fas fa-notes-medical"></i> Moji tretmani
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Napomene
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Zeleno = Obavljen termin</li>
                        <li><i class="fas fa-clock text-primary"></i> Plavo = Zakazan termin</li>
                        <li><i class="fas fa-play text-warning"></i> Žuto = Termin u toku</li>
                        <li><i class="fas fa-times text-danger"></i> Crveno = Otkazan termin</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        Za izmene rasporeda kontaktirajte administratora.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>