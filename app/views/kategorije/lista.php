<div class="naslov-dugme">
    <h2>Kategorije usluga</h2>
    <a href="/kategorije/kreiraj" class="btn btn-novo"><i class="fa-solid fa-add"></i> Nova kategorija</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'kreirana'): ?>
    <div class="alert alert-success">Kategorija je uspješno kreirana.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'obrisana'): ?>
    <div class="alert alert-success">Kategorija je uspješno obrisana.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-warning">Greška pri operaciji.</div>
<?php endif; ?>

<div class="main-content-fw">
<table id="tabela" class="table-standard">
  <thead>
      <tr>
        <th>ID</th>
        <th>Naziv</th>
        <th>Opis</th>
        <th>Broj usluga</th>
        <th>Akcije</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($kategorije as $k): ?>
      <tr>
        <td><?= $k['id'] ?></td>
        <td><?= htmlspecialchars($k['naziv']) ?></td>
        <td><?= htmlspecialchars($k['opis']) ?></td>
        <td>
            <?php
            // Broj usluga u ovoj kategoriji
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cjenovnik WHERE kategorija_id = ? AND aktivan = 1");
            $stmt->execute([$k['id']]);
            echo $stmt->fetchColumn();
            ?>
        </td>
        <td>
          <a href="/kategorije/uredi?id=<?= $k['id'] ?>" class="btn btn-sm btn-edit"><i class="fa-solid fa-edit"></i></a>
          <button class="btn btn-sm btn-danger" onclick="potvrdiBrisanje(<?= $k['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
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
    <p>Jeste li sigurni da želite obrisati ovu kategoriju?</p>
    <form method="post" action="/kategorije/obrisi" style="margin-top: 20px;">
      <input type="hidden" name="id" id="id-brisanja">
      <div style="text-align: center;">
        <button type="button" class="btn btn-secondary" onclick="zatvoriModal()">Otkaži</button>
        <bu