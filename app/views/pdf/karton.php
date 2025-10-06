<!DOCTYPE html>
<html lang="bs">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    .header {
            display: block;
            width:100%;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
            clear:both;
            min-height:140px;
         }
    .logo {
            width: 120px;
            float:left;
            clear:both;
        }

        .ustanova {
            float:right;
            text-align: right;
            font-size: 11px;
            clear:both;
        }
    .section { margin-bottom: 15px; }
    .label { font-weight: bold; }
  </style>
</head>
<body>

<div class="header">
    <div class="header-left">
    <img class="logo" src="https://app.spes.ba/assets/images/spes-logo-slogan.png" alt="SPES logo">
    </div>
    <div class="header-right">
    <div class="ustanova">
        <strong>SPES Privatna praksa</strong><br>
        Šenoina 5, Sarajevo<br>
        Bosna i Hercegovina 71000<br>
        +387 (0) 63 116 833<br>
        privatna.praksa.spes@gmail.com<br>
        www.spes.ba
    </div>
    </div>
</div>

<h2>Karton pacijenta</h2>

<div class="section">
  <div><span class="label">Pacijent:</span> <?= htmlspecialchars($karton['ime'] . ' ' . $karton['prezime']) ?></div>
  <div><span class="label">JMBG:</span> <?= htmlspecialchars($karton['jmbg']) ?></div>
  <div><span class="label">Broj upisa:</span> <?= htmlspecialchars($karton['broj_upisa']) ?></div>
  <div><span class="label">Telefon:</span> <?= htmlspecialchars($karton['telefon']) ?></div>
  <div><span class="label">Adresa:</span> <?= htmlspecialchars($karton['adresa']) ?></div>
  <div><span class="label">Spol:</span> <?= htmlspecialchars($karton['spol']) ?></div>
  <div><span class="label">Datum rođenja:</span> <?= htmlspecialchars($karton['datum_rodjenja']) ?></div>
</div>

<div class="section">
  <div class="label">Anamneza:</div>
  <div><?= nl2br(htmlspecialchars($karton['anamneza'])) ?></div>
</div>

<div class="section">
  <div class="label">Dijagnoza:</div>
  <div><?= nl2br(htmlspecialchars($karton['dijagnoza'])) ?></div>
</div>

<div class="section">
  <div class="label">Početna procjena:</div>
  <div><?= nl2br(htmlspecialchars($karton['pocetna_procjena'])) ?></div>
</div>

<div class="section">
  <div class="label">Bilješke:</div>
  <div><?= nl2br(htmlspecialchars($karton['biljeske'])) ?></div>
</div>

<br><br>
<div style="margin-top: 80px; text-align: right;">
  ___________________________<br>
  Pečat i potpis
</div>

</body>
</html>
