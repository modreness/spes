<?php
require_once __DIR__ . '/../helpers/load.php';
require_login();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8">
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
<div class="dashboard">

  <aside class="sidebar">
    <div class="logo">
      <a href="/"><img src="/assets/images/spes-logo-slogan.svg" alt="SPES"></a>
    </div>
    <nav class="sidebar-nav">
    <h2 class="menu-title">IZBORNIK</h2>
      
        <?php if ($user['uloga'] === 'admin'): ?>
        <ul class="menu-list">
          <li class="dropdown">
              <a href="#" class="dropdown-toggle">
                <img src="/assets/icons/edit.svg" alt=""> Pregled profila
                <span class="arrow">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </span>

              </a>
              <ul class="submenu">
                <li><a href="/profil/pacijent"><img src="/assets/icons/korisnici.svg" alt=""> Pacijent</a></li>
                <li><a href="/profil/terapeut"><img src="/assets/icons/korisnici.svg" alt=""> Terapeut</a></li>
                <li><a href="/profil/recepcioner"><img src="/assets/icons/korisnici.svg" alt=""> Recepcioner</a></li>
                <li><a href="/profil/admin"><img src="/assets/icons/korisnici.svg" alt=""> Admin</a></li>
                <li><a href="/profil/kreiraj"><img src="/assets/icons/korisnici.svg" alt=""> Kreiraj profil</a></li>
              </ul>
            </li>

          <li><a href="/kartoni/lista"><img src="/assets/icons/karton.svg" alt="">  Kartoni</a></li>
          <li><a href="/korisnici"><img src="/assets/icons/search.svg" alt=""> Pretraga</a></li>
          <li><a href="/korisnici"><img src="/assets/icons/izvjestaj.svg" alt=""> Izvještaji</a></li>
          <li><a href="/korisnici"><img src="/assets/icons/kategorija.svg" alt=""> Kategorije</a></li>
          <li><a href="/korisnici"><img src="/assets/icons/cijene.svg" alt=""> Cjenovnik</a></li>
          <li><a href="/korisnici"><img src="/assets/icons/clock.svg" alt=""> Raspored terapeuta</a></li>
          <li><a href="/korisnici"><img src="/assets/icons/calendar.svg" alt=""> Timetable</a></li>
        </ul>
        <div class="zakazani-link">
        <a href="#"><img src="/assets/icons/zakazani.svg" alt=""> ZAKAZANI TERMINI</a>
         </div>
        <?php elseif ($user['uloga'] === 'recepcioner'): ?>
        <ul class="menu-list">
          <li><a href="/dashboard">Dashboard</a></li>
          <li><a href="/dodavanje-rasporeda">Dodavanje rasporeda</a></li>
          <li><a href="/pregled-rasporeda">Pregled rasporeda</a></li>
        </ul>
        <?php elseif ($user['uloga'] === 'terapeut'): ?>
        <ul class="menu-list">
          <li><a href="/dashboard">Dashboard</a></li>
          <li><a href="/moj-raspored">Moj raspored</a></li>
        </ul>
        <?php elseif ($user['uloga'] === 'pacijent'): ?>
        <ul class="menu-list">
          <li><a href="/dashboard">Moj Dashboard</a></li>
          <li><a href="/moj-karton">Moj karton</a></li>
        </ul>
        <?php endif; ?>
      
    </nav>
    <div class="sidebar-footer">
      <a href="/logout" class="logout-btn">Odjavi se</a>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <input type="text" placeholder="Pretraži...">
      <nav class="topbar-nav">
          <a href="/" class="home">Početak</a>
            

          <div class="user-dropdown">
            <a href="#" class="user-toggle">
             <img src="/uploads/profilne/<?= htmlspecialchars($user['slika'] ?? 'default.jpg') ?>" alt="Profil" style="height:30px; border-radius:50%;"> <?= htmlspecialchars($user['ime']) ?> <?= htmlspecialchars($user['prezime']) ?> <span class="arrow">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="6 9 12 15 18 9" />
  </svg>
</span>

            </a>
            <ul class="user-menu">
              <li><a href="/profil/uredi">Uredi profil</a></li>
              <li><a href="/profil/lozinka">Promijeni lozinku</a></li>
              <li><a href="/logout">Odjava</a></li>
            </ul>
          </div>
        </nav>
    </header>

    
      <?= $content ?? '' ?>
    
  </main>

</div>
<script>
  document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      this.parentElement.classList.toggle('open');
    });
  });
  
  document.querySelectorAll('.user-toggle').forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      this.parentElement.classList.toggle('open');
    });
  });
</script>


<!--UPLOAD IMAGE-->

<script>
document.addEventListener("DOMContentLoaded", () => {
  const fileInput = document.getElementById('profilna');
  const uploadBtn = document.getElementById('uploadBtn');
  const fileName = document.getElementById('fileName');
  const previewImage = document.getElementById('previewImage');
  const filePreview = document.getElementById('filePreview');

  const progressContainer = document.getElementById('progress-container');
  const progressBar = document.querySelector('.progress-bar');
  const progressText = document.querySelector('.progress-text');
  const loadingIndicator = document.getElementById('loading-indicator');
  const notification = document.getElementById('notification');

  const form = document.querySelector('form');

  uploadBtn.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (!file) return;

    fileName.textContent = file.name;
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
      $('#tabela').DataTable({
        responsive: true,
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
        placeholder: "Odaberite pacijenta",
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

</body>
</html>
