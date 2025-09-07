<h1>Dobrodošli, <?= htmlspecialchars($user['ime']) ?>!</h1>
<p>Uloga: <?= htmlspecialchars($user['uloga']) ?></p>

<p>Ovdje će pacijent moći vidjeti svoj karton, zakazane termine i eventualne nalaze.</p>

<a href="/logout">Odjava</a>