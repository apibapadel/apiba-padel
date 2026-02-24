<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

// Jugador
$stmt = $pdo->prepare("SELECT id, apellido, nombre, email, categoria FROM jugadores WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$j) die("Jugador no encontrado");

// Detectar columna de fecha en inscripciones
$colFecha = null;
$cols = $pdo->query("SHOW COLUMNS FROM inscripciones")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    if ($c['Field'] === 'created_at') $colFecha = 'created_at';
    if ($c['Field'] === 'fecha') $colFecha = 'fecha';
}
if (!$colFecha) $colFecha = 'id';

// Inscripciones del jugador
$sql = "
  SELECT i.$colFecha AS fecha_inscripcion,
         t.id AS torneo_id, t.nombre, t.categoria, t.fecha_inicio, t.estado
  FROM inscripciones i
  JOIN torneos t ON t.id = i.torneo_id
  WHERE i.jugador_id = ?
  ORDER BY i.$colFecha DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '_header.php';

$msg = $_GET['msg'] ?? '';
?>

<h2>Inscripciones</h2>
<p class="muted">
  <b><?= htmlspecialchars($j['apellido'].' '.$j['nombre']) ?></b> —
  <?= htmlspecialchars($j['email']) ?> —
  Categoría: <?= htmlspecialchars($j['categoria'] ?? '') ?>
</p>

<?php if ($msg): ?>
  <div class="msg">✅ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if(empty($rows)): ?>
  <div class="card">⚠️ Este jugador no tiene inscripciones.</div>
<?php else: ?>
  <table>
    <tr>
      <th>Fecha</th>
      <th>Torneo</th>
      <th>Categoría</th>
      <th>Inicio</th>
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['fecha_inscripcion'] ?? '') ?></td>
        <td><b><?= htmlspecialchars($r['nombre'] ?? '') ?></b></td>
        <td><?= htmlspecialchars($r['categoria'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['fecha_inicio'] ?? '') ?></td>
        <td><span class="badge"><?= htmlspecialchars($r['estado'] ?? '') ?></span></td>
        <td style="white-space:nowrap">
          <a class="btn btn-soft btn-sm" href="inscriptos.php?id=<?= (int)$r['torneo_id'] ?>">Ver inscriptos</a>

          <form method="post" action="eliminar_inscripcion.php" style="display:inline"
                onsubmit="return confirm('¿Eliminar inscripción de este jugador en este torneo?');">
            <input type="hidden" name="jugador_id" value="<?= (int)$j['id'] ?>">
            <input type="hidden" name="torneo_id" value="<?= (int)$r['torneo_id'] ?>">
            <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<br>
<a class="btn btn-soft btn-sm" href="jugadores.php">⬅ Volver</a>

<?php include '_footer.php'; ?>
