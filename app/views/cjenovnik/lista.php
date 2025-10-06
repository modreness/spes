<div class="naslov-dugme">
    <h2>Cjenovnik</h2>
    <a href="/cjenovnik/kreiraj" class="btn btn-novo"><i class="fa-solid fa-add"></i> Nova usluga</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'kreirana'): ?>
    <div class="uspjeh">Usluga je uspješno kreirana.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'azurirana'): ?>
    <div class="uspjeh">Usluga je uspješno ažurirana.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'obrisana'): ?>
    <div class="uspjeh">Usluga je uspješno obrisana.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="greska">Greška pri operaciji.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deaktivirana'): ?>
    <div class="upozorenje">Usluga je deaktivirana jer je korištena u terminima.</div>
<?php endif; ?>

<div class="main-content-fw">
<table id="tabela" class="table-standard">
  <thead>
      <tr>
        <th>ID</th>
        <th>Kategorija</th>
        <th>Naziv usluge</th>
        <th>Tip</th>
        <th>Opis</th>
        <th>Cijena (KM)</th>
        <th>Status</th>
        <th>Akcije</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($usluge as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td>
          <span class="badge badge-category"><?= htmlspecialchars($u['kategorija_naziv'] ?? 'Bez kategorije') ?></span>
        </td>
        <td>
          <?= htmlspecialchars($u['naziv']) ?>
          <?php if ($u['tip_usluge'] === 'paket' && !empty($u['period'])): ?>
            <br><small style="color: #7f8c8d; font-size: 0.85em;">
              <i class="fa-solid fa-calendar"></i> <?= ucfirst($u['period']) ?>
            </small>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($u['tip_usluge'] === 'paket'): ?>
            <span class="badge" style="background: linear-gradient(90deg, #255AA5, #289CC6); color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
              <i class="fa-solid fa-box"></i> Paket <small style="color: #fff; font-weight: 500; margin-top: 5px; display: inline-block;">
              <?= $u['broj_termina'] ?> termina
            </small>
            </span>
            
          <?php else: ?>
            <span class="badge" style="background: #95a5a6; color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.85em;">
              <i class="fa-solid fa-file"></i> Pojedinačna
            </span>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($u['opis']) ?></td>
        <td class="price"><?= number_format($u['cijena'], 2, ',', '.') ?> KM</td>
        <td>
          <?php if ($u['aktivan']): ?>
            <span class="badge badge-success">Aktivno</span>
          <?php else: ?>
            <span class="badge badge-danger">Neaktivno</span>
          <?php endif; ?>
        </td>
        <td>
          <a href="/cjenovnik/uredi?id=<?= $u['id'] ?>" class="btn btn-sm btn-edit"><i class="fa-solid fa-edit"></i></a>
          <button class="btn btn-sm btn-danger" onclick="potvrdiBrisanje(<?= $u['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
</table>
</div>

<!-- Modal za potvrdu brisanja -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<div id="brisanje-modal" class="modal" style="display:none;">
  <div class="modal-content">
    <p>Jeste li sigurni da želite obrisati ovu uslugu?</p>
    <form method="post" action="/cjenovnik/obrisi" style="margin-top: 20px;">
      <input type="hidden" name="id" id="id-brisanja">
      <div style="text-align: center;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
        <button type="submit" class="btn btn-danger">Da, obriši</button>
      </div>
    </form>
  </div>
</div>

<script>
function potvrdiBrisanje(id) {
  document.getElementById('id-brisanja').value = id;
  document.getElementById('brisanje-modal').style.display = 'block';
  document.getElementById('modal-overlay').style.display = 'block';
}

function zatvoriModal() {
  document.getElementById('brisanje-modal').style.display = 'none';
  document.getElementById('modal-overlay').style.display = 'none';
}

document.getElementById('modal-overlay').addEventListener('click', zatvoriModal);
</script>

<style>
/* Dodaj malo boje za pakete */
tr:has(.badge:contains("Paket")) {
    background: linear-gradient(to right, #ffffff, #f8f9ff);
}
</style>