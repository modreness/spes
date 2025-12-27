<div class="naslov-dugme">
    <h2>Dijagnoze</h2>
    <a href="/dijagnoze?action=create" class="btn btn-novo">
        <i class="fa-solid fa-plus"></i> Nova dijagnoza
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Pretraga i info -->
<div style="display: flex; gap: 15px; margin-bottom: 20px; align-items: center;">
    <form method="GET" action="/dijagnoze" style="flex: 1; display: flex; gap: 10px;">
        <input type="hidden" name="action" value="index">
        <input 
            type="text" 
            name="search" 
            placeholder="Pretraži dijagnoze..." 
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
            style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"
        >
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-search"></i> Pretraži
        </button>
        <?php if (!empty($_GET['search'])): ?>
            <a href="/dijagnoze" class="btn btn-secondary">
                <i class="fa-solid fa-times"></i> Očisti
            </a>
        <?php endif; ?>
    </form>
    
    <div style="padding: 10px 20px; background: #e8f4f8; border-radius: 6px; white-space: nowrap;">
        <i class="fa-solid fa-info-circle" style="color: #3498db;"></i>
        <strong>Ukupno:</strong> <?= count($dijagnoze) ?>
    </div>
</div>

<div class="main-content-fw">
    <?php if (empty($dijagnoze)): ?>
        <div style="padding: 60px 40px; text-align: center; color: #7f8c8d; background: #fff; border-radius: 8px;">
            <i class="fa-solid fa-notes-medical" style="font-size: 64px; margin-bottom: 20px; opacity: 0.2;"></i>
            <p style="font-size: 18px; margin: 0; font-weight: 500;">
                <?= !empty($_GET['search']) ? 'Nema rezultata pretrage.' : 'Nema dijagnoza u sistemu.' ?>
            </p>
            <?php if (empty($_GET['search'])): ?>
            <p style="color: #95a5a6; margin-top: 10px;">
                Kliknite na "Nova dijagnoza" dugme da dodate prvu dijagnozu.
            </p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table id="tabela" class="table-standard">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naziv</th>
                    <th>Opis</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dijagnoze as $dijagnoza): ?>
                <tr>
                    <td><?= $dijagnoza['id'] ?></td>
                    <td><?= htmlspecialchars($dijagnoza['naziv']) ?></td>
                    <td><?= htmlspecialchars($dijagnoza['opis'] ?: '-') ?></td>
                    <td>
                        <a href="/dijagnoze?action=edit&id=<?= $dijagnoza['id'] ?>" 
                           class="btn btn-sm btn-edit">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <button 
                            type="button"
                            class="btn btn-sm btn-danger" 
                            onclick="potvrdiBrisanje(<?= $dijagnoza['id'] ?>, '<?= htmlspecialchars(addslashes($dijagnoza['naziv'])) ?>', '<?= htmlspecialchars(addslashes($dijagnoza['opis'])) ?>')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal za brisanje -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<div id="brisanje-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fa-solid fa-exclamation-triangle" style="font-size: 48px; color: #e74c3c;"></i>
        </div>
        
        <p style="font-size: 18px; font-weight: 600; text-align: center; margin-bottom: 20px; color: #2c3e50;">
            Da li ste sigurni da želite obrisati ovu dijagnozu?
        </p>
        
        <div id="modal-detalji" style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 25px;">
            <!-- Detalji će biti ubačeni kroz JavaScript -->
        </div>
        
        <form method="POST" action="/dijagnoze?action=delete">
            <input type="hidden" name="id" id="id-brisanja" value="">
            <div style="text-align: center;">
                <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
                <button type="submit" class="btn btn-danger">Da, obriši</button>
            </div>
        </form>
    </div>
</div>

<script>
function potvrdiBrisanje(id, naziv, opis) {
    // Prvo provjeri koliko kartona koristi ovu dijagnozu
    fetch('/dijagnoze?action=check_usage&id=' + id)
        .then(response => response.json())
        .then(data => {
            // Postavi ID u hidden input
            document.getElementById('id-brisanja').value = id;
            
            // Pripremi upozorenje ako se koristi
            let upozorenje = '';
            if (data.success && data.count > 0) {
                upozorenje = `
                    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                        <i class="fa-solid fa-exclamation-triangle" style="color: #856404;"></i>
                        <strong style="color: #856404;">Pažnja:</strong> 
                        <span style="color: #856404;">Ova dijagnoza se koristi u <strong>${data.count}</strong> karton(a). Brisanjem će biti uklonjena sa svih.</span>
                    </div>
                `;
            }
            
            // Pripremi detalje
            const detalji = `
                ${upozorenje}
                <div style="display: grid; gap: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <strong style="color: #7f8c8d;">Naziv:</strong> 
                        <span style="color: #2c3e50; font-weight: 500;">${naziv}</span>
                    </div>
                    ${opis ? `
                    <div style="display: flex; justify-content: space-between;">
                        <strong style="color: #7f8c8d;">Opis:</strong> 
                        <span style="color: #2c3e50;">${opis}</span>
                    </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('modal-detalji').innerHTML = detalji;
            
            // Prikaži modal
            document.getElementById('brisanje-modal').style.display = 'block';
            document.getElementById('modal-overlay').style.display = 'block';
        })
        .catch(error => {
            console.error('Greška:', error);
            // Ako AJAX ne radi, ipak prikaži modal bez upozorenja
            document.getElementById('id-brisanja').value = id;
            const detalji = `
                <div style="display: grid; gap: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <strong style="color: #7f8c8d;">Naziv:</strong> 
                        <span style="color: #2c3e50; font-weight: 500;">${naziv}</span>
                    </div>
                    ${opis ? `
                    <div style="display: flex; justify-content: space-between;">
                        <strong style="color: #7f8c8d;">Opis:</strong> 
                        <span style="color: #2c3e50;">${opis}</span>
                    </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('modal-detalji').innerHTML = detalji;
            document.getElementById('brisanje-modal').style.display = 'block';
            document.getElementById('modal-overlay').style.display = 'block';
        });
}

function zatvoriModal() {
    document.getElementById('brisanje-modal').style.display = 'none';
    document.getElementById('modal-overlay').style.display = 'none';
}

// Zatvori modal klikom na overlay
document.getElementById('modal-overlay').addEventListener('click', zatvoriModal);
</script>