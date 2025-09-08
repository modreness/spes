<div class="naslov-dugme">
    <h2>Pregled rasporeda</h2>
    <a href="/raspored" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content">
    <!-- Header sa filterom -->
    <div class="schedule-header" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #2c3e50;">Sedmica: <?= $start_date->format('d.m.Y') ?> - <?= $end_date->format('d.m.Y') ?></h3>
            <p style="margin: 5px 0 0 0; color: #7f8c8d;">Pregled rasporeda terapeuta</p>
        </div>
        
        <form method="get" style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap;">
            <label for="filter_datum_od" style="font-weight: 500; color: #34495e;">Sedmica počinje:</label>
            <input type="date" id="filter_datum_od" name="filter_datum_od" 
                   value="<?= htmlspecialchars($datum_od) ?>"
                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-search"></i> Prikaži
            </button>
        </form>
    </div>

    <!-- Raspored vertikalno - dan po dan -->
    <div class="schedule-vertical">
        <?php foreach (dani() as $slug => $label): ?>
            <?php 
            $dan_datum = date('d.m.Y', strtotime("+".array_search($slug, array_keys(dani()))." days", strtotime($datum_od)));
            $ima_terapeute = !empty($raspored_po_danu[$slug]);
            ?>
            <div class="day-row" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 20px; overflow: hidden; border: 1px solid <?= $ima_terapeute ? '#e8f5e8' : '#f8f9fa' ?>;">
                
                <!-- Dan header -->
                <div class="day-header" style="background: <?= $ima_terapeute ? 'linear-gradient(135deg, #27ae60, #2ecc71)' : '#f8f9fa' ?>; padding: 15px 25px; color: <?= $ima_terapeute ? '#fff' : '#7f8c8d' ?>;">
                    <h4 style="margin: 0; font-size: 18px; font-weight: 600;"><?= $label ?></h4>
                    <small style="opacity: 0.9;"><?= $dan_datum ?></small>
                </div>

                <!-- Smjene u redu -->
                <div class="day-content" style="padding: 20px; display: flex; gap: 20px; flex-wrap: wrap;">
                    <?php foreach (smjene() as $key => $naziv): ?>
                        <?php 
                        $terapeuti_u_smjeni = $raspored_po_danu[$slug][$key] ?? [];
                        $smjena_colors = [
                            'jutro' => '#f39c12',
                            'popodne' => '#3498db', 
                            'vecer' => '#9b59b6'
                        ];
                        ?>
                        <div class="shift-block" style="flex: 1; min-width: 200px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid <?= $smjena_colors[$key] ?>;">
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <div style="width: 12px; height: 12px; background: <?= $smjena_colors[$key] ?>; border-radius: 50%; margin-right: 8px;"></div>
                                <strong style="color: #2c3e50; font-size: 16px;"><?= $naziv ?></strong>
                            </div>
                            
                            <?php if (!empty($terapeuti_u_smjeni)): ?>
                                <div class="therapists-list">
                                    <?php foreach ($terapeuti_u_smjeni as $ime): ?>
                                        <div class="therapist-tag" style="display: block; background: #fff; padding: 8px 12px; margin: 5px 0; border-radius: 6px; font-size: 14px; color: #34495e; border: 1px solid #e0e0e0;">
                                            <i class="fa-solid fa-user-doctor" style="margin-right: 8px; color: <?= $smjena_colors[$key] ?>;"></i>
                                            <?= htmlspecialchars($ime) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div style="color: #95a5a6; font-style: italic; text-align: center; padding: 15px; background: #fff; border-radius: 6px;">
                                    <i class="fa-solid fa-calendar-xmark" style="margin-right: 5px;"></i>
                                    Nema terapeuta
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>