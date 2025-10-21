<div class="container-fluid px-4">
    <h1 class="mt-4">Moji tretmani</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Moji tretmani</li>
    </ol>
    
    <!-- Statistike -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Ukupno tretmana</div>
                            <div class="h5 mb-0"><?= $ukupno_svih_tretmana ?></div>
                        </div>
                        <div><i class="fas fa-notes-medical fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Ovaj mesec</div>
                            <div class="h5 mb-0"><?= $tretmani_ovaj_mesec ?></div>
                        </div>
                        <div><i class="fas fa-calendar-month fa-2x"></i></div>
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
                            <div class="small text-white-50">Prosek po pacijentu</div>
                            <div class="h5 mb-0"><?= $broj_pacijenata > 0 ? round($ukupno_svih_tretmana / $broj_pacijenata, 1) : 0 ?></div>
                        </div>
                        <div><i class="fas fa-chart-line fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filteri -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filteri
        </div>
        <div class="card-body">
            <form method="GET" action="/tretmani/moji">
                <div class="row">
                    <div class="col-md-3">
                        <label for="pacijent_id" class="form-label">Pacijent</label>
                        <select name="pacijent_id" id="pacijent_id" class="form-select">
                            <option value="">Svi pacijenti</option>
                            <?php foreach ($pacijenti_lista as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $pacijent_id == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['ime_prezime']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="datum_od" class="form-label">Datum od</label>
                        <input type="date" name="datum_od" id="datum_od" class="form-control" value="<?= $datum_od ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="datum_do" class="form-label">Datum do</label>
                        <input type="date" name="datum_do" id="datum_do" class="form-control" value="<?= $datum_do ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtriraj
                            </button>
                        </div>
                    </div>
                </div>
                <?php if ($datum_od || $datum_do || $pacijent_id): ?>
                <div class="row mt-2">
                    <div class="col-12">
                        <a href="/tretmani/moji" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Ukloni filtere
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Lista tretmana -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-list me-1"></i>
                Lista tretmana (<?= $ukupno_tretmana ?> rezultata)
            </span>
            
            <!-- Paginacija info -->
            <?php if ($ukupno_stranica > 1): ?>
            <span class="badge bg-secondary">
                Stranica <?= $page ?> od <?= $ukupno_stranica ?>
            </span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($moji_tretmani)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php if ($datum_od || $datum_do || $pacijent_id): ?>
                        Nema tretmana koji zadovoljavaju filter kriterijume.
                    <?php else: ?>
                        Niste još uvek unosili tretmane.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Pacijent</th>
                                <th>Broj kartona</th>
                                <th>Stanje prije</th>
                                <th>Terapija</th>
                                <th>Stanje poslije</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($moji_tretmani as $tretman): ?>
                            <tr>
                                <td>
                                    <strong><?= $tretman['datum_format'] ?></strong>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($tretman['pacijent_ime']) ?></strong>
                                    <?php if ($tretman['dijagnoza']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($tretman['dijagnoza']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($tretman['broj_upisa']) ?></span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($tretman['stanje_prije']) ?>">
                                        <?= htmlspecialchars(substr($tretman['stanje_prije'], 0, 50)) ?><?= strlen($tretman['stanje_prije']) > 50 ? '...' : '' ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($tretman['terapija']) ?>">
                                        <?= htmlspecialchars(substr($tretman['terapija'], 0, 60)) ?><?= strlen($tretman['terapija']) > 60 ? '...' : '' ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($tretman['stanje_poslije']) ?>">
                                        <?= htmlspecialchars(substr($tretman['stanje_poslije'], 0, 50)) ?><?= strlen($tretman['stanje_poslije']) > 50 ? '...' : '' ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="prikaziTretman(<?= $tretman['id'] ?>)" 
                                                title="Detaljno">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="/kartoni/pregled?id=<?= $tretman['karton_id'] ?>" 
                                           class="btn btn-outline-success btn-sm" 
                                           title="Karton">
                                            <i class="fas fa-folder-open"></i>
                                        </a>
                                        <?php if (in_array($user['uloga'], ['admin', 'terapeut'])): ?>
                                        <a href="/kartoni/uredi-tretman?id=<?= $tretman['id'] ?>" 
                                           class="btn btn-outline-warning btn-sm" 
                                           title="Uredi">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginacija -->
                <?php if ($ukupno_stranica > 1): ?>
                <nav aria-label="Paginacija tretmana">
                    <ul class="pagination justify-content-center">
                        <!-- Prethodna stranica -->
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $pacijent_id ? '&pacijent_id=' . $pacijent_id : '' ?><?= $datum_od ? '&datum_od=' . $datum_od : '' ?><?= $datum_do ? '&datum_do=' . $datum_do : '' ?>">
                                <i class="fas fa-chevron-left"></i> Prethodna
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Stranice -->
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($ukupno_stranica, $page + 2);
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= $pacijent_id ? '&pacijent_id=' . $pacijent_id : '' ?><?= $datum_od ? '&datum_od=' . $datum_od : '' ?><?= $datum_do ? '&datum_do=' . $datum_do : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <!-- Sledeća stranica -->
                        <?php if ($page < $ukupno_stranica): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $pacijent_id ? '&pacijent_id=' . $pacijent_id : '' ?><?= $datum_od ? '&datum_od=' . $datum_od : '' ?><?= $datum_do ? '&datum_do=' . $datum_do : '' ?>">
                                Sledeća <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
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
                        <a href="/kartoni/moji" class="btn btn-outline-primary">
                            <i class="fas fa-users"></i> Moji pacijenti
                        </a>
                        <a href="/termini/kalendar" class="btn btn-outline-success">
                            <i class="fas fa-calendar-alt"></i> Kalendar termina
                        </a>
                        <a href="/izvjestaji/terapeut" class="btn btn-outline-info">
                            <i class="fas fa-chart-line"></i> Statistike
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
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-eye text-primary"></i> Detaljno - pun prikaz tretmana</li>
                        <li><i class="fas fa-folder-open text-success"></i> Karton - pregled kartona pacijenta</li>
                        <li><i class="fas fa-edit text-warning"></i> Uredi - izmena tretmana</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        Prikazani su samo tretmani koje ste vi uneli.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal za prikaz tretmana -->
<div class="modal fade" id="tretmanModal" tabindex="-1" aria-labelledby="tretmanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tretmanModalLabel">Detaljno o tretmanu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="tretmanModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
            </div>
        </div>
    </div>
</div>

<script>
function prikaziTretman(tretmanId) {
    // Find tretman data in PHP array
    const tretmani = <?= json_encode($moji_tretmani) ?>;
    const tretman = tretmani.find(t => t.id == tretmanId);
    
    if (tretman) {
        const modalBody = document.getElementById('tretmanModalBody');
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-user"></i> Pacijent</h6>
                    <p><strong>${tretman.pacijent_ime}</strong></p>
                    
                    <h6><i class="fas fa-calendar"></i> Datum</h6>
                    <p>${tretman.datum_format}</p>
                    
                    <h6><i class="fas fa-file-medical"></i> Karton</h6>
                    <p>Broj: ${tretman.broj_upisa}</p>
                    ${tretman.dijagnoza ? '<p>Dijagnoza: ' + tretman.dijagnoza + '</p>' : ''}
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-stethoscope"></i> Stanje prije tretmana</h6>
                    <p class="bg-light p-2 rounded">${tretman.stanje_prije}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h6><i class="fas fa-notes-medical"></i> Sprovedena terapija</h6>
                    <p class="bg-light p-2 rounded">${tretman.terapija}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h6><i class="fas fa-check-circle"></i> Stanje nakon tretmana</h6>
                    <p class="bg-light p-2 rounded">${tretman.stanje_poslije}</p>
                </div>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('tretmanModal'));
        modal.show();
    }
}
</script>