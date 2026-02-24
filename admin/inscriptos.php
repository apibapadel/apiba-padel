<?php
$section = 'admin';
$active  = 'torneos';
$page_title = 'Inscriptos';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/_header.php';

$pdo = getDB();

function es_finalizado($estado): bool {
  return mb_strtolower(trim((string)$estado)) === 'finalizado';
}
function column_exists(PDO $pdo, string $table, string $col): bool {
  try {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    return (bool)$st->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) { return false; }
}
function foto_src($foto): string {
  $foto = trim((string)$foto);
  if ($foto === '') return '';
  if (str_starts_with($foto, '/') || str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) return $foto;
  return "/apiba-padel/uploads/jugadores/" . $foto;
}
function puntos_ranking(PDO $pdo, int $jugador_id, string $categoria): int {
  $categoria = trim((string)$categoria);
  if ($jugador_id <= 0 || $categoria === '') return 0;
  try {
    $st = $pdo->prepare("SELECT puntos FROM ranking WHERE jugador_id = ? AND categoria = ? LIMIT 1");
    $st->execute([$jugador_id, $categoria]);
    $v = $st->fetchColumn();
    return ($v !== false && $v !== null && $v !== '') ? (int)$v : 0;
  } catch (Exception $e) { return 0; }
}

/* Torneo */
$torneo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($torneo_id <= 0) {
  echo "<div class='card'><p>Torneo inválido.</p></div>";
  require_once __DIR__ . '/_footer.php';
  exit;
}

$stmt = $pdo->prepare("SELECT id, nombre, categoria, estado FROM torneos WHERE id = ? LIMIT 1");
$stmt->execute([$torneo_id]);
$torneo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$torneo) {
  echo "<div class='card'><p>No se encontró el torneo.</p></div>";
  require_once __DIR__ . '/_footer.php';
  exit;
}

$finalizado = es_finalizado($torneo['estado'] ?? '');
$cat_torneo = trim((string)($torneo['categoria'] ?? ''));

$hasFoto = column_exists($pdo, 'jugadores', 'foto');
$sf = $hasFoto ? "j.foto" : "''";

/* Selección por sesión (máximo 2) */
if (session_status() === PHP_SESSION_NONE) session_start();
$selKey = 'apiba_sel_'.$torneo_id;
if (!isset($_SESSION[$selKey]) || !is_array($_SESSION[$selKey])) $_SESSION[$selKey] = [];

/* GET: seleccionar / quitar / limpiar (sin JS para la lógica) */
if (isset($_GET['sel'])) {
  $jid = (int)$_GET['sel'];
  $sel = array_values(array_filter(array_map('intval', $_SESSION[$selKey])));
  if ($jid > 0 && !in_array($jid, $sel, true)) {
    if (count($sel) >= 2) array_shift($sel);
    $sel[] = $jid;
    $_SESSION[$selKey] = $sel;
  }
  header("Location: inscriptos.php?id=".$torneo_id."&keep=1");
  exit;
}

if (isset($_GET['unsel'])) {
  $jid = (int)$_GET['unsel'];
  $sel = array_values(array_filter(array_map('intval', $_SESSION[$selKey])));
  $sel = array_values(array_filter($sel, fn($v) => (int)$v !== $jid));
  $_SESSION[$selKey] = $sel;
  header("Location: inscriptos.php?id=".$torneo_id."&keep=1");
  exit;
}

if (isset($_GET['clear_sel'])) {
  $_SESSION[$selKey] = [];
  header("Location: inscriptos.php?id=".$torneo_id."&keep=1");
  exit;
}

/* POST: unir / desunir / generar */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? '';

  if ($accion === 'unir') {
    $sel = array_values(array_filter(array_map('intval', $_SESSION[$selKey] ?? [])));

    if (count($sel) !== 2) {
      header("Location: inscriptos.php?id=".$torneo_id."&msg=".urlencode("Tenés que seleccionar EXACTAMENTE 2 jugadores.")."&keep=1");
      exit;
    }

    $j1 = (int)$sel[0];
    $j2 = (int)$sel[1];

    // evitar unir si alguno ya está en equipo
    $st = $pdo->prepare("
      SELECT 1
      FROM torneo_equipos
      WHERE torneo_id = ?
        AND (jugador1_id IN (?,?) OR jugador2_id IN (?,?))
      LIMIT 1
    ");
    $st->execute([$torneo_id, $j1, $j2, $j1, $j2]);
    if ($st->fetchColumn()) {
      header("Location: inscriptos.php?id=".$torneo_id."&msg=".urlencode("Uno de los jugadores ya está en un equipo.")."&keep=1");
      exit;
    }

    // categoria anotada (si existe)
    $cat1 = $cat_torneo;
    $cat2 = $cat_torneo;
    try {
      if (column_exists($pdo, 'inscripciones', 'categoria_anotada')) {
        $stc = $pdo->prepare("SELECT jugador_id, categoria_anotada FROM inscripciones WHERE torneo_id = ? AND jugador_id IN (?,?)");
        $stc->execute([$torneo_id, $j1, $j2]);
        foreach ($stc->fetchAll(PDO::FETCH_ASSOC) as $r) {
          if ((int)$r['jugador_id'] === $j1 && trim((string)$r['categoria_anotada']) !== '') $cat1 = trim((string)$r['categoria_anotada']);
          if ((int)$r['jugador_id'] === $j2 && trim((string)$r['categoria_anotada']) !== '') $cat2 = trim((string)$r['categoria_anotada']);
        }
      }
    } catch (Exception $e) {}

    $ins = $pdo->prepare("
      INSERT INTO torneo_equipos (torneo_id, jugador1_id, jugador2_id, categoria_j1, categoria_j2)
      VALUES (?,?,?,?,?)
    ");
    $ins->execute([$torneo_id, $j1, $j2, $cat1, $cat2]);

    $_SESSION[$selKey] = [];

    header("Location: inscriptos.php?id=".$torneo_id."&msg=".urlencode("Equipo creado.")."&keep=1");
    exit;
  }

  if ($accion === 'desunir') {
    $equipo_id = (int)($_POST['equipo_id'] ?? 0);
    if ($equipo_id <= 0) {
      header("Location: inscriptos.php?id=".$torneo_id."&msg=".urlencode("Equipo inválido."));
      exit;
    }
    $del = $pdo->prepare("DELETE FROM torneo_equipos WHERE id = ? AND torneo_id = ? LIMIT 1");
    $del->execute([$equipo_id, $torneo_id]);

    header("Location: inscriptos.php?id=".$torneo_id."&msg=".urlencode("Equipo desarmado."));
    exit;
  }

  if ($accion === 'generar_torneo') {
    header("Location: inscriptos.php?id=".$torneo_id."&msg=".urlencode("OK: Generar Torneo (pendiente de lógica)."));
    exit;
  }
}

/* Equipos (se mantiene aunque acá no se muestren) */
$equipos = [];
try {
  $st = $pdo->prepare("
    SELECT
      e.id AS equipo_id,
      e.jugador1_id, e.jugador2_id,
      e.categoria_j1, e.categoria_j2,
      j1.apellido AS j1_apellido, j1.nombre AS j1_nombre, ".($hasFoto ? "j1.foto" : "''")." AS j1_foto,
      j2.apellido AS j2_apellido, j2.nombre AS j2_nombre, ".($hasFoto ? "j2.foto" : "''")." AS j2_foto
    FROM torneo_equipos e
    JOIN jugadores j1 ON j1.id = e.jugador1_id
    JOIN jugadores j2 ON j2.id = e.jugador2_id
    WHERE e.torneo_id = ?
    ORDER BY e.id ASC
  ");
  $st->execute([$torneo_id]);
  $equipos = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $equipos = []; }

/* Individuales */
$inscriptos = [];
try {
  $hasCatAnotada = column_exists($pdo, 'inscripciones', 'categoria_anotada');
  $catField = $hasCatAnotada ? "i.categoria_anotada" : "'' AS categoria_anotada";

  $stmt = $pdo->prepare("
    SELECT 
      i.id AS insc_id,
      j.id AS jugador_id,
      j.apellido,
      j.nombre,
      j.categoria,
      $sf AS foto,
      $catField
    FROM inscripciones i
    JOIN jugadores j ON j.id = i.jugador_id
    WHERE i.torneo_id = ?
      AND j.id NOT IN (
        SELECT jugador1_id FROM torneo_equipos WHERE torneo_id = ?
        UNION
        SELECT jugador2_id FROM torneo_equipos WHERE torneo_id = ?
      )
    ORDER BY j.apellido ASC, j.nombre ASC
  ");
  $stmt->execute([$torneo_id, $torneo_id, $torneo_id]);
  $inscriptos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $inscriptos = []; }

/* Selección actual */
$sel_actual = array_values(array_filter(array_map('intval', $_SESSION[$selKey] ?? [])));
?>

<style>
  .btn-select-like-unir { /* mismo formato que btn-ok */
    /* no toca nada, usa .btn.btn-ok */
  }
</style>

<!-- JS MINIMO solo para NO MOVER la pantalla -->
<script>
(function(){
  const key = "apiba_scroll_keep_<?= (int)$torneo_id ?>";

  // si venimos con keep=1, restauramos scroll
  try {
    const url = new URL(window.location.href);
    if (url.searchParams.get("keep") === "1") {
      const y = sessionStorage.getItem(key);
      if (y !== null) window.scrollTo(0, parseInt(y, 10) || 0);
      sessionStorage.removeItem(key);

      // limpiar keep=1 de la URL sin recargar
      url.searchParams.delete("keep");
      window.history.replaceState({}, "", url.toString());
    }
  } catch(e){}

  // guardar scroll antes de click en seleccionar/quitar/limpiar
  window.APIBA_saveScroll = function(){
    try { sessionStorage.setItem(key, String(window.scrollY || 0)); } catch(e){}
    return true;
  };
})();
</script>

<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;">Inscriptos</h2>
      <div style="color:var(--muted); margin-top:4px;">
        Torneo: <b><?= htmlspecialchars($torneo['nombre'] ?? ('#'.$torneo['id'])) ?></b> —
        Categoría: <b><?= htmlspecialchars($torneo['categoria']) ?></b> —
        Estado: <b><?= htmlspecialchars($torneo['estado'] ?? '') ?></b>
      </div>
    </div>

    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
      <a href="inscribir_jugador.php?torneo_id=<?= (int)$torneo_id ?>" class="btn btn-inscribir">Inscribir jugador</a>

      <!-- ✅ NUEVO BOTÓN: Ver equipos unidos -->
      <a href="equipos_unidos.php?id=<?= (int)$torneo_id ?>" class="btn btn-secondary">Ver equipos unidos</a>

      <?php if ($finalizado): ?>
        <a href="/apiba-padel/admin/torneo_puntos.php?id=<?= (int)$torneo_id ?>" class="btn btn-ok">🏁 Cargar/Editar puntos</a>
      <?php else: ?>
        <span class="badge" style="opacity:.85;">Puntos: solo finalizado</span>
      <?php endif; ?>

      <a href="torneos.php" class="btn btn-volver">Volver a torneos</a>
    </div>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success" style="margin-top:12px;"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <hr style="margin:18px 0; opacity:.2;">

  <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
    <h3 style="margin:0;">Individuales</h3>
    <span class="tag">Seleccioná 2 y unir</span>
  </div>

  <?php if (count($sel_actual) > 0): ?>
    <div style="margin-top:12px; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
      <div class="mini-muted">
        Seleccionados: <?= implode(', ', array_map(fn($x)=>"#".$x, $sel_actual)) ?>
      </div>
      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <a class="btn btn-secondary" href="inscriptos.php?id=<?= (int)$torneo_id ?>&clear_sel=1" onclick="return APIBA_saveScroll()">Limpiar</a>
        <form method="post" style="margin:0;">
          <input type="hidden" name="accion" value="unir">
          <button class="btn btn-ok" type="submit">UNIR JUGADORES</button>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <?php if (count($inscriptos) === 0): ?>
    <p style="margin-top:10px; color:var(--muted);">No hay individuales disponibles.</p>
  <?php else: ?>
    <div style="overflow:auto; margin-top:12px;">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Jugador</th>
            <th>Cat. anotada</th>
            <th>Pts (cat. anotada)</th>
            <th style="width:1%;">Seleccionar</th>
            <th style="width:1%;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inscriptos as $insc): ?>
            <?php
              $jid = (int)$insc['jugador_id'];
              $yaSel = in_array($jid, $sel_actual, true);
            ?>
            <tr>
              <td><?= $jid ?></td>
              <td><b><?= htmlspecialchars($insc['apellido'].' '.$insc['nombre']) ?></b></td>
              <td><?= htmlspecialchars(trim((string)($insc['categoria_anotada'] ?? $cat_torneo))) ?></td>
              <td><b><?= (int)puntos_ranking($pdo, $jid, trim((string)($insc['categoria_anotada'] ?? $cat_torneo))) ?></b></td>

              <td style="white-space:nowrap;">
                <?php if ($yaSel): ?>
                  <span class="badge" style="opacity:.85;">OK</span>
                <?php else: ?>
                  <!-- MISMO FORMATO QUE UNIR -->
                  <a class="btn btn-ok btn-select-like-unir"
                     href="inscriptos.php?id=<?= (int)$torneo_id ?>&sel=<?= $jid ?>"
                     onclick="return APIBA_saveScroll()">
                    Seleccionar
                  </a>
                <?php endif; ?>
              </td>

              <td style="white-space:nowrap;">
                <a class="btn btn-danger"
                   href="eliminar_inscripcion.php?jugador_id=<?= $jid ?>&torneo_id=<?= (int)$torneo_id ?>"
                   onclick="return confirm('¿Borrar inscripción?');">
                  Borrar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
