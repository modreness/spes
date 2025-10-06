<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
}
.header {
    text-align: right;
    border-bottom: 1px solid #ccc;
    padding-bottom: 10px;
    margin-bottom: 10px;
    clear:both;
    min-height:100px;
}
.logo {
            width: 90px;
            float:left;
            clear:both;
        }
.tabela {
    width: 100%;
    border-collapse: collapse;
    margin-top:20px;
}
.tabela th, .tabela td {
    border: 1px solid #000;
    padding: 4px;
    text-align: left;
}
.tabela th {
    background-color: #f0f0f0;
}
.tabela th:first-child{
    width: 15%;
}
.tabela th:last-child{
    width: 13%;
}
.footer-info {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
}
.naslov {
    font-size:14px;
    text-align: center;
    padding:0px;
    margin:0px;
    line-height:1;
}
</style>
</head>
<body>
<div class="header">
    <div class="header-left">
    <img class="logo" src="https://app.spes.ba/assets/images/spes-logo-slogan.png" alt="SPES logo">
    </div>
<div class="header-right">
    <p><strong>Pacijent:</strong> <?= htmlspecialchars($karton['ime'] . ' ' . $karton['prezime']) ?></p>
    <p><strong>JMBG:</strong> <?= htmlspecialchars($karton['jmbg']) ?></p>
    <p><strong>Broj kartona:</strong> <?= htmlspecialchars($karton['broj_upisa']) ?></p>
</div>
</div>
<div class="naslov">
    <h2>Lista tretmana</h2>
<table class="tabela">
    <thead>
        <tr>
            <th>Datum</th>
            <th>Stanje prije</th>
            <th>Terapija</th>
            <th>Terapeut</th>
            <th>Unio</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tretmani as $t): ?>
        <tr>
            <td><?= date('d.m.Y. H:i', strtotime($t['datum'])) ?></td>
            <td><?= htmlspecialchars($t['stanje_prije']) ?></td>
            <td><?= htmlspecialchars($t['terapija']) ?></td>
            <td><?= htmlspecialchars($t['terapeut_ime'] . ' ' . $t['terapeut_prezime']) ?></td>
            <td><?= htmlspecialchars($t['unio_ime'] . ' ' . $t['unio_prezime']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="footer-info">
        Šenoina 5, Sarajevo |
        Bosna i Hercegovina 71000 |
        +387 (0) 63 116 833 |
        privatna.praksa.spes@gmail.com |
        www.spes.ba
    </div>
</body>
</html>