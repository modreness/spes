<div class="naslov-dugme">
    <h2>Raspored terapeuta</h2>
    <?php if ($user['uloga'] === 'terapeut'): ?>
        <span style="color: #7f8c8d; font-size: 0.9em;">Moj raspored</span>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'dodan'): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-check-circle"></i>
        <strong>Uspješno!</strong> Dodano je <?= (int)($_GET['dodano'] ?? 1) ?> raspored<?= (int)($_GET['dodano'] ?? 1) > 1 ? 'a' : '' ?>.
    </div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'dodano_delimicno'): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-exclamation-triangle"></i>
        <strong>Djelomično uspješno!</strong><br>
        ✅ Dodano: <?= (int)($_GET['dodano'] ?? 0) ?> raspored<?= (int)($_GET['dodano'] ?? 0) > 1 ? 'a' : '' ?><br>
        ⚠️ Preskočeno (već postoji): <?= (int)($_GET['preskoceno'] ?? 0) ?> raspored<?= (int)($_GET['preskoceno'] ?? 0) > 1 ? 'a' : '' ?>
    </div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'duplikat'): ?>
    <div class="alert alert-warning">
        <i class="fa-solid fa-info-circle"></i>
        <strong>Nijedan raspored nije dodan.</strong><br>
        Svi odabrani rasporedi (<?= (int)($_GET['preskoceno'] ?? 0) ?>) već postoje u sistemu.
    </div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'nista'): ?>
    <div class="alert alert-warning">
        <i class="fa-solid fa-exclamation-triangle"></i>
        <strong>Opomena!</strong> Nijedan raspored nije odabran za dodavanje.
    </div>
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
        
        <!-- NOVO: Dodano dugme za uređivanje -->
        <div class="action-card">
            <h3>Uredi postojeće rasporede</h3>
            <p>Upravljaj postojećim rasporedima terapeuta</p>
            <a href="/raspored/uredi" class="btn btn-primary">
                <i class="fa-solid fa-edit"></i> Uredi rasporede
            </a>
        </div>
        <?php endif; ?>
        
        <div class="action-card">
            <h3><?= $user['uloga'] === 'terapeut' ? 'Moj raspored' : 'Pregled rasporeda' ?></h3>
            <p><?= $user['uloga'] === 'terapeut' ? 'Pogledaj svoj radni raspored' : 'Pogledaj postojeće rasporede terapeuta' ?></p>
            <a href="/raspored/pregled" class="btn btn-success">
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