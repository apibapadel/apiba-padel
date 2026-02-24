<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

$stmt = $pdo->prepare("SELECT * FROM torneos WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$t) die("Torneo no encontrado");


$estado_actual = trim((string)($t['estado'] ?? ''));
$estado_low = (function_exists('mb_strtolower') ? mb_strtolower($estado_actual, 'UTF-8') : strtolower($estado_actual));
$bloqueado = ($estado_low === 'en curso' || $estado_low === 'encurso');

$err = null;

$categorias = [
  '4TA CATEGORIA CABALLEROS',
  '5TA CATEGORIA CABALLEROS',
  '6TA CATEGORIA CABALLEROS',
  '7MA CATEGORIA CABALLEROS',
  '4TA CATEGORIA DAMAS',
  '5TA CATEGORIA DAMAS',
  '6TA CATEGORIA DAMAS',
  '7MA CATEGORIA DAMAS',
];

$sedes = [
  "90´S PADEL",
  "FRAY PADEL",
  "LA QUINTA PADEL",
  "CASBAS PADEL",
];

// ✅ Estados EXACTOS como pediste
$estados = ['Abierto','En Curso','Finalizado','Cancelado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

if ($bloqueado) {
    header("Location: torneos.php?msg=" . urlencode("🔒 Este torneo está En Curso y no se puede editar."));
    exit;
}

    $categoria = trim($_POST['categoria'] ?? '');
    $sede = trim($_POST['sede'] ?? '');
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
    $estado = trim($_POST['estado'] ?? '');

    // Nombre = categoría
    $nombre = $categoria;

    if ($categoria === '' || $sede === '' || $fecha_inicio === '' || $estado === '') {
        $err = "Completá todos los campos obligatorios.";
    } else {
        if (!in_array($categoria, $categorias, true)) $err = "Categoría inválida.";
        if (!in_array($sede, $sedes, true)) $err = "Sede inválida.";
        if (!in_array($estado, $estados, true)) $err = "Estado inválido.";

        if (!$err) {
            $stmt = $pdo->prepare("
              UPDATE torneos
              SET nombre=?, categoria=?, sede=?, fecha_inicio=?, estado=?
              WHERE id=?
            ");
            $stmt->execute([$nombre, $categoria, $sede, $fecha_inicio, $estado, $id]);

            header("Location: torneos.php?msg=" . urlencode("Torneo actualizado correctamente."));
            exit;
        }
    }
}

include '_header.php';
?>


if ($bloqueado) {
  echo "<div class='card' style='max-width:760px;margin:18px auto;'>
          <h2 style='margin:0 0 8px 0;'>🔒 Torneo en curso</h2>
          <p style='margin:0;color:var(--muted);'>Este torneo está en estado <b>En Curso</b>, por lo tanto <b>no se puede editar</b>.</p>
          <div style='margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;'>
            <a class='btn btn-soft btn-sm' href='torneos.php'>⬅ Volver a Torneos</a>
            <a class='btn btn-soft btn-sm btn-verweb' href='/apiba-padel/torneos/ver.php?id=".(int)$t['id']."' target='_blank'>VER WEB</a>
          </div>
        </div>";
  include '_footer.php';
  exit;
}



<h2>Editar torneo</h2>
<p class="muted">ID #<?= (int)$t['id'] ?></p>

<?php if ($err): ?>
  <div class="msg" style="background:#ffe9e9;border-color:#f2a2a2">❌ <?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" class="grid" autocomplete="off">

    <div class="field">
      <label>Categoría (Nombre del torneo) *</label>
      <?php $vcat = $_POST['categoria'] ?? ($t['categoria'] ?? ''); ?>
      <select name="categoria" required>
        <option value="">-- Seleccionar --</option>
        <?php foreach($categorias as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>" <?= $vcat===$c?'selected':'' ?>>
            <?= htmlspecialchars($c) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label>Sede *</label>
      <?php $vsede = $_POST['sede'] ?? ($t['sede'] ?? ''); ?>
      <select name="sede" required>
        <option value="">-- Seleccionar --</option>
        <?php foreach($sedes as $s): ?>
          <option value="<?= htmlspecialchars($s) ?>" <?= $vsede===$s?'selected':'' ?>>
            <?= htmlspecialchars($s) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label>Fecha inicio *</label>
      <input name="fecha_inicio" type="date" required value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? $t['fecha_inicio']) ?>">
    </div>

    <div class="field">
      <label>Estado *</label>
      <?php $vest = $_POST['estado'] ?? ($t['estado'] ?? 'Abierto'); ?>
      <select name="estado" required>
        <?php foreach($estados as $e): ?>
          <option value="<?= htmlspecialchars($e) ?>" <?= $vest===$e?'selected':'' ?>>
            <?= htmlspecialchars($e) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="grid-column:1/-1; display:flex; gap:10px; flex-wrap:wrap">
      <button class="btn btn-ok" type="submit">Guardar cambios</button>
      <a class="btn btn-soft" href="fixture.php?torneo=<?= (int)$t['id'] ?>">Administrar fixture</a>
      <a class="btn btn-soft" href="torneos.php">Volver</a>
    </div>

  </form>
</div>

<?php include '_footer.php'; ?>
