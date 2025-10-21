<div class="container-fluid px-4">
    <h1 class="mt-4">Nalazi mojih pacijenata</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Nalazi pacijenata</li>
    </ol>
    
    <!-- Statistike -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Ukupno nalaza</div>
                            <div class="h5 mb-0"><?= $ukupno_nalaza ?></div>
                        </div>
                        <div><i class="fas fa-file-medical fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Pacijenti sa nalazima</div>
                            <div class="h5 mb-0"><?= $pacijenti_sa_nalazima ?></div>
                        </div>
                        <div><i class="fas fa-users fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Novi nalazi (30 dana)</div>
                            <div class="h5 mb-0"><?= $novi_nalazi_30_dana ?></div>
                        </div>
                        <div><i class="fas fa-calendar-plus fa-2x"></i></div>
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
                            <div class="h5 mb-0"><?= $pacijenti_sa_nalazima > 0 ? round($ukupno_nalaza / $pacijenti_sa_nalazima, 1) : 0 ?></div>
                        </div>
                        <div><i class="fas fa-chart-line fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tipovi nalaza -->
    <?php if (!empty($tipovi_nalaza)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-pie me-1"></i>
            Tipovi nalaza
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($tipovi_nalaza as $tip): ?>
                <div class="col-md-3 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php
                            $ikona = 'fas fa-file';
                            $boja = 'secondary';
                            switch($tip['tip_nalaza']) {
                                case 'PDF dokumenti': $ikona = 'fas fa-file-pdf'; $boja = 'danger'; break;
                                case 'Slike/RTG': $ikona = 'fas fa-images'; $boja = 'info'; break;
                                case 'Word dokumenti': $ikona = 'fas fa-file-word'; $boja = 'primary'; break;
                            }
                            ?>
                            <i class="<?= $ikona ?> fa-2x text-<?= $boja ?>"></i>
                        </div>
                        <div>
                            <div class="fw-bold"><?= $tip['broj'] ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($tip['tip_nalaza']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filteri -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filteri
        </div>
        <div class="card-body">
            <form method="GET" action="/nalazi/moji-pacijenti">
                <div class="row">
                    <div class="col-md-4">
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
                    <div class="col-md-2">
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
                        <a href="/nalazi/moji-pacijenti" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Ukloni filtere
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Lista nalaza -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-list me-1"></i>
            Nalazi mojih pacijenata (<?= count($nalazi) ?> rezultata)
        </div>
        <div class="card-body">
            <?php if (empty($nalazi)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php if ($datum_od || $datum_do || $pacijent_id): ?>
                        Nema nalaza koji zadovoljavaju filter kriterijume.
                    <?php else: ?>
                        Vaši pacijenti još uvek nemaju uploadovane nalaze.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Pacijent</th>
                                <th>Karton</th>
                                <th>Naziv nalaza</th>
                                <th>Opis</th>
                                <th>Dodao</th>
                                <th>Tip</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nalazi as $nalaz): ?>
                            <tr>
                                <td>
                                    <strong><?= $nalaz['datum_upload_format'] ?></strong>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($nalaz['pacijent_ime']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($nalaz['broj_upisa']) ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($nalaz['naziv']) ?>
                                </td>
                                <td>
                                    <?php if ($nalaz['opis']): ?>
                                        <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($nalaz['opis']) ?>">
                                            <?= htmlspecialchars(substr($nalaz['opis'], 0, 50)) ?><?= strlen($nalaz['opis']) > 50 ? '...' : '' ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($nalaz['dodao_ime'] ?? 'Nepoznato') ?></small>
                                </td>
                                <td>
                                    <?php
                                    $ext = strtolower(pathinfo($nalaz['file_path'], PATHINFO_EXTENSION));
                                    $ikona = 'fas fa-file';
                                    $boja = 'secondary';
                                    $tip_text = 'Fajl';
                                    
                                    switch($ext) {
                                        case 'pdf': 
                                            $ikona = 'fas fa-file-pdf'; 
                                            $boja = 'danger'; 
                                            $tip_text = 'PDF';
                                            break;
                                        case 'jpg':
                                        case 'jpeg':
                                        case 'png':
                                        case 'gif':
                                            $ikona = 'fas fa-images'; 
                                            $boja = 'info'; 
                                            $tip_text = 'Slika';
                                            break;
                                        case 'doc':
                                        case 'docx':
                                            $ikona = 'fas fa-file-word'; 
                                            $boja = 'primary'; 
                                            $tip_text = 'Word';
                                            break;
                                    }
                                    ?>
                                    <span class="badge bg-<?= $boja ?>">
                                        <i class="<?= $ikona ?>"></i> <?= $tip_text ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/<?= $nalaz['file_path'] ?>" target="_blank" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Preuzmi/Otvori">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="/kartoni/pregled?id=<?= $nalaz['pacijent_id'] ?>" 
                                           class="btn btn-outline-success btn-sm" 
                                           title="Karton pacijenta">
                                            <i class="fas fa-folder-open"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm" 
                                                onclick="prikaziNalaz(<?= htmlspecialchars(json_encode($nalaz)) ?>)" 
                                                title="Detaljno">
                                            <i class="fas fa-eye"></i>
                                        </button>
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

    <!-- Grupisano po pacijentima -->
    <?php if (!empty($nalazi_po_pacijentima)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i>
            Pregled po pacijentima
        </div>
        <div class="card-body">
            <div class="accordion" id="pacijentiAccordion">
                <?php $index = 0; foreach ($nalazi_po_pacijentima as $pacijent_ime => $nalazi_pacijenta): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $index ?>">
                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" 
                                aria-expanded="<?= $index == 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                            <strong><?= htmlspecialchars($pacijent_ime) ?></strong>
                            <span class="badge bg-primary ms-2"><?= count($nalazi_pacijenta) ?> nalaza</span>
                        </button>
                    </h2>
                    <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index == 0 ? 'show' : '' ?>" 
                         aria-labelledby="heading<?= $index ?>" data-bs-parent="#pacijentiAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                <?php foreach ($nalazi_pacijenta as $nalaz): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($nalaz['naziv']) ?></h6>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> <?= $nalaz['datum_upload_format'] ?>
                                                </small>
                                                <?php if ($nalaz['opis']): ?>
                                                    <br><small><?= htmlspecialchars(substr($nalaz['opis'], 0, 100)) ?><?= strlen($nalaz['opis']) > 100 ? '...' : '' ?></small>
                                                <?php endif; ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <a href="/<?= $nalaz['file_path'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-download"></i> Otvori
                                                </a>
                                                <span class="badge bg-secondary"><?= pathinfo($nalaz['file_path'], PATHINFO_EXTENSION) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $index++; endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                        <a href="/tretmani/moji" class="btn btn-outline-success">
                            <i class="fas fa-notes-medical"></i> Moji tretmani
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
                        <li><i class="fas fa-download text-primary"></i> Preuzmi/Otvori nalaz</li>
                        <li><i class="fas fa-folder-open text-success"></i> Karton pacijenta</li>
                        <li><i class="fas fa-eye text-info"></i> Detaljno o nalazu</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        Prikazani su nalazi samo onih pacijenata sa kojima ste radili.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal za prikaz nalaza -->
<div class="modal fade" id="nalazModal" tabindex="-1" aria-labelledby="nalazModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nalazModalLabel">Detaljno o nalazu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="nalazModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <a href="#" id="nalazDownloadLink" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download"></i> Preuzmi
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
            </div>
        </div>
    </div>
</div>

<script>
function prikaziNalaz(nalaz) {
    const modalBody = document.getElementById('nalazModalBody');
    const downloadLink = document.getElementById('nalazDownloadLink');
    
    // Određuj tip fajla
    const ext = nalaz.file_path.split('.').pop().toLowerCase();
    let tipFajla = 'Dokument';
    let ikona = 'fas fa-file';
    
    switch(ext) {
        case 'pdf': tipFajla = 'PDF dokument'; ikona = 'fas fa-file-pdf'; break;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif': tipFajla = 'Slika/RTG'; ikona = 'fas fa-images'; break;
        case 'doc':
        case 'docx': tipFajla = 'Word dokument'; ikona = 'fas fa-file-word'; break;
    }
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-user"></i> Pacijent</h6>
                <p><strong>${nalaz.pacijent_ime}</strong></p>
                
                <h6><i class="fas fa-file-medical"></i> Karton</h6>
                <p>Broj: ${nalaz.broj_upisa}</p>
                
                <h6><i class="${ikona}"></i> Tip</h6>
                <p>${tipFajla}</p>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-calendar"></i> Datum upload-a</h6>
                <p>${nalaz.datum_upload_format}</p>
                
                <h6><i class="fas fa-user-plus"></i> Dodao</h6>
                <p>${nalaz.dodao_ime || 'Nepoznato'}</p>
                
                <h6><i class="fas fa-file"></i> Putanja</h6>
                <p><small class="text-muted">${nalaz.file_path}</small></p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6><i class="fas fa-tag"></i> Naziv nalaza</h6>
                <p class="bg-light p-2 rounded">${nalaz.naziv}</p>
            </div>
        </div>
        ${nalaz.opis ? `
        <div class="row">
            <div class="col-12">
                <h6><i class="fas fa-comment"></i> Opis</h6>
                <p class="bg-light p-2 rounded">${nalaz.opis}</p>
            </div>
        </div>
        ` : ''}
    `;
    
    downloadLink.href = '/' + nalaz.file_path;
    
    const modal = new bootstrap.Modal(document.getElementById('nalazModal'));
    modal.show();
}
</script>