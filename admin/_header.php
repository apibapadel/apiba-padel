<?php
ob_start(); // evita "headers already sent"
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin'])) {
  header("Location: /apiba-padel/admin/login.php");
  exit;
}

$adminNombre = $_SESSION['admin']['email'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title ?? 'Admin APiBA') ?></title>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap");
/* ===========================
       APiBA Admin — estilo tipo Mercado Libre
       (pero verde suave)
       Base global para TODO /admin/
       =========================== */

    :root{
      --brand:#D9F7D5;          /* barra superior (verde suave) */
      --brand-strong:#2DB31F;   /* verde APIBA (botones/acentos) */
      --brand-strong-2:#239816;

      --bg:#E2E6E1;             /* fondo gris ML */
      --surface:#FFFFFF;        /* tarjetas */
      --surface-2:#F6F6F6;      /* inputs/soft */
      --ink:#111827;
      --muted:#6B7280;
      --line:#E5E7EB;

      --blue:#1D4ED8;
      --violet:#7C3AED;
      --amber:#B45309;
      --cyan:#0891B2;
      --red:#DC2626;

      --radius:12px;
      --shadow: 0 1px 2px rgba(0,0,0,.08);
      --shadow2: 0 8px 24px rgba(0,0,0,.10);
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color:var(--ink);
      background:var(--bg);
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
    }
    a{color:inherit}

    /* ===== Header estilo ML ===== */
    .ml-header{
      background: var(--brand);
      border-bottom: 1px solid rgba(0,0,0,.08);
    }
    .ml-header__row{
      max-width: 1180px;
      margin: 0 auto;
      padding: 10px 14px;
      display:flex;
      align-items:center;
      gap:12px;
    }
    .ml-logo{
      display:flex; align-items:center; gap:10px; min-width: 160px;
      text-decoration:none;
    }
    .ml-logo__badge{
      height:34px; width:34px; border-radius:8px;
      background: var(--brand-strong);
      box-shadow: var(--shadow);
      display:flex; align-items:center; justify-content:center;
      color:#fff; font-weight:800;
    }
    .ml-logo__text{
      font-weight:800;
      letter-spacing:.2px;
      line-height:1.05;
    }
    .ml-logo__sub{display:block; font-weight:600; font-size:12px; color:rgba(0,0,0,.55)}
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
      display:flex; align-items:center; gap:10px; justify-content:flex-end;
      min-width: 220px;
    }
    .ml-chip{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 7px 10px;
      border-radius:999px;
      background: rgba(255,255,255,.65);
      border:1px solid rgba(0,0,0,.08);
      font-size: 13px;
      color: rgba(0,0,0,.75);
      text-decoration:none;
      white-space:nowrap;
    }
    .ml-chip strong{font-weight:700; color: rgba(0,0,0,.85)}
    .ml-chip--cta{
      background: var(--brand-strong);
      border-color: rgba(0,0,0,.06);
      color:#fff;
    }
    .ml-chip--cta:hover{background: var(--brand-strong-2)}
    .ml-chip:hover{filter: brightness(.98)}

    .ml-subnav{
      max-width:1180px;
      margin: 0 auto;
      padding: 8px 14px 10px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
    }
    .ml-links{
      display:flex; gap:8px; flex-wrap:wrap; align-items:center;
    }
    .ml-link{
      text-decoration:none;
      font-size:13px;
      padding:6px 10px;
      border-radius:999px;
      background: rgba(255,255,255,.55);
      border: 1px solid rgba(0,0,0,.08);
      color: rgba(0,0,0,.72);
    }
    .ml-link:hover{background: rgba(255,255,255,.8)}
    .ml-link.is-active{
      background:#fff;
      border-color: rgba(0,0,0,.14);
      color: rgba(0,0,0,.85);
      box-shadow: var(--shadow);
    }

    /* ===== Layout ===== */
    .wrap{
      max-width: 1180px;
      margin: 14px auto 22px;
      padding: 0 14px;
    }
    .card{
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: var(--shadow2);
      padding: 14px;
    }

    /* ===== Breadcrumbs (opcional) ===== */
    .breadcrumb{
      display:flex;
      gap:6px;
      align-items:center;
      flex-wrap:wrap;
      font-size: 12.5px;
      color: var(--muted);
      margin: 0 0 10px;
    }
    .breadcrumb a{color: rgba(0,0,0,.65); text-decoration:none}
    .breadcrumb a:hover{text-decoration:underline}

    /* ===== Tipografías / títulos ===== */
    h1,h2,h3{margin:0 0 10px}
    h1{font-size:22px}
    h2{font-size:18px}
    .muted{color:var(--muted)}

    /* ===== Controls ===== */
    .input, input[type="text"], input[type="number"], input[type="date"], input[type="email"], input[type="password"], select, textarea{
      background: var(--surface-2);
      border: 1px solid var(--line);
      border-radius: 10px;
      padding: 9px 10px;
      outline: none;
      color: var(--ink);
      font-size: 14px;
    }
    select{cursor:pointer}
    input:focus, select:focus, textarea:focus{border-color: rgba(45,179,31,.55); box-shadow: 0 0 0 3px rgba(45,179,31,.15)}

    /* ===== Buttons ===== */
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
      text-decoration:none;
      cursor:pointer;
      box-shadow: var(--shadow);
      font-size: 13.5px;
      line-height: 1;
      white-space: nowrap;
    }
    .btn:hover{filter: brightness(.98)}
    .btn-sm{padding:7px 10px; font-size: 13px; border-radius: 9px}

    .btn-primary{background: var(--brand-strong); color:#fff; border-color: rgba(0,0,0,.06)}
    .btn-primary:hover{background: var(--brand-strong-2)}
    .btn-danger{background: var(--red); color:#fff; border-color: rgba(0,0,0,.06)}
    .btn-danger:hover{filter: brightness(.96)}

    /* “colores suaves” por acción */
    .btn-edit{border-color: rgba(29,78,216,.25); background: rgba(29,78,216,.08); color:#0B2F8F}
    .btn-insc{border-color: rgba(124,58,237,.25); background: rgba(124,58,237,.08); color:#4C1D95}
    .btn-points{border-color: rgba(180,83,9,.25); background: rgba(180,83,9,.08); color:#7C2D12}
    .btn-view{border-color: rgba(8,145,178,.25); background: rgba(8,145,178,.08); color:#155E75}
    .btn-zones{border-color: rgba(45,179,31,.25); background: rgba(45,179,31,.10); color:#14532D}
    .btn-print{border-color: rgba(0,0,0,.18); background: rgba(255,255,255,.95); color: rgba(0,0,0,.8)}

    /* ===== Table (look ML) ===== */
    table{border-collapse:separate; border-spacing:0; width:100%}
    .table-wrap{overflow:auto}
    .table{
      width:100%;
      background:#fff;
      border:1px solid var(--line);
      border-radius: 12px;
      overflow:hidden;
      box-shadow: var(--shadow);
    }
    .table thead th{
      background: #F9FAFB;
      font-size: 12.5px;
      color: rgba(0,0,0,.70);
      text-transform: uppercase;
      letter-spacing: .04em;
      padding: 10px 10px;
      border-bottom: 1px solid var(--line);
      white-space: nowrap;
    }
    .table tbody td{
      padding: 10px 10px;
      border-bottom: 1px solid var(--line);
      vertical-align: top;
      font-size: 14px;
      color: rgba(0,0,0,.85);
    }
    .table tbody tr:hover{background: rgba(0,0,0,.02)}
    .table tbody tr:last-child td{border-bottom:0}

    /* Column helpers */
    .col-id{width:70px; color: rgba(0,0,0,.65); font-variant-numeric: tabular-nums}
    .col-actions{min-width: 230px}
    .actions{
      display:flex;
      justify-content:flex-end;
      gap:6px;
      flex-wrap:wrap;
    }

    /* ===== Badges ===== */
    .badge{
      display:inline-flex;
      align-items:center;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12.5px;
      border: 1px solid rgba(0,0,0,.10);
      background: #fff;
      color: rgba(0,0,0,.75);
      white-space:nowrap;
    }
    .badge-open{border-color: rgba(45,179,31,.25); background: rgba(45,179,31,.10); color:#14532D}
    .badge-closed{border-color: rgba(220,38,38,.25); background: rgba(220,38,38,.08); color:#7F1D1D}
    .badge-warn{border-color: rgba(180,83,9,.25); background: rgba(180,83,9,.08); color:#7C2D12}

    /* ===== Toggle compacto ===== */
    .toggle{
      position:relative;
      width: 42px; height: 22px;
      display:inline-block;
    }
    .toggle input{display:none}
    .toggle span{
      position:absolute; inset:0;
      background: rgba(0,0,0,.18);
      border-radius: 999px;
      transition: .18s ease;
      border: 1px solid rgba(0,0,0,.12);
    }
    .toggle span:before{
      content:"";
      position:absolute;
      width: 18px; height: 18px;
      left: 2px; top: 1px;
      background:#fff;
      border-radius: 50%;
      box-shadow: var(--shadow);
      transition: .18s ease;
    }
    .toggle input:checked + span{
      background: rgba(45,179,31,.70);
      border-color: rgba(0,0,0,.06);
    }
    .toggle input:checked + span:before{transform: translateX(20px)}
    .toggle-label{font-size:12.5px; color: var(--muted)}

    /* ===== Utils ===== */
    .row{display:flex; gap:10px; flex-wrap:wrap; align-items:center}
    .right{margin-left:auto}
    .hr{height:1px;background:var(--line);margin:12px 0}

    @media (max-width: 820px){
      .ml-logo{min-width:auto}
      .ml-user{min-width:auto}
      .col-actions{min-width: 180px}
    }
  
    /* ===== Dashboard (mobile first) ===== */
    .admin-dash-grid{
      display:grid;
      grid-template-columns: 1fr;
      gap:12px;
    }
    @media (min-width: 760px){
      .admin-dash-grid{ grid-template-columns: repeat(2, 1fr); }
    }
    .admin-dash-card{
      display:flex;
      gap:12px;
      align-items:center;
      padding:14px;
      border-radius: 18px;
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.14);
      color: var(--text);
      text-decoration:none;
      transition: transform .12s ease, background .12s ease, border-color .12s ease;
    }
    .admin-dash-card:hover{
      transform: translateY(-1px);
      background: rgba(255,255,255,.10);
      border-color: rgba(255,255,255,.18);
      text-decoration:none;
    }
    .admin-dash-ico{
      width:44px;height:44px;
      border-radius: 14px;
      display:grid;place-items:center;
      background: rgba(103,209,42,.14);
      border: 1px solid rgba(103,209,42,.22);
      font-size: 20px;
      flex: 0 0 auto;
    }
    .admin-dash-title{
      font-weight: 850;
      letter-spacing: .2px;
      margin-bottom: 2px;
    }

  </style>
</head>
<body>

<header class="ml-header">
  <div class="ml-header__row">
    <a class="ml-logo" href="/apiba-padel/admin/index.php">
      <span class="ml-logo__badge">A</span>
      <span class="ml-logo__text">
        APiBA Admin
        <span class="ml-logo__sub">panel de gestión</span>
      </span>
    </a>

    <form class="ml-search" action="#" method="get" onsubmit="return false;">
      <input type="text" placeholder="Buscar en el admin (títulos, jugadores, torneos)..." aria-label="Buscar">
      <button type="submit" title="Buscar">🔍</button>
    </form>

    <div class="ml-user">
      <span class="ml-chip"><strong><?= htmlspecialchars($adminNombre) ?></strong></span>
      <a class="ml-chip ml-chip--cta" href="/apiba-padel/admin/logout.php">Salir</a>
    </div>
  </div>

  <div class="ml-subnav">
    <nav class="ml-links">
      <a class="ml-link <?= ($active ?? '')==='torneos'?'is-active':'' ?>" href="/apiba-padel/admin/torneos.php">Torneos</a>
      <a class="ml-link <?= ($active ?? '')==='jugadores'?'is-active':'' ?>" href="/apiba-padel/admin/jugadores.php">Jugadores</a>
      <a class="ml-link <?= ($active ?? '')==='ranking'?'is-active':'' ?>" href="/apiba-padel/admin/ranking.php">Ranking</a>
      <a class="ml-link <?= ($active ?? '')==='noticias'?'is-active':'' ?>" href="/apiba-padel/admin/noticias.php">Noticias</a>
      <a class="ml-link" href="/apiba-padel/index.php">Ir al sitio</a>
    </nav>

    <div class="row">
      <?php if (!empty($page_title)): ?>
        <span class="ml-chip">📍 <strong><?= htmlspecialchars($page_title) ?></strong></span>
      <?php endif; ?>
    </div>
  </div>
</header>

<div class="wrap">
