<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* ✅ Variables para que el header cargue el diseño público */
$section = 'public';
$active = 'perfil';
$page_title = 'Mi perfil - APIBA Pádel';

/* ✅ Obligamos login */
if (!isset($_SESSION['jugador'])) {
  header("Location: /apiba-padel/login.php");
  exit;
}

/* ✅ Includes correctos desde /jugador */
require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function sede_logo(string $sede): string {
  $s = mb_strtolower(trim($sede));
  $s = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
  if (str_contains($s, 'casbas')) return '/apiba-padel/assets/img/sedes/logos/casbas.jpg';
  if (str_contains($s, 'fray')) return '/apiba-padel/assets/img/sedes/logos/fray.jpg';
  if (str_contains($s, 'quinta')) return '/apiba-padel/assets/img/sedes/logos/la-quinta.jpg';
  if (str_contains($s, '90')) return '/apiba-padel/assets/img/sedes/logos/90s.jpg';
  return '';
}

function ensure_csrf() {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}
$csrf = ensure_csrf();

/* ✅ ID jugador desde sesión */
$jugador_id = (int)($_SESSION['jugador']['id'] ?? 0);
if ($jugador_id <= 0) {
  echo "<div class='card'><div class='card__body'><p class='p'>No se pudo identificar al jugador (falta ID en sesión).</p></div></div>";
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

$flash_ok = '';
$flash_err = '';

/* ✅ Acción: borrar inscripción (si pertenece al jugador) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cancelar_inscripcion') {
  $token = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    $flash_err = "Token inválido. Recargá la página e intentá de nuevo.";
  } else {
    $insc_id = (int)($_POST['inscripcion_id'] ?? 0);
    if ($insc_id <= 0) {
      $flash_err = "Inscripción inválida.";
    } else {
      try {
        $stmt = $pdo->prepare("DELETE FROM inscripciones WHERE id = ? AND jugador_id = ?");
        $stmt->execute([$insc_id, $jugador_id]);

        if ($stmt->rowCount() > 0) $flash_ok = "Inscripción eliminada.";
        else $flash_err = "No se pudo eliminar (no existe o no te pertenece).";
      } catch (Exception $e) {
        $flash_err = "Error al eliminar inscripción.";
      }
    }
  }
}

/* ✅ Traer datos completos del jugador desde DB */
$jugador = [];
try {
  $stmt = $pdo->prepare("SELECT * FROM jugadores WHERE id = ? LIMIT 1");
  $stmt->execute([$jugador_id]);
  $jugador = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
  $jugador = [];
}

if (!$jugador) {
  echo "<div class='card'><div class='card__body'><p class='p'>Jugador no encontrado.</p></div></div>";
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

/* ✅ Refrescamos sesión con lo último (así se actualiza nombre/foto si cambió) */
$_SESSION['jugador'] = $jugador;

/* ✅ Foto */
$foto_raw = trim((string)($jugador['foto'] ?? ''));

// default
$foto_src = "/apiba-padel/assets/img/user-placeholder.png";

if ($foto_raw !== '') {

  // 1) Si ya es URL completa
  if (preg_match('~^https?://~i', $foto_raw)) {
    $foto_src = $foto_raw;

  // 2) Si ya viene con /apiba-padel/...
  } elseif (strpos($foto_raw, '/apiba-padel/') === 0) {
    $foto_src = $foto_raw;

  // 3) Si ya es ruta absoluta desde el root (/uploads/...)
  } elseif ($foto_raw[0] === '/') {
    $foto_src = $foto_raw;

  // 4) Si es relativa tipo uploads/jugadores/...
  } elseif (strpos($foto_raw, 'uploads/') === 0) {
    $foto_src = "/apiba-padel/" . $foto_raw;

  // 5) Si es solo nombre de archivo: jugador_5.jpg
  } else {
    $foto_src = "/apiba-padel/uploads/jugadores/" . $foto_raw;
  }

  // Validación de existencia SOLO para archivos locales del proyecto
  // (si es URL, no chequeamos)
  if (!preg_match('~^https?://~i', $foto_src)) {
    $fs_path = $_SERVER['DOCUMENT_ROOT'] . $foto_src;
    if (!file_exists($fs_path)) {
      $foto_src = "/apiba-padel/assets/img/user-placeholder.png";
    }
  }
}


/* ✅ Ranking/Puntos desde jugadores */
$puntos = (int)($jugador['puntos'] ?? 0);
$posicion = (int)($jugador['ranking'] ?? 0);

/* ✅ Inscripciones del jugador */
$inscripciones = [];
try {
  $stmt = $pdo->prepare("
    SELECT i.id AS inscripcion_id, i.fecha AS fecha_inscripcion,
           t.id AS torneo_id, t.nombre, t.categoria, t.sede, t.fecha_inicio, t.fecha_fin, t.estado
    FROM inscripciones i
    INNER JOIN torneos t ON t.id = i.torneo_id
    WHERE i.jugador_id = ?
    ORDER BY t.fecha_inicio DESC
  ");
  $stmt->execute([$jugador_id]);
  $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $inscripciones = [];
}

function estado_label($estado) {
  $e = strtolower(trim((string)$estado));
  $e = str_replace('_',' ', $e);
  if ($e === 'abierto') return 'Abierto';
  if ($e === 'en curso') return 'En curso';
  if ($e === 'finalizado') return 'Finalizado';
  if ($e === 'cancelado') return 'Cancelado';
  return (string)$estado;
}
function estado_badge($estado) {
  $e = strtolower(trim((string)$estado));
  $e = str_replace('_',' ', $e);
  if ($e === 'abierto') return 'badge--ok';
  if ($e === 'en curso') return 'badge--warn';
  if ($e === 'finalizado') return 'badge';
  if ($e === 'cancelado') return 'badge--danger';
  return 'badge';
}

$edit_url = "/apiba-padel/jugador/editar_perfil.php";
?>

<?php if ($flash_ok): ?>
  <div class="card"><div class="card__body"><span class="badge badge--ok"><?= h($flash_ok) ?></span></div></div>
  <div style="height:10px;"></div>
<?php endif; ?>

<?php if ($flash_err): ?>
  <div class="card"><div class="card__body"><span class="badge badge--danger"><?= h($flash_err) ?></span></div></div>
  <div style="height:10px;"></div>
<?php endif; ?>

<div class="grid grid--2">

  <!-- PERFIL -->
  <div class="card">
    <div class="card__header">
      <h1 class="h1">Mi perfil</h1>
      <?php if ((int)($jugador['activo'] ?? 1) === 1): ?>
        <span class="badge badge--ok">Activo</span>
      <?php else: ?>
        <span class="badge badge--danger">Inactivo</span>
      <?php endif; ?>
    </div>

    <div class="card__body" style="display:flex; gap:14px; align-items:flex-start; flex-wrap:wrap;">

      <div style="width:120px; flex:0 0 120px;">
        <img src="<?= h($foto_src) ?>"
             alt="Foto jugador"
             style="width:120px;height:120px;object-fit:cover;border-radius:16px;border:1px solid var(--border);background:var(--surface);">
      </div>

      <div style="flex:1; min-width:240px;">
        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
          <div class="h2" style="margin:0;">
            <?= h(($jugador['apellido'] ?? '') . ' ' . ($jugador['nombre'] ?? '')) ?>
          </div>

          <?php if (!empty($jugador['categoria'])): ?>
            <span class="badge badge--warn">Categoría: <?= h($jugador['categoria']) ?></span>
          <?php endif; ?>

          <?php if (!empty($jugador['sexo'])): ?>
            <span class="badge">Sexo: <?= h($jugador['sexo']) ?></span>
          <?php endif; ?>
        </div>

        <div style="height:8px;"></div>

        <div class="grid" style="gap:8px;">
          <p class="p" style="margin:0;"><b>Email:</b> <?= h($jugador['email'] ?? '') ?></p>
          <p class="p" style="margin:0;"><b>DNI:</b> <?= h($jugador['dni'] ?? '') ?></p>
          <p class="p" style="margin:0;"><b>Teléfono:</b> <?= h($jugador['telefono'] ?? '') ?></p>
          <p class="p" style="margin:0;"><b>Registrado:</b> <?= h($jugador['created_at'] ?? '') ?></p>
        </div>

        <div style="height:12px;"></div>

        <div class="form-actions" style="display:flex; gap:10px; flex-wrap:wrap;">
          <a class="btn btn--primary" href="<?= h($edit_url) ?>">Modificar perfil</a>
          <a class="btn" href="/apiba-padel/ranking/">Ver ranking</a>
          <a class="btn" href="/apiba-padel/torneos/">Ver torneos</a>
          <a class="btn" href="/apiba-padel/logout_jugador.php">Cerrar sesión</a>
        </div>

        <div style="height:10px;"></div>
        <p class="p" style="margin:0; color:var(--muted);">
          * La categoría se muestra pero no se edita desde “Modificar perfil”.
        </p>
      </div>
    </div>
  </div>

  <!-- RANKING -->
  <div class="card">
    <div class="card__header">
      <h2 class="h2">📊 Mi ranking</h2>
      <span class="badge">Puntos / Posición</span>
    </div>

    <div class="card__body">
      <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <span class="badge">Puntos: <?= (int)$puntos ?></span>
        <?php if ($posicion > 0): ?>
          <span class="badge badge--ok">Posición: #<?= (int)$posicion ?></span>
        <?php else: ?>
          <span class="badge badge--warn">Posición: —</span>
        <?php endif; ?>
        <span class="badge">Categoría: <?= h($jugador['categoria'] ?? '') ?></span>
      </div>

      <div style="height:10px;"></div>
      <p class="p" style="margin:0;">
        La posición se toma de <b>jugadores.ranking</b> y los puntos de <b>jugadores.puntos</b>.
      </p>
    </div>
  </div>

</div>

<div style="height:14px;"></div>

<!-- ✅ FIX: evitar overflow horizontal en “Mis inscripciones” (sin barra inferior) -->
<style>
  /* Asegura que nada de esta card genere ancho extra */
  #card-mis-inscripciones,
  #card-mis-inscripciones .card__body,
  #card-mis-inscripciones .table-wrap {
    max-width: 100%;
  }

  /* Tabla SIEMPRE calza en el ancho (no “crece” por contenido) */
  #card-mis-inscripciones .table-wrap { width: 100%; overflow: hidden; }
  #card-mis-inscripciones table { width: 100%; table-layout: fixed; border-collapse: collapse; }

  /* Permite cortar textos largos y evitar empuje horizontal */
  #card-mis-inscripciones th,
  #card-mis-inscripciones td {
    white-space: normal !important;
    word-break: break-word;
    overflow-wrap: anywhere;
  }

  /* Acciones: que los botones bajen de línea en vez de estirar */
  #card-mis-inscripciones td.col-actions { width: 150px; }
  #card-mis-inscripciones .actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-start;
  }
  #card-mis-inscripciones .actions form { margin: 0; }
</style>

<!-- INSCRIPCIONES -->
<div class="card" id="card-mis-inscripciones">
  <div class="card__header">
    <h2 class="h2">🏆 Torneos en los que estoy anotado</h2>
    <span class="badge">Inscripciones</span>
  </div>

  <div class="card__body">
    <?php if (empty($inscripciones)): ?>
      <p class="p">No estás anotado en ningún torneo.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Torneo</th>
              <th>Cat.</th>
              <th>Sede</th>
              <th>Inicio</th>
              <th>Estado</th>
              <th>Inscripto</th>
              <th class="col-actions"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($inscripciones as $i): ?>
              <tr>
                <td><b><?= h($i['nombre'] ?? '') ?></b></td>
                <td><?= h($i['categoria'] ?? '') ?></td>
                <td>
                  <?php $logo = sede_logo($i['sede'] ?? ''); ?>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <?php if ($logo !== ''): ?>
                      <img src="<?= h($logo) ?>" alt="" style="height:22px;border-radius:6px;background:#fff;padding:2px 6px;border:1px solid rgba(0,0,0,.08);">
                    <?php endif; ?>
                    <span><?= h($i['sede'] ?? '') ?></span>
                  </div>
                </td>
                <td><?= h($i['fecha_inicio'] ?? '') ?></td>
                <td>
                  <span class="badge <?= h(estado_badge($i['estado'] ?? '')) ?>">
                    <?= h(estado_label($i['estado'] ?? '')) ?>
                  </span>
                </td>
                <td><?= h($i['fecha_inscripcion'] ?? '') ?></td>
                <td class="col-actions">
                  <div class="actions">
                    <a class="btn" href="/apiba-padel/torneos/ver.php?id=<?= (int)($i['torneo_id'] ?? 0) ?>">Ver</a>

                    <form method="post">
                      <input type="hidden" name="accion" value="cancelar_inscripcion">
                      <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                      <input type="hidden" name="inscripcion_id" value="<?= (int)($i['inscripcion_id'] ?? 0) ?>">
                      <button type="submit" class="btn" onclick="return confirm('¿Borrar tu inscripción a este torneo?');">
                        Borrar
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Cards compactas (mobile) -->
      <div class="insc-cards">
        <?php foreach ($inscripciones as $i): ?>
          <div class="insc-card">
            <div class="insc-top">
              <p class="insc-title"><?= h($i['nombre'] ?? '') ?></p>
              <span class="badge <?= h(estado_badge($i['estado'] ?? '')) ?>"><?= h(estado_label($i['estado'] ?? '')) ?></span>
            </div>
            <div class="insc-meta">
              <div class="muted">🎾 <?= h($i['categoria'] ?? '') ?></div>
              <div class="muted" style="display:flex;align-items:center;gap:8px;">
                <?php $logo = sede_logo($i['sede'] ?? ''); ?>
                <?php if ($logo !== ''): ?>
                  <img src="<?= h($logo) ?>" alt="" style="height:20px;border-radius:6px;background:#fff;padding:2px 6px;border:1px solid rgba(0,0,0,.08);">
                <?php endif; ?>
                <span>📍 <?= h($i['sede'] ?? '') ?></span>
              </div>
              <div class="muted">📅 Inicio: <?= h($i['fecha_inicio'] ?? '') ?></div>
              <div class="muted">🕒 Inscripto: <?= h($i['fecha_inscripcion'] ?? '') ?></div>
            </div>
            <div class="insc-actions">
              <a class="btn btn--primary" href="/apiba-padel/torneos/ver.php?id=<?= (int)($i['torneo_id'] ?? 0) ?>">Ver</a>
              <form method="post">
                <input type="hidden" name="accion" value="cancelar_inscripcion">
                <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                <input type="hidden" name="inscripcion_id" value="<?= (int)($i['inscripcion_id'] ?? 0) ?>">
                <button type="submit" class="btn" onclick="return confirm('¿Borrar tu inscripción a este torneo?');">Borrar</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
