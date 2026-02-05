<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Dashboard' ?></title>
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />



</head>
<body>
<!-- Mobile Menu Button -->
<button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Menu">
    <i class="fa-solid fa-bars"></i>
</button>
<div class="sidebar-overlay" onclick="toggleMobileMenu()"></div>

<div class="dashboard">

  <aside class="sidebar">
    <div class="logo">
      <a href="/"><img src="/assets/images/spes-logo-slogan.svg" alt="SPES"></a>
    </div>
    <nav class="sidebar-nav">
    <h2 class="menu-title">IZBORNIK</h2>
      
        <?php 
        // Trenutni URI za aktivnu stavku
        $current_uri = $_SERVER['REQUEST_URI'];
        $current_path = parse_url($current_uri, PHP_URL_PATH);
        ?>
        
        <?php if ($user['uloga'] === 'admin'): ?>
        <ul class="menu-list">
          <li class="dropdown">
              <a href="#" class="dropdown-toggle">
                <i class="fa-solid fa-user-pen"></i> Pregled profila
                <span class="arrow">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </span>
              </a>
              <ul class="submenu">
                <li><a href="/profil/pacijent" class="<?= strpos($current_path, '/profil/pacijent') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-user"></i> Pacijent</a></li>
                <li><a href="/profil/terapeut" class="<?= strpos($current_path, '/profil/terapeut') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-user-doctor"></i> Terapeut</a></li>
                <li><a href="/profil/recepcioner" class="<?= strpos($current_path, '/profil/recepcioner') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-user-tie"></i> Recepcioner</a></li>
                <li><a href="/profil/admin" class="<?= strpos($current_path, '/profil/admin') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-user-shield"></i> Admin</a></li>
                <li><a href="/profil/kreiraj" class="<?= strpos($current_path, '/profil/kreiraj') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-user-plus"></i> Kreiraj profil</a></li>
              </ul>
            </li>

          <li><a href="/kartoni/lista" class="<?= strpos($current_path, '/kartoni') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-folder-open"></i> Kartoni</a></li>
          <li><a href="/pretraga" class="<?= strpos($current_path, '/pretraga') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-magnifying-glass"></i> Pretraga</a></li>
          <li><a href="/izvjestaji" class="<?= strpos($current_path, '/izvjestaji') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Izvještaji</a></li>
          <li><a href="/kategorije" class="<?= strpos($current_path, '/kategorije') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-tags"></i> Kategorije</a></li>
          <li><a href="/cjenovnik" class="<?= strpos($current_path, '/cjenovnik') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-dollar-sign"></i> Cjenovnik</a></li>
          <li><a href="/paketi" class="<?= strpos($current_path, '/paketi') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-box"></i> Paketi</a></li>
          <li><a href="/dijagnoze" class="<?= strpos($current_path, '/dijagnoze') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-notes-medical"></i> Dijagnoze</a></li>
          <li><a href="/timetable" class="<?= strpos($current_path, '/timetable') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-clock"></i> Timetable</a></li>
          <li><a href="/raspored" class="<?= strpos($current_path, '/raspored') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-calendar-days"></i> Raspored terapeuta</a></li>
          
        </ul>
        
        <div class="zakazani-link">
        <a href="/termini" class="<?= strpos($current_path, '/termini') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check"></i> ZAKAZANI TERMINI</a>
        </div>
         
        <?php elseif ($user['uloga'] === 'recepcioner'): ?>
        <ul class="menu-list">
          <li class="dropdown">
              <a href="#" class="dropdown-toggle">
                <i class="fa-solid fa-user-pen"></i> Pregled profila
                <span class="arrow">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </span>
              </a>
              <ul class="submenu">
                <li><a href="/profil/pacijent" class="<?= strpos($current_path, '/profil/pacijent') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-user"></i> Pacijent</a></li>
                <li><a href="/profil/terapeut" class="<?= strpos($current_path, '/profil/terapeut') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-user-doctor"></i> Terapeut</a></li> 
                <li><a href="/profil/kreiraj" class="<?= strpos($current_path, '/profil/kreiraj') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-user-plus"></i> Kreiraj profil</a></li>
              </ul>
            </li>

          <li><a href="/kartoni/lista" class="<?= strpos($current_path, '/kartoni') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-folder-open"></i> Kartoni</a></li>
          <li><a href="/pretraga" class="<?= strpos($current_path, '/pretraga') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-magnifying-glass"></i> Pretraga</a></li>
          <li><a href="/izvjestaji/operativni" class="<?= strpos($current_path, '/izvjestaji/operativni') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Operativni izvještaj</a></li>
          <li><a href="/izvjestaji/medicinski" class="<?= strpos($current_path, '/izvjestaji/medicinski') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Medicinski izvještaj</a></li>
          <li><a href="/kategorije" class="<?= strpos($current_path, '/kategorije') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-tags"></i> Kategorije</a></li>
          <li><a href="/cjenovnik" class="<?= strpos($current_path, '/cjenovnik') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-dollar-sign"></i> Cjenovnik</a></li>
          <li><a href="/paketi" class="<?= strpos($current_path, '/paketi') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-box"></i> Paketi</a></li>
          <li><a href="/dijagnoze" class="<?= strpos($current_path, '/dijagnoze') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-notes-medical"></i> Dijagnoze</a></li>
          <li><a href="/timetable" class="<?= strpos($current_path, '/timetable') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-clock"></i> Timetable</a></li>
          <li><a href="/raspored" class="<?= strpos($current_path, '/raspored') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-calendar-days"></i> Raspored terapeuta</a></li>
          
        </ul>

        <div class="zakazani-link">
        <a href="/termini" class="<?= strpos($current_path, '/termini') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check"></i> ZAKAZANI TERMINI</a>
        </div>

        <?php elseif ($user['uloga'] === 'terapeut'): ?>
        <ul class="menu-list">
          <li><a href="/dashboard" class="<?= $current_path === '/dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a></li>
          <li><a href="/pretraga" class="<?= $current_path === '/pretraga' ? 'active' : '' ?>"><i class="fa-solid fa-magnifying-glass"></i> Pretraga</a></li>
          <li><a href="/kartoni/lista" class="<?= $current_path === '/kartoni/lista' ? 'active' : '' ?>"><i class="fa-solid fa-folder-open"></i> Kartoteka</a></li>
          <li><a href="/raspored/moj" class="<?= $current_path === '/raspored/moj' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-days"></i> Moj raspored</a></li>
          <li><a href="/kartoni/moji" class="<?= $current_path === '/kartoni/moji' ? 'active' : '' ?>"><i class="fa-solid fa-user"></i> Moji pacijenti</a></li>
          <li><a href="/tretmani/moji" class="<?= $current_path === '/tretmani/moji' ? 'active' : '' ?>"><i class="fa-solid fa-list-alt"></i> Moji tretmani</a></li>
          <li><a href="/termini/lista" class="<?= $current_path === '/termini/lista' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check"></i> Moji termini</a></li>
          <li><a href="/izvjestaji/terapeut" class="<?= $current_path === '/izvjestaji/terapeut' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Izvještaji</a></li>
        </ul>
        
        <?php elseif ($user['uloga'] === 'pacijent'): ?>
          <ul class="menu-list">
            <?php $karton_id = get_user_karton_id($user['id']); ?>
            <li><a href="/kartoni/pregled?id=<?= $karton_id ?>"><i class="fa-solid fa-folder-open"></i> Moj karton</a></li>
            <li><a href="/kartoni/tretmani?id=<?= $karton_id ?>"><i class="fa-solid fa-notes-medical"></i> Moji tretmani</a></li>
            <li><a href="/kartoni/nalazi?id=<?= $karton_id ?>"><i class="fa-solid fa-file-medical"></i> Moji nalazi</a></li>
            <li><a href="/moji-termini"><i class="fa-solid fa-calendar-check"></i> Moji termini</a></li>
        </ul>
        <?php endif; ?>
      
    </nav>
    <div class="sidebar-footer">
      <a href="/logout" class="logout-btn">Odjavi se</a>
    </div>
  </aside>

  <main class="main">
    <header class="topbar"  <?php if ($user['uloga'] === 'pacijent'): ?>style="justify-content:end"<?php endif; ?>>
      <?php if ($user['uloga'] != 'pacijent'): ?>
      <form action="/pretraga" method="GET" class="admin-search">
          <div class="admin-search-input-group">
              <input type="text" name="q" placeholder="Pretraži..." class="admin-search-input">
              <button type="submit" class="btn btn-search">
                  <i class="fa-solid fa-search"></i>
              </button>
          </div>
      </form>
      <?php endif; ?>
      <nav class="topbar-nav">
          <a href="/" class="home">Početak</a>
            

          <div class="user-dropdown">
            <a href="#" class="user-toggle">
             <img src="/uploads/profilne/<?= htmlspecialchars($user['slika'] ?? 'default.jpg') ?>" alt="Profil" style="height:30px; border-radius:50%;"> <?= htmlspecialchars($user['ime']) ?> <?= htmlspecialchars($user['prezime']) ?> <span class="arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9" />
                </svg>
            </span>
            </a>
            <ul class="user-dropdown-menu">
            <li><a href="/moj-profil"><i class="fa-solid fa-user"></i> Moj profil</a></li>
            <li><a href="/logout"><i class="fa-solid fa-sign-out-alt"></i> Odjava</a></li>
            </ul>
        </div>

      </nav>
    </header>
    <?= $content ?? '' ?>
  </main>
</div>

<script>
// Dropdown menu functionality
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
  toggle.addEventListener('click', function(e) {
    e.preventDefault();
    this.parentElement.classList.toggle('open');
  });
});

// Zatvori dropdown ako se klikne negdje drugo
document.addEventListener('click', function(e) {
  document.querySelectorAll('.dropdown').forEach(dropdown => {
    if (!dropdown.contains(e.target)) {
      dropdown.classList.remove('open');
    }
  });
});
</script>

<script>
document.querySelector('.user-toggle').addEventListener('click', function(e) {
  e.preventDefault();
  document.querySelector('.user-dropdown').classList.toggle('open');
});
document.addEventListener('click', function(e) {
  if (!document.querySelector('.user-dropdown').contains(e.target)) {
    document.querySelector('.user-dropdown').classList.remove('open');
  }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.upload-form');
  if (!form) return;

  const fileInput = form.querySelector('input[type="file"]');
  const progressContainer = document.querySelector('.progress-container');
  const progressBar = document.querySelector('.progress-bar');
  const progressText = document.querySelector('.progress-text');
  const notification = document.querySelector('.notification');
  const filePreview = document.querySelector('.file-preview');
  const previewImage = document.querySelector('.preview-image');
  const loadingIndicator = document.querySelector('.loading-indicator');

  fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (!file) return;

    filePreview.style.display = 'block';

    const reader = new FileReader();
    reader.onload = e => {
      previewImage.src = e.target.result;
      previewImage.style.display = 'block';
    };
    reader.readAsDataURL(file);
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    progressContainer.style.display = 'block';
    progressBar.style.width = '0%';
    progressText.textContent = '0%';
    loadingIndicator.style.display = 'none';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);

    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable) {
        const percentComplete = Math.round((e.loaded / e.total) * 100);
        progressBar.style.width = percentComplete + '%';
        progressText.textContent = `${percentComplete}%`;

        if (percentComplete === 100) {
          loadingIndicator.style.display = 'block';
        }
      }
    };

    xhr.onload = () => {
      loadingIndicator.style.display = 'none';

      if (xhr.status === 200) {
        window.location.href = form.action + (form.action.includes('?') ? '&' : '?') + 'msg=updated';

      } else {
        showNotification('Greška pri slanju.', 'error');
      }
    };

    xhr.onerror = () => {
      loadingIndicator.style.display = 'none';
      showNotification('Greška na serveru.', 'error');
    };

    xhr.send(formData);
  });

  function showNotification(msg, type = 'info') {
    notification.textContent = msg;
    notification.style.color = type === 'error' ? 'red' : 'green';
  }
});
</script>



<!-- jQuery + DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector('#tabela')) {
      // Provjeri da li je stranica sa terminima, kartonima ili pacijentima
      var isListPage = window.location.pathname.includes('/termini/lista') || 
                       window.location.pathname.includes('/kartoni/lista') ||
                       window.location.pathname.includes('/profil/pacijent');
      
      var orderConfig = [[0, "asc"]]; // Default: po ID-u
      
      if (window.location.pathname.includes('/termini/lista')) {
        orderConfig = [[1, "asc"]]; // Za termine: po datumu
      }
      
      $('#tabela').DataTable({
        responsive: true,
        order: orderConfig,
        // AŽURIRANO: pageLength 100 i stateSave za liste
        pageLength: isListPage ? 100 : 25,
        stateSave: isListPage,  // Pamti stranicu samo za liste
        stateDuration: 60 * 60 * 24, // Pamti 24 sata
        lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, "Sve"]],
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/hr.json"
        }
      });
    }
  });
</script>

<!--SELECT 2-->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const select = document.querySelector('.select2');
    if (select) {
      $(select).select2({
        placeholder: "Odaberite...",
        allowClear: true,
        width: '100%',
        language: {
          noResults: function () {
            return "Nema rezultata";
          }
        }
      });
    }
  });
</script>
<script>
// Mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const btn = document.querySelector('.mobile-menu-btn');
    
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    
    // Change icon
    const icon = btn.querySelector('i');
    if (sidebar.classList.contains('active')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
        document.body.style.overflow = 'hidden';
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
        document.body.style.overflow = '';
    }
}

// Close menu on resize to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const btn = document.querySelector('.mobile-menu-btn');
        
        if (sidebar) sidebar.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        if (btn) {
            const icon = btn.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
        document.body.style.overflow = '';
    }
});

// Close menu when clicking a link
document.querySelectorAll('.sidebar-nav a').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            toggleMobileMenu();
        }
    });
});
</script>
</body>
</html>