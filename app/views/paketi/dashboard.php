<div class="naslov-dugme">
    <h2>Upravljanje paketima</h2>
    <a href="/paketi/prodaj" class="btn btn-add">
        <i class="fa-solid fa-plus"></i> Prodaj paket
    </a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'prodat'): ?>
    <div class="uspjeh"><i class="fa-solid fa-check-circle"></i> Paket je uspješno prodat!</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="greska"><i class="fa-solid fa-times-circle"></i> Greška pri prodaji paketa.</div>
<?php endif; ?>

<div class="main-content-fw">
    <!-- Statistike -->
    <div class="stats-grid" style="margin-bottom: 25px;">
        <div class="stat-card">
            <h3>Ukupno paketa</h3>
            <div class="stat-number"><?= $ukupno_paketa ?></div>
        </div>
        <div class="stat-card">
            <h3>Aktivni paketi</h3>
            <div class="stat-number" style="color: #ffffff;"><?= $aktivnih_paketa ?></div>
        </div>
        <div class="stat-card">
            <h3>Prosječna iskorištenost</h3>
            <div class="stat-number"><?= $prosjecna_iskoristenos ?>%</div>
        </div>
    </div>

    <!-- Filteri -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 20px;">
        <form method="get" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label for="filter_pacijent" style="display: block; margin-bottom: 5px; font-weight: 500;">Pacijent:</label>
                <select id="filter_pacijent" name="filter_pacijent" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    <option value="">Svi pacijenti</option>
                    <?php foreach ($pacijenti as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $filter_pacijent == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['ime'] . ' ' . $p['prezime']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="flex: 1; min-width: 200px;">
                <label for="filter_status" style="display: block; margin-bottom: 5px; font-weight: 500;">Status:</label>
                <select id="filter_status" name="filter_status" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    <option value="">Svi statusi</option>
                    <option value="aktivan" <?= $filter_status === 'aktivan' ? 'selected' : '' ?>>Aktivan</option>
                    <option value="zavrsen" <?= $filter_status === 'zavrsen' ? 'selected' : '' ?>>Završen</option>
                    <option value="istekao" <?= $filter_status === 'istekao' ? 'selected' : '' ?>>Istekao</option>
                    <option value="otkazan" <?= $filter_status === 'otkazan' ? 'selected' : '' ?>>Otkazan</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Filtriraj
            </button>
            
            <?php if (!empty($filter_pacijent) || !empty($filter_status)): ?>
                <a href="/paketi" class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i> Resetuj
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabela paketa -->
    <?php if (empty($paketi)): ?>
        <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; color: #7f8c8d;">
            <i class="fa-solid fa-box-open" style="font-size: 3em; margin-bottom: 20px; opacity: 0.3;"></i>
            <p style="margin: 0; font-size: 1.2em;">Nema prodatih paketa</p>
            <p style="margin: 10px 0 0 0;">
                <a href="/paketi/prodaj" class="btn btn-add">
                    <i class="fa-solid fa-plus"></i> Prodaj prvi paket
                </a>
            </p>
        </div>
    <?php else: ?>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pacijent</th>
                    <th>Paket</th>
                    <th>Iskorištenost</th>
                    <th>Datum kupovine</th>
                    <th>Status</th>
                    <th>Plaćeno</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paketi as $p): ?>
                    <?php
                    $procenat = $p['ukupno_termina'] > 0 ? round(($p['iskoristeno_termina'] / $p['ukupno_termina']) * 100) : 0;
                    $status_boja = [
                        'aktivan' => '#27ae60',
                        'završen' => '#95a5a6',
                        'istekao' => '#e74c3c',
                        'otkazan' => '#e67e22'
                    ];
                    ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($p['pacijent_ime']) ?></strong><br>
                            <small style="color: #7f8c8d;"><?= htmlspecialchars($p['pacijent_email']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($p['paket_naziv']) ?><br>
                            <small style="color: #7f8c8d;">
                                <?= number_format($p['paket_cijena'], 2, ',', '.') ?> KM
                            </small>
                        </td>
                        <td>
                            <div style="margin-bottom: 5px;">
                                <strong><?= $p['iskoristeno_termina'] ?> / <?= $p['ukupno_termina'] ?></strong> termina
                            </div>
                            <div style="background: #ecf0f1; border-radius: 10px; height: 8px; overflow: hidden;">
                                <div style="background: <?= $procenat >= 100 ? '#e74c3c' : '#289CC6' ?>; height: 100%; width: <?= $procenat ?>%; transition: width 0.3s;"></div>
                            </div>
                            <small style="color: #7f8c8d;"><?= $procenat ?>%</small>
                        </td>
                        <td><?= date('d.m.Y', strtotime($p['datum_kupovine'])) ?></td>
                        <td>
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 500; background: <?= $status_boja[$p['status']] ?>; color: white;">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($p['placeno']): ?>
                                <span style="color: #27ae60;" title="Plaćeno">
                                    <i class="fa-solid fa-check-circle"></i>
                                </span>
                            <?php else: ?>
                                <span style="color: #e74c3c;" title="Nije plaćeno">
                                    <i class="fa-solid fa-times-circle"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/paketi/detalji?id=<?= $p['id'] ?>" class="btn btn-edit" title="Detalji">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>