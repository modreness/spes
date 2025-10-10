<div class="naslov-dugme">
    <h2>Uredi radna vremena smjena</h2>
    <a href="/timetable" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="main-content">
    <form method="post" action="/timetable/uredi">
        
        <?php foreach (smjene() as $key => $naziv): ?>
            <?php 
            $smjena_data = $vremena[$key] ?? null;
            $je_aktivna = $smjena_data ? (bool)$smjena_data['aktivan'] : true;
            $smjena_colors = [
                'jutro' => '#289cc6',
                'popodne' => '#255AA5', 
                'vecer' => '#666666'
            ];
            ?>
            <div class="shift-edit-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 20px; overflow: hidden; border-left: 5px solid <?= $smjena_colors[$key] ?>;">
                
                <div class="shift-header" style="background: <?= $smjena_colors[$key] ?>15; padding: 20px; border-bottom: 1px solid <?= $smjena_colors[$key] ?>30;">
                    <h3 style="margin: 0; color: <?= $smjena_colors[$key] ?>; font-size: 18px;">
                        <i class="fa-solid fa-clock" style="margin-right: 10px;"></i>
                        <?= $naziv ?>
                    </h3>
                </div>

                <div class="shift-content" style="padding: 25px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 200px; gap: 20px; align-items: center;">
                        
                        <div class="form-group">
                            <label for="<?= $key ?>_pocetak">Početak smjene</label>
                            <input type="time" 
                                   id="<?= $key ?>_pocetak" 
                                   name="smjene[<?= $key ?>][pocetak]" 
                                   value="<?= htmlspecialchars($_POST['smjene'][$key]['pocetak'] ?? ($smjena_data['pocetak'] ?? '')) ?>"
                                   required
                                   style="padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; width: 100%;">
                        </div>

                        <div class="form-group">
                            <label for="<?= $key ?>_kraj">Kraj smjene</label>
                            <input type="time" 
                                   id="<?= $key ?>_kraj" 
                                   name="smjene[<?= $key ?>][kraj]" 
                                   value="<?= htmlspecialchars($_POST['smjene'][$key]['kraj'] ?? ($smjena_data['kraj'] ?? '')) ?>"
                                   required
                                   style="padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; width: 100%;">
                        </div>
                        
                        <div style="margin-top: 5px;">
                            <label style="display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 13px 16px; background: <?= $je_aktivna ? '#d4edda' : '#f8d7da' ?>; border-radius: 8px; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
                                <input type="checkbox" 
                                       name="smjene[<?= $key ?>][aktivan]" 
                                       <?= $je_aktivna ? 'checked' : '' ?>
                                       style="width: 18px; height: 18px; margin-right: 8px; cursor: pointer;">
                                <span style="font-weight: 600; color: <?= $je_aktivna ? '#155724' : '#721c24' ?>; font-size: 14px; white-space: nowrap;">
                                    <i class="fa-solid fa-<?= $je_aktivna ? 'check-circle' : 'times-circle' ?>" style="margin-right: 5px;"></i>
                                    <?= $je_aktivna ? 'Aktivna' : 'Neaktivna' ?>
                                </span>
                            </label>
                        </div>
                    </div>

                    <?php if ($smjena_data): ?>
                        <div class="current-time" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 6px; text-align: center; color: #7f8c8d; font-size: 14px;">
                            <strong>Trenutno:</strong> 
                            <?= date('H:i', strtotime($smjena_data['pocetak'])) ?> - <?= date('H:i', strtotime($smjena_data['kraj'])) ?>
                            <span style="margin-left: 15px;">
                                <i class="fa-solid fa-circle" style="color: <?= $je_aktivna ? '#28a745' : '#dc3545' ?>; font-size: 8px;"></i>
                                <?= $je_aktivna ? 'Aktivna' : 'Neaktivna' ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="form-actions" style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn btn-add" style="padding: 12px 30px; font-size: 16px;">
                <i class="fa-solid fa-save"></i> Sačuvaj promjene
            </button>
            <a href="/timetable" class="btn btn-secondary" style="padding: 12px 30px; font-size: 16px;">Otkaži</a>
        </div>
    </form>
</div>

<script>
// Dinamički mijenjaj stil checkboxa
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="checkbox"][name^="smjene"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const label = this.closest('label');
            const span = label.querySelector('span');
            const icon = span.querySelector('i');
            
            if (this.checked) {
                label.style.background = '#d4edda';
                span.style.color = '#155724';
                icon.className = 'fa-solid fa-check-circle';
                span.childNodes[2].textContent = 'Aktivna';
            } else {
                label.style.background = '#f8d7da';
                span.style.color = '#721c24';
                icon.className = 'fa-solid fa-times-circle';
                span.childNodes[2].textContent = 'Neaktivna';
            }
        });
    });
});
</script>