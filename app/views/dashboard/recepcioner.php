
<h3>Recepcioner Dashboard</h3>
<ul>
  <li><a href="/dodavanje-rasporeda">Dodavanje rasporeda</a></li>
  <li><a href="/pregled-rasporeda">Pregled rasporeda</a></li>
  <li><a href="/dodavanje-termina">Dodavanje termina</a></li>
  <li><a href="/unos-terapije">Unos terapije</a></li>
</ul>

<p>Dobrodošli, <strong><?= htmlspecialchars($user['ime_prezime']) ?></strong>!</p>
<p>Ovdje možete upravljati sedmičnim rasporedima terapeuta, zakazivati termine pacijentima i unositi njihove terapijske protokole.</p>

<section class="cards">
  <div class="card">Unos rasporeda terapeuta</div>
</section>

<section class="layout-grid">
  <div class="termini">
    <h3>Dodavanje rasporeda za sedmicu</h3>
    <?php if (isset($_GET['status'])): ?>
      <p style="background: #d9edf7; color: #31708f; padding: 10px; border-radius: 6px;">
        <?php if ($_GET['status'] === 'added'): ?>
          ✅ Raspored je uspješno dodan.
        <?php elseif ($_GET['status'] === 'updated'): ?>
          ✏️ Raspored je ažuriran.
        <?php else: ?>
          ❗ Greška prilikom dodavanja rasporeda.
        <?php endif; ?>
      </p>
    <?php endif; ?>

    <form action="/raspored" method="post" class="rasporedi-table">
      <table>
        <tr>
          <td>Terapeut</td>
          <td>
            <select name="terapeut_id">
              <?php
              $terapeuti = $pdo->query("SELECT id, ime_prezime FROM users WHERE uloga = 'terapeut'")->fetchAll(PDO::FETCH_ASSOC);
              foreach ($terapeuti as $t): ?>
                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['ime_prezime']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <td>Datum početka sedmice (ponedjeljak):</td>
          <td><input type="date" name="datum_od"></td>
        </tr>
      </table>

      <table>
        <thead>
          <tr><th>Dan</th><th>Smjena</th></tr>
        </thead>
        <tbody>
          <?php foreach (dani() as $key => $label): ?>
          <tr>
            <td><?= $label ?></td>
            <td>
              <select name="raspored[<?= $key ?>][smjena]">
                <option value="">Ne radi</option>
                <?php foreach (smjene() as $smjenaKey => $smjenaLabel): ?>
                  <option value="<?= $smjenaKey ?>"><?= $smjenaLabel ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <input type="submit" name="snimi" value="Snimi raspored">
    </form>
  </div>
</section>

<section style="margin-top: 40px;">
  <h3>Pregled rasporeda po danima i smjenama</h3>

  <?php
  $datum_od = $_GET['filter_datum_od'] ?? date('Y-m-d');
  $start_date = new DateTime($datum_od);
  $end_date = (clone $start_date)->modify('+6 days');

  $rasporedi = db()->prepare("
    SELECT r.*, u.ime_prezime AS terapeut_ime
    FROM rasporedi_sedmicni r
    JOIN users u ON r.terapeut_id = u.id
    WHERE r.datum_od BETWEEN :od AND :do
    ORDER BY r.datum_od ASC, FIELD(r.smjena, 'jutro','popodne','vecer')
  ");
  $rasporedi->execute([
    'od' => $start_date->format('Y-m-d'),
    'do' => $end_date->format('Y-m-d')
  ]);
  $data = $rasporedi->fetchAll(PDO::FETCH_ASSOC);

  $raspored_po_danu = [];
  foreach ($data as $r) {
    $dan = $r['dan'];
    $smjena = $r['smjena'];
    $raspored_po_danu[$dan][$smjena][] = $r['terapeut_ime'];
  }
  ?>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
    <?php foreach (dani() as $slug => $label): ?>
      <div class="day-block" style="background:#fff; padding:20px; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.05);">
        <h4><?= $label ?> (<?= date('d.m.Y', strtotime("+".array_search($slug, array_keys(dani()))." days", strtotime($datum_od))) ?>)</h4>

        <?php foreach (smjene() as $key => $naziv): ?>
          <div class="smjena-block" style="margin-bottom:12px;">
            <strong>🕒 <?= $naziv ?>:</strong><br>
            <?php if (!empty($raspored_po_danu[$slug][$key])): ?>
              <ul style="padding-left:18px;">
                <?php foreach ($raspored_po_danu[$slug][$key] as $ime): ?>
                  <li><?= htmlspecialchars($ime) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <em style="color:#777;">Nema terapeuta</em>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
