<h2><?= htmlspecialchars($title) ?></h2>

<div class="main-content-fw">
  <div class="card-wrapper">
    <div class="card-head"><h3><i class="fa fa-user"></i> Pacijent: <?= htmlspecialchars($karton['ime'] ?? '') ?> <?= htmlspecialchars($karton['prezime'] ?? '') ?></h3>
        <div>
        <a href="/kartoni/uredi?id=<?= $karton['id'] ?>" class="btn btn-edit">Uredi podatke</a>
        <a href="/kartoni/tretmani?id=<?= $karton['id'] ?>" class="btn btn-sm btn-add">Tretmani pacijenta</a>
        <a href="/kartoni/nalazi?id=<?= $karton['id'] ?>" class="btn btn-sm btn-add">Nalazi pacijenta</a>
        <a href="/kartoni/print-karton?id=<?= $karton['id'] ?>" class="btn btn-print" target="_blank"><i class="fa-solid fa-print"></i> Print/PDF</a>
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
        <div class="card-info bg-yellow">
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
        <label>Dijagonoza:</label>
        <div class="card-info-fw">
            <?= htmlspecialchars($karton['dijagnoza'] ?? '') ?>
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
    <!-- Dodaj još polja ako želiš -->
  </div>
    
    
  <div class="bottom-foot"> 
  <a href="/kartoni/tretmani?id=<?= $karton['id'] ?>" class="btn btn-sm btn-add">Pogledaj karton pacijenta</a>
    </div> 
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
