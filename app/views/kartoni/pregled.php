<div class="naslov-dugme">
<h2><?= htmlspecialchars($title) ?></h2>
<?php if ($user['uloga'] === 'pacijent'): ?>
    <a href="/dashboard" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Povratak na dashboard
    </a>
<?php else: ?>
    <a href="/kartoni/lista" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Povratak
    </a>
<?php endif; ?>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'ureden'): ?>
    <div class="alert alert-success">✅ Karton je uspješno ažuriran.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-danger">❌ Greška pri ažuriranju kartona.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'jmbg_postoji'): ?>
    <div class="alert alert-warning">⚠️ JMBG već postoji u sistemu!</div>
<?php endif; ?>

<div class="main-content-fw">
  <div class="card-wrapper">
    <div class="card-head">
        <h3><i class="fa fa-user"></i> Pacijent: <?= htmlspecialchars($karton['ime'] ?? '') ?> <?= htmlspecialchars($karton['prezime'] ?? '') ?></h3>
        <div class="card-head-buttons">
            <?php if ($user['uloga'] !== 'pacijent'): ?>
                <a href="/kartoni/uredi?id=<?= $karton['id'] ?>" class="btn btn-edit-yellow">Uredi podatke</a>
            <?php endif; ?>
            
            <?php if ($user['uloga'] === 'pacijent' && hasPermission($user, 'pregled_vlastiti_tretmani')): ?>
                <a href="/kartoni/tretmani?id=<?= $karton['id'] ?>" class="btn btn btn-add">Moji tretmani</a>
            <?php elseif ($user['uloga'] !== 'pacijent'): ?>
                <a href="/kartoni/tretmani?id=<?= $karton['id'] ?>" class="btn btn btn-add">Tretmani pacijenta</a>
            <?php endif; ?>
            
            <?php if ($user['uloga'] === 'pacijent' && hasPermission($user, 'pregled_vlastiti_nalazi')): ?>
                <a href="/kartoni/nalazi?id=<?= $karton['id'] ?>" class="btn btn btn-add">Moji nalazi</a>
            <?php elseif ($user['uloga'] !== 'pacijent'): ?>
                <a href="/kartoni/nalazi?id=<?= $karton['id'] ?>" class="btn btn btn-add">Nalazi pacijenta</a>
            <?php endif; ?>
            
            <?php if (hasPermission($user, 'print_vlastiti_podaci') || in_array($user['uloga'], ['admin', 'recepcioner', 'terapeut'])): ?>
                <a href="/kartoni/print-karton?id=<?= $karton['id'] ?>" class="btn btn-print" target="_blank">
                    <i class="fa-solid fa-print"></i> Print/PDF
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-block">
        <label>Ime i prezime</label> 
        <div class="card-info"><?= htmlspecialchars($karton['ime'] ?? '') ?> <?= htmlspecialchars($karton['prezime']  ?? '') ?></div>
    </div>
    <div class="card-block">
        <label>Datum rođenja:</label>
        <div class="card-info">
            <?= date('d.m.Y', strtotime($karton['datum_rodjenja'])) ?>
        </div>
        <p>Spol:</p> 
        <div class="card-info-no-border">
            <div class="card-info-no-border"><?= htmlspecialchars($karton['spol'] ?? '') ?></div>
        </div>
    </div>
    <div class="card-block">
        <label>Adresa:</label>
        <div class="card-info">
            <?= htmlspecialchars($karton['adresa'] ?? '') ?>
        </div>
    </div>
    <div class="card-block">
        <label>Telefon:</label>
        <div class="card-info">
            <?= htmlspecialchars($karton['telefon'] ?? '') ?>
        </div>
        <p>E-mail:</p>
        <div class="card-info">
            <?= htmlspecialchars($karton['email'] ?? '') ?>
        </div>
    </div>
    <div class="card-block">
        <label>JMBG:</label>
        <div class="card-info">
            <?= htmlspecialchars($karton['jmbg'] ?? '') ?>
        </div>
        <p>Broj upisa:</p>
        <div class="card-info bg-grey">
            <?= htmlspecialchars($karton['broj_upisa'] ?? '') ?>
        </div>
    </div>
    <div class="card-block cb-top">
        <label>Anamneza:</label>
        <div class="card-info-fw">
            <?= htmlspecialchars($karton['anamneza'] ?? '') ?>
        </div>
    </div>
    <div class="card-block cb-top">
        <label>Dijagnoza:</label>
        <div class="card-info-fw">
            <?php
            // Dohvati dijagnoze povezane s ovim kartonom
            $stmt_dijagnoze = $pdo->prepare("
                SELECT d.naziv 
                FROM karton_dijagnoze kd
                LEFT JOIN dijagnoze d ON kd.dijagnoza_id = d.id
                WHERE kd.karton_id = ?
            ");
            $stmt_dijagnoze->execute([$karton['id']]);
            $dijagnoze = $stmt_dijagnoze->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($dijagnoze)) {
                echo htmlspecialchars(implode(', ', $dijagnoze));
            } else {
                echo '<em>Nema dodanih dijagnoza</em>';
            }
            ?>
        </div>
    </div>
    <div class="card-block cb-top">
        <label>Rehabilitacija:</label>
        <div class="card-info-fw">
            <?= htmlspecialchars($karton['rehabilitacija'] ?? '') ?>
        </div>
    </div>
    <div class="card-block cb-top">
        <label>Početna procjena:</label>
        <div class="card-info-fw">
            <?= htmlspecialchars($karton['pocetna_procjena'] ?? '') ?>
        </div>
    </div>
    
    <?php if ($user['uloga'] !== 'pacijent'): ?>
    <div class="card-block cb-top">
        <label>Bilješke:</label>
        <div class="card-info-fw">
            <?= htmlspecialchars($karton['biljeske'] ?? '') ?>
        </div>
    </div>
    <div class="card-block cb-top">
        <label>Napomena:</label>
        <div class="card-info-fw">
            <?= htmlspecialchars($karton['napomena'] ?? '') ?>
        </div>
    </div>
    <?php endif; ?>
  </div>
    
  <?php if ($user['uloga'] === 'pacijent'): ?>
  <div class="bottom-foot"> 
    <a href="/kartoni/tretmani?id=<?= $karton['id'] ?>" class="btn btn btn-add">Pogledaj moje tretmane</a>
    <a href="/kartoni/nalazi?id=<?= $karton['id'] ?>" class="btn btn btn-add">Pogledaj moje nalaze</a>
  </div>
  <?php else: ?>
  <div class="bottom-foot"> 
    <a href="/kartoni/tretmani?id=<?= $karton['id'] ?>" class="btn btn btn-add">Pogledaj tretmane pacijenta</a>
  </div>
  <?php endif; ?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const redovi = document.querySelectorAll('.tretman-red');
    redovi.forEach((red, index) => {
      red.addEventListener('click', () => {
        const detalji = red.nextElementSibling;
        detalji.style.display = detalji.style.display === 'table-row' ? 'none' : 'table-row';
      });
    });
  });
</script>