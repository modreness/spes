<?php
require_once __DIR__ . '/../helpers/auth.php';
$user = current_user();
$current_page = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'SPES') ?></title>
    <link rel="stylesheet" href="/public/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-heartbeat"></i> SPES</h2>
            <div class="user-info">
                <span><?= htmlspecialchars($user['ime'] . ' ' . $user['prezime']) ?></span>
                <small><?= htmlspecialchars(ucfirst($user['uloga'])) ?></small>
            </div>
        </div>

        <div class="sidebar-menu">
            <!-- DASHBOARD - dostupan svima -->
            <a href="/dashboard" class="menu-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>

            <?php if (in_array($user['uloga'], ['admin', 'recepcioner'])): ?>
            <!-- PACIJENTI - admin i recepcioner -->
            <a href="/kartoni" class="menu-item <?= in_array($current_page, ['kartoni', 'karton']) ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Kartoni pacijenata</span>
            </a>
            <?php endif; ?>

            <?php if (in_array($user['uloga'], ['admin', 'recepcioner', 'terapeut'])): ?>
            <!-- TERMINI - svi osim pacijenata -->
            <a href="/termini" class="menu-item <?= in_array($current_page, ['termini', 'kalendar']) ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Termini</span>
            </a>
            <?php endif; ?>

            <?php if ($user['uloga'] === 'pacijent'): ?>
            <!-- MOJI TERMINI - samo pacijenti -->
            <a href="/moji-termini" class="menu-item <?= $current_page === 'moji-termini' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Moji termini</span>
            </a>
            <?php endif; ?>

            <?php if (in_array($user['uloga'], ['admin', 'recepcioner'])): ?>
            <!-- RASPORED - admin i recepcioner -->
            <a href="/raspored" class="menu-item <?= $current_page === 'raspored' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i>
                <span>Raspored</span>
            </a>
            <?php endif; ?>

            <?php if ($user['uloga'] === 'admin'): ?>
            <!-- ADMIN OPCIJE -->
            <div class="menu-group">
                <div class="group-label">Administracija</div>
                
                <a href="/users" class="menu-item <?= $current_page === 'users' ? 'active' : '' ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Korisnici</span>
                </a>
                
                <a href="/kategorije" class="menu-item <?= $current_page === 'kategorije' ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i>
                    <span>Kategorije</span>
                </a>
                
                <a href="/cjenovnik" class="menu-item <?= $current_page === 'cjenovnik' ? 'active' : '' ?>">
                    <i class="fas fa-money-bill"></i>
                    <span>Cjenovnik</span>
                </a>
                
                <a href="/timetable" class="menu-item <?= $current_page === 'timetable' ? 'active' : '' ?>">
                    <i class="fas fa-business-time"></i>
                    <span>Radna vremena</span>
                </a>
                
                <a href="/izvjestaji" class="menu-item <?= $current_page === 'izvjestaji' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Izvještaji</span>
                </a>
            </div>
            <?php endif; ?>

            <?php if (in_array($user['uloga'], ['admin', 'recepcioner'])): ?>
            <!-- IZVJEŠTAJI - admin i recepcioner -->
            <?php if ($user['uloga'] !== 'admin'): // ako nije admin, jer admin već ima gore ?>
            <a href="/izvjestaji" class="menu-item <?= $current_page === 'izvjestaji' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Izvještaji</span>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <!-- PRETRAGA - svi -->
            <a href="/pretraga" class="menu-item <?= $current_page === 'pretraga' ? 'active' : '' ?>">
                <i class="fas fa-search"></i>
                <span>Pretraga</span>
            </a>

            <!-- PROFIL - svi -->
            <a href="/profil" class="menu-item <?= $current_page === 'profil' ? 'active' : '' ?>">
                <i class="fas fa-user"></i>
                <span>Moj profil</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <a href="/logout" class="menu-item logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Odjava</span>
            </a>
        </div>
    </nav>

    <main class="main-content">
        <header class="content-header">
            <h1><?= htmlspecialchars($title ?? 'SPES') ?></h1>
            <div class="header-actions">
                <!-- Brzа pretragа -->
                <form action="/pretraga" method="GET" class="quick-search">
                    <input type="text" name="q" placeholder="Brza pretraga..." class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <!-- Notifikacije -->
                <div class="notifications">
                    <i class="fas fa-bell notification-icon"></i>
                    <span class="notification-count">0</span>
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            <?= $content ?? '' ?>
        </div>
    </main>

    <script src="/public/js/dashboard.js"></script>
</body>
</html>