<div class="naslov-dugme">
    <h2>Pregled rasporeda</h2>
    <a href="/raspored" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content">
    <!-- Filter za sedmicu -->
    <form method="get" style="margin-bottom: 20px;">
        <div class="form-group" style="display: inline-block; margin-right: 10px;">
            <label for="filter_datum_od">Sedmica počinje:</label>
            <input type="date" id="filter_datum_od" name="filter_datum_od" 
                   value="<?= htmlspecialchars($datum_od) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Prikaži</button>
    </form>

    <h3>Sedmica: <?= $start_date->format('d.m.Y') ?> - <?= $end_date->format('d.m.Y') ?></h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <?php foreach (dani() as $slug => $label): ?>
            <div class="day-block" style="background:#fff; padding:20px; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.05);">
                <h4><?= $label ?></h4>
                <small><?= date('d.m.Y', strtotime("+".array_search($slug, array_keys(dani()))." days", strtotime($datum_od))) ?></small>

                <?php foreach (smjene() as $key => $naziv): ?>
                    <div class="smjena-block" style="margin:12px 0; padding:10px; background:#f8f9fa; border-radius:5px;">
                        <strong>🕒 <?= $naziv ?>:</strong><br>
                        <?php if (!empty($raspored_po_danu[$slug][$key])): ?>
                            <ul style="padding-left:18px; margin:5px 0;">
                                <?php foreach ($raspored_po_danu[$slug][$key] as $ime): ?>
                                    <li><?= htmlspecialchars($ime) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <em style="color:#777;">Nema terapeuta</em>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>