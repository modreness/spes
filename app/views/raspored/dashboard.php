<div class="naslov-dugme">
    <h2>Raspored terapeuta</h2>
    <?php if ($user['uloga'] === 'terapeut'): ?>
        <span style="color: #7f8c8d; font-size: 0.9em;">Moj raspored</span>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'dodan'): ?>
    <div class="alert alert-success">Raspored je uspješno dodan.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'greska'): ?>
    <div class="alert alert-warning">Greška pri dodavanju rasporeda.</div>
<?php endif; ?>

<div class="main-content">
    <?php if ($user['uloga'] !== 'terapeut'): ?>
    <!-- Statistike samo za admin/recepcioner -->
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
    <?php endif; ?>

    <div class="action-cards">
        <?php if (in_array($user['uloga'], ['admin', 'recepcioner'])): ?>
        <!-- Admin i recepcioner mogu dodavati raspored -->
        <div class="action-card">
            <h3>Dodaj novi raspored</h3>
            <p>Kreiraj sedmični raspored za terapeute</p>
            <a href="/raspored/dodaj" class="btn btn-add">
                <i class="fa-solid fa-plus"></i> Dodaj raspored
            </a>
        </div>
        <?php endif; ?>
        
        <div class="action-card">
            <h3><?= $user['uloga'] === 'terapeut' ? 'Moj raspored' : 'Pregled rasporeda' ?></h3>
            <p><?= $user['uloga'] === 'terapeut' ? 'Pogledaj svoj radni raspored' : 'Pogledaj postojeće rasporede terapeuta' ?></p>
            <a href="/raspored/pregled" class="btn btn-primary">
                <i class="fa-solid fa-calendar"></i> <?= $user['uloga'] === 'terapeut' ? 'Moj raspored' : 'Pregled rasporeda' ?>
            </a>
        </div>
        
        <?php if ($user['uloga'] === 'terapeut'): ?>
        <!-- Dodatne opcije za terapeuta -->
        <div class="action-card">
            <h3>Moji termini</h3>
            <p>Pregled mojih termina i kalendar</p>
            <a href="/termini" class="btn btn-success">
                <i class="fa-solid fa-calendar-days"></i> Termini
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>