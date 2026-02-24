<?php
$section = 'public';
$active = 'ranking';
$page_title = 'Ranking - APiBA Pádel';

require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

/* Categorías disponibles desde la tabla ranking */
$cats = $pdo->query("
  SELECT DISTINCT categoria
  FROM ranking
  WHERE categoria IS NOT NULL AND categoria <> ''
  ORDER BY categoria ASC
")->fetchAll(PDO::FETCH_ASSOC);

$catSel = $_GET['cat'] ?? '';
if ($catSel === '' && !empty($cats)) {
  $catSel = (string)$cats[0]['categoria'];
}

/* Traer ranking de la categoría seleccionada */
$rows = [];
if ($catSel !== '') {
  $stmt = $pdo->prepare("
    SELECT
      r.posicion,
      r.jugador,
      r.puntos,
      j.id AS jugador_id,
      j.foto
    FROM ranking r
    LEFT JOIN jugadores j
      ON TRIM(CONCAT(j.apellido,' ',j.nombre)) = TRIM(r.jugador)
    WHERE r.categoria = ?
    ORDER BY r.posicion ASC
  ");
  $stmt->execute([$catSel]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* Helper foto jugador (USA TU PLACEHOLDER REAL) */
function jugador_foto_url($foto) {
  $f = trim((string)$foto);

  // Placeholder oficial del sitio
  if ($f === '') {
    return '/apiba-padel/assets/img/user-placeholder.png';
  }

  // Si ya viene con ruta absoluta
  if (str_starts_with($f, '/')) {
    return $f;
  }

  // Foto subida
  return '/apiba-padel/uploads/jugadores/' . $f;
}
?>

<div class="card">
  <div class="card__body">

    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
      <div>
        <h1 style="margin:0;">📊 Ranking</h1>
        <div class="muted" style="margin-top:6px;">Por categoría</div>
      </div>

      <div class="badge" style="opacity:.9;">Por categoría</div>
    </div>

    <form method="get" style="margin-top:12px;">
      <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
        <div>
          <label class="muted" style="display:block; margin-bottom:6px;">Categoría:</label>
          <select name="cat" class="ranking-select" onchange="this.form.submit()">
            <?php foreach ($cats as $c): ?>
              <?php $cname = (string)$c['categoria']; ?>
              <option value="<?= htmlspecialchars($cname) ?>" <?= ($cname === $catSel ? 'selected' : '') ?>>
                <?= htmlspecialchars($cname) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </form>

    <div style="overflow:auto; margin-top:14px;">
      <table class="table">
        <thead>
          <tr>
            <th style="width:90px;">PUESTO</th>
            <th>JUGADOR</th>
            <th style="width:140px; text-align:right;">PUNTOS</th>
          </tr>
        </thead>
        <tbody>

          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="3" class="muted">
                No hay jugadores con puntos cargados en esta categoría.
              </td>
            </tr>
          <?php else: ?>

            <?php foreach ($rows as $r): ?>
              <?php
                $puesto = (int)($r['posicion'] ?? 0);
                $nombre = (string)($r['jugador'] ?? '');
                $pts    = (int)($r['puntos'] ?? 0);
                $foto   = jugador_foto_url($r['foto'] ?? '');

                $jid = (int)($r['jugador_id'] ?? 0);
                $nombreHtml = htmlspecialchars($nombre);
                if ($jid > 0) {
                  $nombreHtml = '<a href="/apiba-padel/public/jugador.php?id='.$jid.'" style="color:inherit;">'.$nombreHtml.'</a>';
                }
              ?>

              <tr>
                <td><b><?= $puesto ?></b></td>

                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <img
                      src="<?= htmlspecialchars($foto) ?>"
                      alt=""
                      style="width:34px;height:34px;border-radius:10px;object-fit:cover;border:1px solid rgba(255,255,255,.12);"
                    >
                    <div style="line-height:1.15;">
                      <div style="font-weight:700;">
                        <?= $nombreHtml ?>
                      </div>
                    </div>
                  </div>
                </td>

                <td style="text-align:right;">
                  <span class="badge badge--ok" style="padding:6px 10px; border-radius:999px;">
                    <?= $pts ?>
                  </span>
                </td>
              </tr>

            <?php endforeach; ?>

          <?php endif; ?>

        </tbody>
      </table>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
