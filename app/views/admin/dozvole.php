<div class="naslov-dugme">
    <h2>Upravljanje dozvolama</h2>
    <a href="/admin/dashboard" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Nazad na admin
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'success'): ?>
        <div class="notifikacija uspjeh">Dozvole su uspješno ažurirane.</div>
    <?php elseif ($_GET['msg'] === 'partial_error'): ?>
        <div class="notifikacija greska">Neke dozvole nisu mogle biti ažurirane.</div>
    <?php endif; ?>
<?php endif; ?>

<div class="main-content-fw">
    <div class="help-box">
        <div>
            <i class="fa-solid fa-shield-halved" style="font-size: 2.5em; color: #255AA5;"></i>
        </div>
        <div class="info-text">
            <h3>Sistem dozvola</h3>
            <p>Ovdje možete kontrolisati koje funkcionalnosti su dostupne pojedinim ulogama. 
            Admin uvek ima sve dozvole.</p>
        </div>
    </div>

    <form method="post" action="/admin/dozvole">
        <input type="hidden" name="action" value="update_permissions">
        
        <!-- Tabela dozvola -->
        <table class="table-standard permissions-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Dozvola</th>
                    <th style="width: 20%; text-align: center;">
                        <i class="fa-solid fa-user-tie"></i><br>
                        <strong>Recepcioner</strong>
                    </th>
                    <th style="width: 20%; text-align: center;">
                        <i class="fa-solid fa-user-doctor"></i><br>
                        <strong>Terapeut</strong>
                    </th>
                    <th style="width: 20%; text-align: center;">
                        <i class="fa-solid fa-user"></i><br>
                        <strong>Pacijent</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($available_permissions as $permission_name => $description): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($description) ?></strong><br>
                        <small style="color: #666; font-style: italic;">
                            <?= htmlspecialchars($permission_name) ?>
                        </small>
                    </td>
                    
                    <!-- Recepcioner -->
                    <td style="text-align: center;">
                        <label class="permission-switch">
                            <input type="checkbox" 
                                   name="recepcioner[<?= $permission_name ?>]" 
                                   <?= ($current_permissions['recepcioner'][$permission_name] ?? false) ? 'checked' : '' ?>>
                            <span class="permission-slider"></span>
                        </label>
                    </td>
                    
                    <!-- Terapeut -->
                    <td style="text-align: center;">
                        <label class="permission-switch">
                            <input type="checkbox" 
                                   name="terapeut[<?= $permission_name ?>]" 
                                   <?= ($current_permissions['terapeut'][$permission_name] ?? false) ? 'checked' : '' ?>>
                            <span class="permission-slider"></span>
                        </label>
                    </td>
                    
                    <!-- Pacijent -->
                    <td style="text-align: center;">
                        <label class="permission-switch">
                            <input type="checkbox" 
                                   name="pacijent[<?= $permission_name ?>]" 
                                   <?= ($current_permissions['pacijent'][$permission_name] ?? false) ? 'checked' : '' ?>>
                            <span class="permission-slider"></span>
                        </label>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="bottom-foot">
            <button type="submit" class="submit-button">
                <i class="fa-solid fa-save"></i> Sačuvaj dozvole
            </button>
        </div>
    </form>

    <!-- Informacioni box -->
    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;">
        <h4 style="margin-top: 0; color: #17a2b8;">
            <i class="fa-solid fa-info-circle"></i> Važne napomene
        </h4>
        <ul style="margin-bottom: 0; color: #666;">
            <li><strong>Admin</strong> uvek ima sve dozvole i ne može se ograničiti</li>
            <li><strong>Recepcioner</strong> obično treba pristup većini funkcionalnosti</li>
            <li><strong>Terapeut</strong> treba pristup svojim pacijentima i unošenju tretmana</li>
            <li><strong>Pacijent</strong> obično ima ograničen pristup samo svojim podacima</li>
            <li>Promene se primenjuju odmah na sve korisnike</li>
        </ul>
    </div>
</div>

<style>
.permissions-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #dee2e6;
}

.permissions-table td {
    vertical-align: middle;
    padding: 15px 12px;
}

/* Custom Switch Stilovi */
.permission-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    cursor: pointer;
}

.permission-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.permission-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    border-radius: 24px;
    transition: 0.3s;
}

.permission-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
}

.permission-switch input:checked + .permission-slider {
    background-color: #255AA5;
}

.permission-switch input:checked + .permission-slider:before {
    transform: translateX(26px);
}

.permission-switch:hover .permission-slider {
    box-shadow: 0 0 0 2px rgba(37, 90, 165, 0.2);
}

/* Responsive */
@media (max-width: 768px) {
    .permissions-table {
        font-size: 0.9rem;
    }
    
    .permissions-table th, 
    .permissions-table td {
        padding: 10px 8px;
    }
    
    .permission-switch {
        width: 40px;
        height: 20px;
    }
    
    .permission-slider:before {
        height: 14px;
        width: 14px;
    }
    
    .permission-switch input:checked + .permission-slider:before {
        transform: translateX(20px);
    }
}
</style>

<script>
// Notifikacije auto-hide
const notif = document.querySelector('.notifikacija');
if (notif) {
    setTimeout(() => notif.remove(), 4000);
}

// Potvrda pre submit-a
document.querySelector('form').addEventListener('submit', function(e) {
    if (!confirm('Da li ste sigurni da želite da sačuvate promene dozvola?')) {
        e.preventDefault();
    }
});
</script>