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

    <!-- Lista dijagnoza -->
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <?php if (empty($dijagnoze)): ?>
            <div style="padding: 40px; text-align: center; color: #7f8c8d;">
                <i class="fa-solid fa-notes-medical" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                <p style="font-size: 16px; margin: 0;">
                    <?= !empty($_GET['search']) ? 'Nema rezultata pretrage.' : 'Nema dijagnoza.' ?>
                </p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Naziv</th>
                        <th style="width: 45%;">Opis</th>
                        <th style="width: 15%; text-align: center;">Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dijagnoze as $dijagnoza): ?>
                    <tr>
                        <td style="font-weight: 600; color: #2c3e50;">
                            <?= htmlspecialchars($dijagnoza['naziv']) ?>
                        </td>
                        <td style="color: #7f8c8d;">
                            <?= htmlspecialchars($dijagnoza['opis'] ?: '-') ?>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 5px; justify-content: center;">
                                <a href="/dijagnoze?action=edit&id=<?= $dijagnoza['id'] ?>" 
                                   class="btn btn-sm btn-primary" 
                                   title="Uredi">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <a href="/dijagnoze?action=delete&id=<?= $dijagnoza['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Obriši"
                                   onclick="return confirm('Da li ste sigurni da želite obrisati ovu dijagnozu?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Informacija -->
    <div style="margin-top: 20px; padding: 15px; background: #e8f4f8; border-left: 4px solid #3498db; border-radius: 4px;">
        <p style="margin: 0; color: #2c3e50;">
            <i class="fa-solid fa-info-circle" style="color: #3498db;"></i>
            <strong>Ukupno dijagnoza:</strong> <?= count($dijagnoze) ?>
        </p>
    </div>
</div>