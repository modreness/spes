<div class="naslov-dugme">
    <h2>Dijagnoze</h2>
    <a href="/dijagnoze?action=create" class="btn btn-add">
        <i class="fa-solid fa-plus"></i> Nova dijagnoza
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="main-content">
    <!-- Pretraga -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 20px;">
        <form method="GET" action="/dijagnoze" style="display: flex; gap: 10px; align-items: center;">
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
    </div>

    <!-- Informacija -->
    <div style="margin-bottom: 20px; padding: 15px; background: #e8f4f8; border-left: 4px solid #3498db; border-radius: 4px;">
        <p style="margin: 0; color: #2c3e50;">
            <i class="fa-solid fa-info-circle" style="color: #3498db;"></i>
            <strong>Ukupno dijagnoza:</strong> <?= count($dijagnoze) ?>
        </p>
    </div>

    <!-- Lista dijagnoza -->
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <?php if (empty($dijagnoze)): ?>
            <div style="padding: 60px 40px; text-align: center; color: #7f8c8d;">
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
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%;">Naziv</th>
                        <th style="width: 50%;">Opis</th>
                        <th style="width: 15%; text-align: center;">Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dijagnoze as $dijagnoza): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #2c3e50; font-size: 15px;">
                                <?= htmlspecialchars($dijagnoza['naziv']) ?>
                            </div>
                        </td>
                        <td>
                            <div style="color: #7f8c8d; line-height: 1.5;">
                                <?= htmlspecialchars($dijagnoza['opis'] ?: '-') ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <a href="/dijagnoze?action=edit&id=<?= $dijagnoza['id'] ?>" 
                                   class="btn btn-sm btn-primary" 
                                   title="Uredi"
                                   style="padding: 8px 12px;">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-danger" 
                                    title="Obriši"
                                    style="padding: 8px 12px;"
                                    onclick="otvoriModal(<?= $dijagnoza['id'] ?>, '<?= htmlspecialchars(addslashes($dijagnoza['naziv'])) ?>', '<?= htmlspecialchars(addslashes($dijagnoza['opis'])) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
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
        
        <form method="POST" action="/dijagnoze?action=delete" id="delete-form">
            <input type="hidden" name="id" id="id-brisanja" value="">
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">
                    <i class="fa-solid fa-times"></i> Otkaži
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-trash"></i> Da, obriši
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function otvoriModal(id, naziv, opis) {
    // Postavi ID u hidden input
    document.getElementById('id-brisanja').value = id;
    
    // Pripremi detalje
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
    
    // Prikaži modal
    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById('brisanje-modal').style.display = 'block';
}

function zatvoriModal() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.getElementById('brisanje-modal').style.display = 'none';
}

// Zatvori modal kada se klikne na overlay
document.getElementById('modal-overlay').addEventListener('click', zatvoriModal);
</script>