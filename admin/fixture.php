<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

function column_exists(PDO $pdo, string $table, string $col): bool {
  try {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    return (bool)$st->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) { return false; }
}
function normalize_estado(string $s): string {
  $e = mb_strtolower(trim($s));
  $e = str_replace(['_', '-'], ' ', $e);
  $e = preg_replace('/\s+/', ' ', $e);
  return $e;
}
function get_enum_values(PDO $pdo, string $table, string $col): array {
  try {
    $st = $pdo->query("SHOW COLUMNS FROM `$table` LIKE ".$pdo->quote($col));
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) return [];
    $type = (string)($row['Type'] ?? '');
    if (!str_starts_with(strtolower($type), 'enum(')) return [];
    $inside = substr($type, 5, -1);

    $vals = [];
    $cur = '';
    $inQ = false;
    for ($i=0; $i<strlen($inside); $i++){
      $ch = $inside[$i];
      if ($ch === "'" && ($i===0 || $inside[$i-1] !== '\\')) { $inQ = !$inQ; continue; }
      if ($ch === ',' && !$inQ) { $vals[] = $cur; $cur=''; continue; }
      $cur .= $ch;
    }
    if ($cur !== '') $vals[] = $cur;

    $vals = array_map(fn($v)=>str_replace("\\'", "'", trim($v)), $vals);
    return array_values(array_filter($vals, fn($v)=>$v!=='')); 
  } catch (Exception $e) { return []; }
}
function pick_save_value(string $target_norm, array $enum_vals): string {
  if (!$enum_vals) return $target_norm;
  $map = [];
  foreach ($enum_vals as $v) $map[normalize_estado($v)] = $v;
  if (isset($map[$target_norm])) return $map[$target_norm];

  if ($target_norm === 'en curso') {
    foreach ($enum_vals as $v) {
      $nv = normalize_estado($v);
      if ($nv === 'en curso' || str_contains($nv, 'curso')) return $v;
    }
  }
  if ($target_norm === 'abierto') {
    foreach ($enum_vals as $v) {
      $nv = normalize_estado($v);
      if ($nv === 'abierto' || str_contains($nv, 'abiert') || $nv === 'open') return $v;
    }
  }
  return $enum_vals[0] ?? $target_norm;
}

$torneo_id = (int)($_GET['torneo'] ?? 0);
if ($torneo_id <= 0) die("ID de torneo inválido");

// Torneo
$stmt = $pdo->prepare("SELECT * FROM torneos WHERE id=? LIMIT 1");
$stmt->execute([$torneo_id]);
$torneo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$torneo) die("Torneo no encontrado");

$err = null;
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['_action'] ?? '';

  if ($action === 'abrir_torneo') {
    if (!column_exists($pdo,'torneos','estado')) {
      header("Location: fixture.php?torneo=".$torneo_id."&msg=".urlencode("No existe torneos.estado."));
      exit;
    }
    $enum_vals = get_enum_values($pdo,'torneos','estado');
    $saveValue = pick_save_value('en curso', $enum_vals);

    try {
      $up = $pdo->prepare("UPDATE torneos SET estado=? WHERE id=? LIMIT 1");
      $up->execute([$saveValue, $torneo_id]);
    } catch (Exception $e) {}

    header("Location: generar_torneo.php?id=".$torneo_id);
    exit;
  }

  if ($action === 'add') {
    $fecha = trim($_POST['fecha'] ?? '');
    $horario = trim($_POST['horario'] ?? '');
    $cancha = trim($_POST['cancha'] ?? '');
    $local = trim($_POST['local'] ?? '');
    $visitante = trim($_POST['visitante'] ?? '');

    if ($fecha === '' || $horario === '' || $cancha === '' || $local === '' || $visitante === '') {
      $err = "Completá todos los campos del partido.";
    } else {
      $stmt = $pdo->prepare("
        INSERT INTO fixture (torneo_id, fecha, horario, cancha, local, visitante)
        VALUES (?,?,?,?,?,?)
      ");
      $stmt->execute([$torneo_id, $fecha, $horario, $cancha, $local, $visitante]);

      header("Location: fixture.php?torneo=".$torneo_id."&msg=".urlencode("Partido agregado al fixture."));
      exit;
    }
  }

  if ($action === 'delete') {
    $fid = (int)($_POST['fixture_id'] ?? 0);
    if ($fid > 0) {
      $stmt = $pdo->prepare("DELETE FROM fixture WHERE id=? AND torneo_id=? LIMIT 1");
      $stmt->execute([$fid, $torneo_id]);

      header("Location: fixture.php?torneo=".$torneo_id."&msg=".urlencode("Partido eliminado."));
      exit;
    }
  }
}

// Cargar fixture
$stmt = $pdo->prepare("SELECT * FROM fixture WHERE torneo_id=? ORDER BY fecha, horario, id");
$stmt->execute([$torneo_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por fecha
$porFecha = [];
foreach($rows as $r){
  $porFecha[$r['fecha']][] = $r;
}

include '_header.php';
?>

<h2>Fixture</h2>
<p class="muted">
  <b><?= htmlspecialchars($torneo['nombre']) ?></b> —
  Categoría: <span class="badge"><?= htmlspecialchars($torneo['categoria']) ?></span> —
  ID #<?= (int)$torneo_id ?>
</p>

<?php if ($msg): ?>
  <div class="msg">✅ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($err): ?>
  <div class="msg" style="background:#ffe9e9;border-color:#f2a2a2">❌ <?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="card" style="display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap;">
  <div style="font-weight:900;">Acciones</div>

  <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
    <form method="post" style="margin:0;">
      <input type="hidden" name="_action" value="abrir_torneo">
      <button class="btn btn-ok" type="submit" onclick="return confirm('¿Abrir el torneo otra vez?');">
        ABRIR TORNEO
      </button>
    </form>

    <a class="btn btn-soft" href="generar_torneo.php?id=<?= (int)$torneo_id ?>">Volver a zonas</a>
    <a class="btn btn-soft" href="torneos.php">Volver a torneos</a>
    <a class="btn btn-soft" href="/apiba-padel/torneos/ver.php?id=<?= (int)$torneo_id ?>" target="_blank">Ver público</a>
  </div>
</div>

<br>

<div class="card">
  <h3 style="margin-top:0">➕ Agregar partido</h3>
  <form method="post" class="grid" autocomplete="off">
    <input type="hidden" name="_action" value="add">

    <div class="field">
      <label>Fecha *</label>
      <input name="fecha" type="date" required value="<?= htmlspecialchars($_POST['fecha'] ?? '') ?>">
    </div>

    <div class="field">
      <label>Horario *</label>
      <input name="horario" type="time" required value="<?= htmlspecialchars($_POST['horario'] ?? '') ?>">
    </div>

    <div class="field">
      <label>Cancha *</label>
      <input name="cancha" required value="<?= htmlspecialchars($_POST['cancha'] ?? '') ?>" placeholder="Ej: Cancha 1">
    </div>

    <div class="field">
      <label>Local *</label>
      <input name="local" required value="<?= htmlspecialchars($_POST['local'] ?? '') ?>" placeholder="Ej: Pareja A">
    </div>

    <div class="field">
      <label>Visitante *</label>
      <input name="visitante" required value="<?= htmlspecialchars($_POST['visitante'] ?? '') ?>" placeholder="Ej: Pareja B">
    </div>

    <div style="grid-column:1/-1; display:flex; gap:10px; flex-wrap:wrap">
      <button class="btn btn-ok" type="submit">Agregar</button>
    </div>
  </form>
</div>

<br>

<?php if (empty($rows)): ?>
  <div class="card">⚠️ Todavía no hay partidos en el fixture.</div>
<?php else: ?>

  <?php foreach($porFecha as $fecha => $partidos): ?>
    <h3 style="margin-top:18px">📅 <?= htmlspecialchars($fecha) ?></h3>

    <table>
      <tr>
        <th style="width:110px">Horario</th>
        <th style="width:120px">Cancha</th>
        <th>Partido</th>
        <th style="width:160px">Acciones</th>
      </tr>

      <?php foreach($partidos as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['horario']) ?></td>
          <td><span class="badge"><?= htmlspecialchars($p['cancha']) ?></span></td>
          <td><b><?= htmlspecialchars($p['local']) ?></b> vs <b><?= htmlspecialchars($p['visitante']) ?></b></td>
          <td style="white-space:nowrap">
            <form method="post" style="display:inline"
                  onsubmit="return confirm('¿Eliminar este partido del fixture?');">
              <input type="hidden" name="_action" value="delete">
              <input type="hidden" name="fixture_id" value="<?= (int)$p['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endforeach; ?>

<?php endif; ?>

<?php include '_footer.php'; ?>