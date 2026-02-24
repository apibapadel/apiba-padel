<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

include '_header.php';

$msg = $_GET['msg'] ?? '';

// Filtros
$categoria = $_GET['categoria'] ?? 'ALL';
$q = trim($_GET['q'] ?? '');

// Categorías disponibles (desde tabla ranking)
$categorias = [];
try {
  $categorias = $pdo->query("
    SELECT DISTINCT categoria
    FROM ranking
    WHERE categoria IS NOT NULL AND categoria <> ''
    ORDER BY categoria
  ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $categorias = [];
}

// Query base
$sql = "
  SELECT categoria, posicion, jugador, puntos
  FROM ranking
  WHERE 1=1
";
$params = [];

if ($categoria !== '' && $categoria !== 'ALL') {
  $sql .= " AND categoria = ? ";
  $params[] = $categoria;
}
if ($q !== '') {
  $sql .= " AND jugador LIKE ? ";
  $params[] = "%".$q."%";
}

$sql .= " ORDER BY categoria ASC, posicion ASC ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Ranking (Admin)</h2>
<p class="muted">Ranking por <b>categoría del torneo</b> (tabla <code>ranking</code>). Se actualiza al cargar puntos y también podés forzar con “Recalcular”.</p>

<?php if ($msg === 'recalc_ok'): ?>
  <div class="msg">✅ Ranking recalculado correctamente.</div>
<?php elseif ($msg): ?>
  <div class="msg">✅ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card">
  <div class="toolbar" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
    <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
      <div class="field">
        <label>Categoría</label>
        <select name="categoria">
          <option value="ALL">Todas</option>
          <?php foreach($categorias as $c): ?>
            <?php $cat = $c['categoria']; ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria===$cat?'selected':'' ?>>
              <?= htmlspecialchars($cat) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field" style="min-width:260px;">
        <label>Buscar jugador</label>
        <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Ej: Martínez Juan">
      </div>

      <button class="btn btn-sm" type="submit">Filtrar</button>
      <a class="btn btn-soft btn-sm" href="ranking.php">Limpiar</a>
    </form>

    <div style="margin-left:auto; display:flex; gap:10px; flex-wrap:wrap;">
      <a class="btn btn-ok btn-sm" href="/apiba-padel/admin/recalcular_ranking.php"
         onclick="return confirm('¿Recalcular ranking completo ahora?');">
        🔄 Recalcular
      </a>
      <a class="btn btn-soft btn-sm" href="/apiba-padel/ranking/" target="_blank">Ver ranking (público)</a>
    </div>
  </div>
</div>

<br>

<?php if (empty($rows)): ?>
  <div class="card">⚠️ No hay ranking generado todavía (o no hay resultados con esos filtros).</div>
<?php else: ?>
  <?php
    $catActual = '';
    $tablaAbierta = false;

    foreach ($rows as $r):
      $cat = trim((string)$r['categoria']);

      if ($catActual !== $cat):
        if ($tablaAbierta) {
          echo "</tbody></table></div><br>";
        }
        $tablaAbierta = true;
        $catActual = $cat;
  ?>
        <h3 style="margin:12px 0 8px;"><?= htmlspecialchars($catActual) ?></h3>
        <div style="overflow:auto;">
          <table class="table">
            <thead>
              <tr>
                <th style="width:90px;">Posición</th>
                <th>Jugador</th>
                <th style="width:140px;">Puntos</th>
              </tr>
            </thead>
            <tbody>
      <?php endif; ?>

      <tr>
        <td><b>#<?= (int)$r['posicion'] ?></b></td>
        <td><?= htmlspecialchars($r['jugador']) ?></td>
        <td><b><?= (int)$r['puntos'] ?></b></td>
      </tr>

    <?php endforeach;

    if ($tablaAbierta) {
      echo "</tbody></table></div>";
    }
  ?>
<?php endif; ?>

<?php include '_footer.php'; ?>
