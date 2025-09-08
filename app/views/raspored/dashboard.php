<div class="naslov-dugme">
    <h2>Raspored terapeuta</h2>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'dodan'): ?>
    <div class="alert alert-success">Raspored je uspješno dodan.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-warning">Greška pri dodavanju rasporeda.</div>
<?php endif; ?>

<div class="main-content">
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Ukupno terapeuta</h3>
            <div class="stat-number"><?= $broj_terapeuta ?></div>
        </div>
        <div class="stat-card">
            <h3>Raspoređeno ove sedmice</h3>
            <div class="stat-number"><?= $rasporedeni_terapeuti ?></div>
        </div>
    </div>

    <div class="action-cards">
        <div class="action-card">
            <h3>Dodaj novi raspored</h3>
            <p>Kreiraj sedmični raspored za terapeute</p>
            <a href="/raspored/dodaj" class="btn btn-add">
                <i class="fa-solid fa-plus"></i> Dodaj raspored
            </a>
        </div>
        
        <div class="action-card">
            <h3>Pregled rasporeda</h3>
            <p>Pogledaj postojeće rasporede terapeuta</p>
            <a href="/raspored/pregled" class="btn btn-primary">
                <i class="fa-solid fa-calendar"></i> Pregled rasporeda
            </a>
        </div>
    </div>
</div>