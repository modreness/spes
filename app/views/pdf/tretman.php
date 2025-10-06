<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.6; }

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

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 16px;
        }

        .section {
            margin-bottom: 15px;
        }

        .label {
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 60px;
            width: 100%;
            text-align: center;
            font-size: 12px;
        }

        .potpis {
            margin-top: 80px;
            text-align: right;
            padding-right: 50px;
        }

        .footer-info {
            font-size: 10px;
            text-align: center;
            position: fixed;
            bottom: 20px;
            width: 100%;
            color: #777;
        }
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

<h2>Tretman pacijenta</h2>

<div class="section">
    <div><span class="label">Pacijent:</span> <?= htmlspecialchars($tretman['pacijent_ime']) . ' ' . htmlspecialchars($tretman['pacijent_prezime']) ?></div>
    <div><span class="label">Broj kartona:</span> <?= htmlspecialchars($tretman['broj_upisa']) ?></div>
    <div><span class="label">Datum tretmana:</span> <?= date('d.m.Y H:i', strtotime($tretman['datum'])) ?></div>
    <div><span class="label">Unio:</span> <?= htmlspecialchars($tretman['unio_ime']) . ' ' . htmlspecialchars($tretman['unio_prezime']) ?></div>
</div>

<div class="section">
    <div class="label">Stanje prije:</div>
    <div><?= nl2br(htmlspecialchars($tretman['stanje_prije'])) ?></div>
</div>

<div class="section">
    <div class="label">Terapija:</div>
    <div><?= nl2br(htmlspecialchars($tretman['terapija'])) ?></div>
</div>

<div class="section">
    <div class="label">Stanje poslije:</div>
    <div><?= nl2br(htmlspecialchars($tretman['stanje_poslije'])) ?></div>
</div>

<div class="section">
    <div class="label">Terapeut:</div>
    <div><?= htmlspecialchars($tretman['terapeut_ime']) . ' ' . htmlspecialchars($tretman['terapeut_prezime']) ?></div>
</div>

<div class="potpis">
    ____________________________<br>
    <em>Pečat i potpis</em>
</div>

<div class="footer-info">
    SPES Privatna praksa | Šenoina 5, Sarajevo | www.spes.ba
</div>

</body>
</html>
