<div class="naslov-dugme">
    <h2>Timetable - Radna vremena smjena</h2>
    <a href="/timetable/uredi" class="btn btn-edit"><i class="fa-solid fa-edit"></i> Uredi vremena</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'azurirano'): ?>
    <div class="alert alert-success">Radna vremena su uspješno ažurirana.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-warning">Greška pri ažuriranju vremena.</div>
<?php endif; ?>

<div class="main-content-fw">
    <div class="timetable-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        
        <?php foreach (smjene() as $key => $naziv): ?>
            <?php 
            $smjena_data = $vremena[$key] ?? null;
            $smjena_colors = [
                'jutro' => '#289cc6',
                'popodne' => '#255AA5', 
                'vecer' => '#666666'
            ];
            ?>
            <div class="shift-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; border-left: 5px solid <?= $smjena_colors[$key] ?>;">
                
                <!-- Smjena header -->
                <div class="shift-header" style="background: linear-gradient(135deg, <?= $smjena_colors[$key] ?>, <?= $smjena_colors[$key] ?>dd); padding: 20px; color: #fff;">
                    <h3 style="margin: 0; font-size: 20px; font-weight: 600;">
                        <i class="fa-solid fa-clock" style="margin-right: 10px;"></i>
                        <?= $naziv ?>
                    </h3>
                </div>

                <!-- Vremena -->
                <div class="shift-content" style="padding: 25px;">
                    <?php if ($smjena_data): ?>
                        <div class="time-display" style="text-align: center;">
                            <div class="time-range" style="font-size: 28px; font-weight: 600; color: #2c3e50; margin-bottom: 10px;">
                                <?= date('H:i', strtotime($smjena_data['pocetak'])) ?> 
                                <span style="color: #95a5a6; margin: 0 10px;">-</span>
                                <?= date('H:i', strtotime($smjena_data['kraj'])) ?>
                            </div>
                            
                            <div class="time-duration" style="color: #7f8c8d; font-size: 14px; margin-bottom: 15px;">
                                <?php 
                                $start = new DateTime($smjena_data['pocetak']);
                                $end = new DateTime($smjena_data['kraj']);
                                $duration = $start->diff($end);
                                echo $duration->h . 'h ' . $duration->i . 'min';
                                ?>
                            </div>

                            <div class="status-badge" style="display: inline-block; background: #e8f5e8; color: #27ae60; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 500;">
                                <i class="fa-solid fa-check" style="margin-right: 5px;"></i>
                                AKTIVNO
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-time" style="text-align: center; color: #95a5a6;">
                            <i class="fa-solid fa-clock-slash" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                            <p style="margin: 0; font-style: italic;">Vrijeme nije definirano</p>
                            <div class="status-badge" style="display: inline-block; background: #fdf2e9; color: #e67e22; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 500; margin-top: 10px;">
                                <i class="fa-solid fa-exclamation-triangle" style="margin-right: 5px;"></i>
                                NEDEFINIRANO
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 25px;">
        <h4 style="margin: 0 0 10px 0; color: #2c3e50;">
            <i class="fa-solid fa-info-circle" style="margin-right: 8px; color: #3498db;"></i>
            Napomena
        </h4>
        <p style="margin: 0; color: #7f8c8d; line-height: 1.6;">
            Ova vremena se koriste kao osnova za raspored terapeuta. Promjena radnog vremena će utjecati na sve buduće rasporede.
        </p>
    </div>
</div>