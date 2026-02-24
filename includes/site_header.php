<?php
if (!ob_get_level()) { ob_start(); }
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$base = "/apiba-padel";
$esJugador = isset($_SESSION['jugador']);

// Opcionales
if (!isset($section)) $section = 'public'; // 'public' | 'admin'
if (!isset($active)) $active = '';
if (!isset($page_title)) $page_title = 'APiBA Pádel';

// Autodetecta admin por URL
$uri = $_SERVER['REQUEST_URI'] ?? '';
if ($section === 'public' && strpos($uri, '/admin/') !== false) {
  $section = 'admin';
}

function nav_active($key, $active){
  return $key === $active ? 'is-active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>
  <link rel="stylesheet" href="/apiba-padel/assets/css/apiba-pro.css?v=20260223">
  <link rel="stylesheet" href="/apiba-padel/assets/css/ml-apiba.css">

  <style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap");
/* ===========================
       APiBA Public 
       =========================== */
    :root{
      --brand:#D9F7D5;
      --brand-strong:#2DB31F;
      --brand-strong-2:#239816;

      --bg:#E2E6E1;
      --surface:#FFFFFF;
      --surface-2:#F6F6F6;
      --ink:#111827;
      --muted:#6B7280;
      --line:#E5E7EB;

      --radius:12px;
      --shadow: 0 1px 2px rgba(0,0,0,.08);
      --shadow2: 0 10px 30px rgba(0,0,0,.10);
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: linear-gradient(180deg, var(--bg), #EEF2EE 55%, #F6F7F6);
      color: var(--ink);
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
    }
    a{color:inherit; text-decoration:none}
    img{max-width:100%; display:block}

    /* ===== Header ML-like ===== */
    .ml-header{background: var(--brand); border-bottom:1px solid rgba(0,0,0,.08)}
    .ml-row{
      max-width: 1180px;
      margin:0 auto;
      padding: 10px 14px;
      display:flex; align-items:center; gap:12px; flex-wrap:wrap;
    }
    .ml-logo{display:flex; align-items:center; gap:10px; min-width: 180px}
    .ml-logo__badge{
      height:34px; width:34px; border-radius:8px;
      background: var(--brand-strong);
      box-shadow: var(--shadow);
      display:flex; align-items:center; justify-content:center;
      color:#fff; font-weight:800;
    }
    .ml-logo__text{font-weight:900; letter-spacing:.2px; line-height:1.05}
    .ml-logo__sub{display:block; font-weight:600; font-size:12px; color: rgba(0,0,0,.55)}

    .ml-search{
      flex:1;
      display:flex;
      align-items:center;
      background:#fff;
      border:1px solid rgba(0,0,0,.12);
      border-radius:10px;
      box-shadow: var(--shadow);
      overflow:hidden;
      min-width: 260px;
    }
    .ml-search input{
      flex:1;
      border:0;
      outline:0;
      padding: 10px 12px;
      font-size:14px;
      background:transparent;
    }
    .ml-search button{
      border:0;
      padding: 10px 14px;
      cursor:pointer;
      background: transparent;
      border-left: 1px solid rgba(0,0,0,.10);
      color: rgba(0,0,0,.6);
    }

    .ml-user{
      display:flex; gap:8px; align-items:center; justify-content:flex-end;
      min-width: 220px;
      flex: 0 0 auto;
    }
    .chip{
      display:inline-flex; align-items:center; gap:8px;
      padding: 7px 10px;
      border-radius:999px;
      background: rgba(255,255,255,.65);
      border:1px solid rgba(0,0,0,.08);
      font-size: 13px;
      color: rgba(0,0,0,.75);
      white-space:nowrap;
    }
    .chip strong{font-weight:800; color: rgba(0,0,0,.85)}
    .chip--cta{
      background: var(--brand-strong);
      color:#fff;
      border-color: rgba(0,0,0,.06);
    }
    .chip--cta:hover{background: var(--brand-strong-2)}

    .ml-subnav{
      max-width:1180px;
      margin:0 auto;
      padding: 8px 14px 10px;
      display:flex; align-items:center; justify-content:space-between;
      gap:10px; flex-wrap:wrap;
    }
    .cats{display:flex; gap:8px; flex-wrap:wrap; align-items:center}
    .cat{
      font-size:13px;
      padding:6px 10px;
      border-radius:999px;
      background: rgba(255,255,255,.55);
      border: 1px solid rgba(0,0,0,.08);
      color: rgba(0,0,0,.72);
    }
    .cat:hover{background: rgba(255,255,255,.8)}
    .cat.is-active{
      background:#fff;
      border-color: rgba(0,0,0,.14);
      color: rgba(0,0,0,.85);
      box-shadow: var(--shadow);
    }

    /* ===== Layout ===== */
    .page{max-width:1180px;margin: 14px auto 24px; padding: 0 14px;}
    .container{background:transparent}

    /* Cards / panels */
    .card{
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: var(--shadow2);
      padding: 14px;
    }

    /* Tables (ranking, etc.) */
    table{border-collapse:separate;border-spacing:0;width:100%}
    .table{
      background:#fff;
      border:1px solid var(--line);
      border-radius:12px;
      overflow:hidden;
      box-shadow: var(--shadow);
    }
    .table th{
      background:#F9FAFB;
      font-size:12.5px;
      color: rgba(0,0,0,.70);
      text-transform: uppercase;
      letter-spacing: .04em;
      padding:10px;
      border-bottom:1px solid var(--line);
      white-space:nowrap;
      text-align:left;
    }
    .table td{
      padding:10px;
      border-bottom:1px solid var(--line);
      color: rgba(0,0,0,.85);
      vertical-align:top;
    }
    .table tr:hover td{background: rgba(0,0,0,.02)}
    .table tr:last-child td{border-bottom:0}

    /* Inputs */
    input[type="text"], input[type="email"], input[type="password"], input[type="number"], select, textarea{
      background: var(--surface-2);
      border:1px solid var(--line);
      border-radius: 10px;
      padding: 9px 10px;
      outline: none;
      color: var(--ink);
      font-size: 14px;
    }
    input:focus, select:focus, textarea:focus{
      border-color: rgba(45,179,31,.55);
      box-shadow: 0 0 0 3px rgba(45,179,31,.15);
    }

    /* Buttons */
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      padding: 8px 12px;
      border-radius: 10px;
      border: 1px solid rgba(0,0,0,.10);
      background:#fff;
      color: rgba(0,0,0,.82);
      box-shadow: var(--shadow);
      cursor:pointer;
    }
    .btn:hover{filter: brightness(.98)}
    .btn-primary{
      background: var(--brand-strong);
      color:#fff;
      border-color: rgba(0,0,0,.06);
    }
    .btn-primary:hover{background: var(--brand-strong-2)}
    .muted{color:var(--muted)}

    /* Breadcrumbs opcional */
    .breadcrumb{
      display:flex; gap:6px; align-items:center; flex-wrap:wrap;
      font-size: 12.5px; color: var(--muted);
      margin: 0 0 10px;
    }
    .breadcrumb a{color: rgba(0,0,0,.65)}
    .breadcrumb a:hover{text-decoration:underline}

    @media (max-width: 820px){
      .ml-user{min-width:auto}
      .ml-logo{min-width:auto}
    }
  </style>
</head>
<body>

<header class="ml-header">
  <div class="ml-row">
    <a class="ml-logo" href="<?= $base ?>/index.php">
      <span class="ml-logo__badge">A</span>
      <span class="ml-logo__text">
        APiBA Pádel
        <span class="ml-logo__sub">- HOME - </span>
      </span>
    </a>

    <form class="ml-search" action="<?= $base ?>/buscar.php" method="get">
      <input type="text" name="q" placeholder="Buscar torneos por sede/categoría o noticias por título..." aria-label="Buscar">
      <button type="submit" title="Buscar">🔍</button>
    </form>

    <div class="ml-user">
      <?php if ($esJugador): ?>
        <span class="chip">👤 <strong><?= htmlspecialchars($_SESSION['jugador']['nombre'] ?? 'Jugador') ?></strong></span>
        <a class="chip chip--cta" href="<?= $base ?>/logout_jugador.php">Cerrar sesión</a>
      <?php else: ?>
        <a class="chip chip--cta" href="<?= $base ?>/login.php">Ingresar</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="ml-subnav">
    <nav class="cats">
      <a class="cat <?= nav_active('torneos', $active) ?>" href="<?= $base ?>/torneos/index.php">Torneos</a>
      <a class="cat <?= nav_active('ranking', $active) ?>" href="<?= $base ?>/ranking/index.php">Ranking</a>
      <a class="cat <?= nav_active('noticias', $active) ?>" href="<?= $base ?>/public/noticias.php">Noticias</a>
      <a class="cat <?= nav_active('registro', $active) ?>" href="<?= $base ?>/registro.php">Registro</a>
    </nav>

    <div class="cats">
      <?php if (!empty($page_title)): ?>
        <span class="chip">📍 <strong><?= htmlspecialchars($page_title) ?></strong></span>
      <?php endif; ?>
    </div>
  </div>
</header>

<?php if ($section === 'public'): ?>
  <div class="page">
    <div class="container">
<?php else: ?>
  <!-- ADMIN: si algún admin usa este header por error, no metemos el layout público -->
<?php endif; ?>