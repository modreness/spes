<h2><?= htmlspecialchars($title) ?></h2>
<div class="main-content-fw">
<table id="tabela" class="table-standard">
  <thead>
    <tr>
      <th>Ime i prezime</th>
      <th>Email</th>
      <th>Korisničko ime</th>
      <?php if ($rola === 'pacijent'): ?>
      <th>Karton</th>
      <?php endif; ?>
      <th>Posljednja prijava</th>
      <th>Akcije</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($korisnici as $k): ?>
      <tr>
        <td><?= htmlspecialchars($k['ime']) ?> <?= htmlspecialchars($k['prezime']) ?></td>
        <td><?= htmlspecialchars($k['email'] ?? '') ?></td>
        <td><?= htmlspecialchars($k['username'] ?? '') ?></td>
        <?php if ($rola === 'pacijent'): ?>
        <td>
          <?php if (!empty($k['ima_karton'])): ?>
            <a href="/kartoni/pregled?id=<?= $k['karton_id'] ?>" 
               style="display: inline-flex; align-items: center; gap: 5px; background: #27ae60; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; text-decoration: none;">
              <i class="fa-solid fa-folder-open"></i> Da
            </a>
          <?php else: ?>
            <span style="display: inline-flex; align-items: center; gap: 5px; background: #e74c3c; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
              <i class="fa-solid fa-folder"></i> Ne
            </span>
          <?php endif; ?>
        </td>
        <?php endif; ?>
        <td><?= $k['last_login'] ? date('d.m.Y. H:i', strtotime($k['last_login'])) : 'Nema podataka'; ?></td>
        <td>
          <?php if ($k['id'] != $user['id']): ?>
            <?php if (in_array($user['uloga'], ['admin', 'recepcioner'])): ?>
              <a href="/profil/uredi?id=<?= $k['id'] ?>" class="btn btn-sm btn-edit">Uredi</a>
              <button class="btn btn-sm btn-danger" onclick="potvrdiBrisanje(<?= $k['id'] ?>)">Obriši</button>
            <?php endif; ?>
          <?php else: ?>
            <span class="text-muted">Tvoj nalog</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<!-- Overlay -->
<div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

<!-- Modal za potvrdu brisanja -->
<div id="brisanje-modal" class="modal" style="display:none;">
  <div class="modal-content">
    <p>Jeste li sigurni da želite obrisati ovaj profil?</p>
    <form method="post" action="/profil/obrisi" style="margin-top: 20px;">
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