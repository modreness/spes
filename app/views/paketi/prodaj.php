<div class="naslov-dugme">
    <h2>Prodaj paket</h2>
    <a href="/paketi" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Povratak
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="greska">
        <?php foreach ($errors as $error): ?>
            <p><i class="fa-solid fa-times-circle"></i> <?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="main-content">
    <form method="post" action="/paketi/prodaj">
        
        <!-- Odabir pacijenta -->
        <div class="form-group">
            <label for="pacijent_id">Pacijent *</label>
            <select id="pacijent_id" name="pacijent_id" class="select2" required>
                <option value="">Odaberite pacijenta</option>
                <?php foreach ($pacijenti as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($_POST['pacijent_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['ime'] . ' ' . $p['prezime']) ?> 
                        (<?= htmlspecialchars($p['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                Odaberite pacijenta kojem prodajete paket
            </small>
        </div>

        <!-- Odabir paketa -->
        <div class="form-group">
            <label for="usluga_id">Paket *</label>
            <select id="usluga_id" name="usluga_id" required onchange="prikaziDetalje()">
                <option value="">Odaberite paket</option>
                <?php 
                $trenutna_kategorija = null;
                foreach ($paketi as $paket): 
                    if ($trenutna_kategorija !== $paket['kategorija_naziv']):
                        if ($trenutna_kategorija !== null): ?>
                            </optgroup>
                        <?php endif; ?>
                        <optgroup label="<?= htmlspecialchars($paket['kategorija_naziv'] ?? 'Ostalo') ?>">
                        <?php $trenutna_kategorija = $paket['kategorija_naziv'];
                    endif;
                ?>
                    <option value="<?= $paket['id'] ?>" 
                            data-termina="<?= $paket['broj_termina'] ?>"
                            data-cijena="<?= $paket['cijena'] ?>"
                            data-period="<?= $paket['period'] ?>"
                            <?= ($_POST['usluga_id'] ?? '') == $paket['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($paket['naziv']) ?> 
                        (<?= $paket['broj_termina'] ?> termina - <?= number_format($paket['cijena'], 2, ',', '.') ?> KM)
                    </option>
                <?php endforeach; ?>
                <?php if ($trenutna_kategorija !== null): ?>
                    </optgroup>
                <?php endif; ?>
            </select>
        </div>

        <!-- Detalji paketa -->
        <div id="paket-detalji" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #667eea;">
            <h4 style="margin-top: 0; color: #667eea;">
                <i class="fa-solid fa-info-circle"></i> Detalji paketa
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <strong>Broj termina:</strong><br>
                    <span id="detalj-termina" style="font-size: 1.2em; color: #667eea;">-</span>
                </div>
                <div>
                    <strong>Cijena:</strong><br>
                    <span id="detalj-cijena" style="font-size: 1.2em; color: #27ae60;">-</span>
                </div>
                <div>
                    <strong>Period:</strong><br>
                    <span id="detalj-period" style="font-size: 1.2em;">-</span>
                </div>
                <div>
                    <strong>Cijena po terminu:</strong><br>
                    <span id="detalj-po-terminu" style="font-size: 1.2em; color: #e67e22;">-</span>
                </div>
            </div>
        </div>

        <!-- Datumi -->
        <div class="form-group">
            <label for="datum_pocetka">Datum početka korištenja</label>
            <input type="date" 
                   id="datum_pocetka" 
                   name="datum_pocetka" 
                   value="<?= htmlspecialchars($_POST['datum_pocetka'] ?? '') ?>">
            <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                Opciono - datum od kada pacijent može početi koristiti paket
            </small>
        </div>

        <div class="form-group">
            <label for="datum_kraja">Datum isteka</label>
            <input type="date" 
                   id="datum_kraja" 
                   name="datum_kraja" 
                   value="<?= htmlspecialchars($_POST['datum_kraja'] ?? '') ?>">
            <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                Opciono - rok važenja paketa (ako postoji)
            </small>
        </div>

        <!-- Napomena -->
        <div class="form-group">
            <label for="napomena">Napomena</label>
            <textarea id="napomena" 
                      name="napomena" 
                      rows="3" 
                      placeholder="Dodatne napomene o paketu..."><?= htmlspecialchars($_POST['napomena'] ?? '') ?></textarea>
        </div>

        <!-- Akcije -->
        <div class="form-actions">
            <button type="submit" class="btn btn-add">
                <i class="fa-solid fa-shopping-cart"></i> Prodaj paket
            </button>
            <a href="/paketi" class="btn btn-secondary">Otkaži</a>
        </div>
    </form>
</div>

<script>
function prikaziDetalje() {
    const select = document.getElementById('usluga_id');
    const option = select.options[select.selectedIndex];
    const detalji = document.getElementById('paket-detalji');
    
    if (!option.value) {
        detalji.style.display = 'none';
        return;
    }
    
    const termina = option.dataset.termina;
    const cijena = parseFloat(option.dataset.cijena);
    const period = option.dataset.period || 'Nije definisan';
    const poTerminu = cijena / parseInt(termina);
    
    document.getElementById('detalj-termina').textContent = termina;
    document.getElementById('detalj-cijena').textContent = cijena.toFixed(2) + ' KM';
    document.getElementById('detalj-period').textContent = period.charAt(0).toUpperCase() + period.slice(1);
    document.getElementById('detalj-po-terminu').textContent = poTerminu.toFixed(2) + ' KM';
    
    detalji.style.display = 'block';
}

// Pozovi na load ako je paket već odabran
document.addEventListener('DOMContentLoaded', function() {
    prikaziDetalje();
});
</script>