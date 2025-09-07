<h1>Dobrodošli, <?= htmlspecialchars($user['ime']) ?>!</h1>
<p>Uloga: <?= htmlspecialchars($user['uloga']) ?></p>

<p>Ovdje će terapeut vidjeti svoj raspored termina i pacijente.</p>

<a href="/logout">Odjava</a>