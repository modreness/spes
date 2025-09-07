<div class="naslov-dugme">
    <h2>Cjenovnik</h2>
    <a href="/cjenovnik/kreiraj" class="btn btn-novo"><i class="fa-solid fa-add"></i> Nova usluga</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'kreirana'): ?>
    <div class="alert alert-success">Usluga je uspješno kreirana.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'azurirana'): ?>
    <div class="alert alert-success">Usluga je uspješno ažurirana.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'obrisana'): ?>
    <div class="alert alert-success">Usluga je uspješno obrisana.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-warning">Greška pri operaciji.</div>
<?php endif; ?>

<div class="main-content-fw">
<table id="tabela" class="table-standard">
  <thead>
      <tr>
        <th>ID</th>
        <th>Kategorija</th>
        <th>Naziv usluge</th>
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
        <td><?= htmlspecialchars($u['naziv']) ?></td>
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