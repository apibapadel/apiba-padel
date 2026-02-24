<?php
$section = 'public';
$active = 'ranking';
$page_title = 'Ranking - APiBA Pádel';

require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

function normalizar_categoria_param(string $cat): string {
  $map = [
    '4TA Caballeros' => '4TA CATEGORIA CABALLEROS',
    '5TA Caballeros' => '5TA CATEGORIA CABALLEROS',
    '6TA Caballeros' => '6TA CATEGORIA CABALLEROS',
    '7MA Caballeros' => '7MA CATEGORIA CABALLEROS',
    '4TA Damas' => '4TA CATEGORIA DAMAS',
    '5TA Damas' => '5TA CATEGORIA DAMAS',
    '6TA Damas' => '6TA CATEGORIA DAMAS',
    '7MA Damas' => '7MA CATEGORIA DAMAS',
  ];
  return $map[trim($cat)] ?? trim($cat);
}

function placeholder_list(): array {
  return [
    '/apiba-padel/assets/IMG/user-placeholder.PNG',
    '/apiba-padel/assets/img/user-placeholder.png',
    '/apiba-padel/assets/img/user-placeholder.PNG',
    '/apiba-padel/assets/IMG/user-placeholder.png',
  ];
}

function foto_url($foto): string {
  $ph = placeholder_list()[0];

  $f = trim((string)$foto);
  if ($f === '') return $ph;

  if (preg_match('~^https?://~i', $f)) return $f;
  if (str_starts_with($f, '/')) return $f;

  if (str_starts_with($f, 'uploads/')) return '/apiba-padel/' . $f;
  if (str_starts_with($f, 'apiba-padel/uploads/')) return '/' . $f;

  return '/apiba-padel/uploads/jugadores/' . $f;
}

$cats = $pdo->query("
  SELECT DISTINCT categoria
  FROM ranking
  WHERE categoria IS NOT NULL AND categoria <> ''
  ORDER BY categoria ASC
")->fetchAll(PDO::FETCH_ASSOC);

$catSel = normalizar_categoria_param($_GET['cat'] ?? '');
if ($catSel === '' && !empty($cats)) $catSel = (string)$cats[0]['categoria'];

if (!empty($cats)) {
  $existe = false;
  foreach ($cats as $c) {
    if ((string)$c['categoria'] === $catSel) { $existe = true; break; }
  }
  if (!$existe) $catSel = (string)$cats[0]['categoria'];
}

$rows = [];
if ($catSel !== '') {
  $stmt = $pdo->prepare("
    SELECT
      r.posicion,
      r.jugador,
      r.puntos,
      j.foto,
      rp.posicion AS prev_pos
    FROM ranking r
    LEFT JOIN jugadores j ON j.id = r.jugador_id
    LEFT JOIN ranking_prev rp
      ON rp.categoria = r.categoria
     AND rp.jugador_id = r.jugador_id
    WHERE r.categoria = ?
    ORDER BY r.puntos DESC, r.jugador ASC
  ");
  $stmt->execute([$catSel]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ✅ Recalcular puestos por puntos (empates comparten puesto: 1,1,3...)
$computed = [];
$rank = 0; $i = 0; $lastPts = null;
foreach ($rows as $r) {
  $i++;
  $pts = (int)($r['puntos'] ?? 0);
  if ($lastPts === null || $pts !== $lastPts) { $rank = $i; $lastPts = $pts; }
  $r['_rank'] = $rank;
  $computed[] = $r;
}
$rows = $computed;


$placeholders = placeholder_list();
$ph0 = $placeholders[0];
$phJson = htmlspecialchars(json_encode($placeholders), ENT_QUOTES, 'UTF-8');
?>

<style>
.ranking-anim{opacity:0;transform:translateY(10px);transition:.25s}
.ranking-anim.is-in{opacity:1;transform:none}

.delta{display:inline-flex;align-items:center;gap:6px;font-weight:800;opacity:.9}
.delta small{font-weight:700;opacity:.8}

.tr-top1{background:rgba(45,179,31,.10)}
.tr-top2{background:rgba(29,78,216,.06)}
.tr-top3{background:rgba(124,58,237,.06)}

/* ✅ PUNTOS: número grande + “-Pts” chico y abajo */
.points-wrap{
  display:inline-flex;
  flex-direction:column;
  align-items:flex-end; /* alineado a la derecha */
  line-height:1.05;
}
.points-num{
  font-weight:900;
  font-size:1.35em;
  letter-spacing:.3px;
}
.points-suf{
  font-size:.75em;
  opacity:.7;
  margin-top:2px; /* baja el -Pts */
}
</style>

<div class="card">
  <div class="card__body">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
      <div>
        <h1 style="margin:0;">📊 Ranking</h1>
        <div class="muted">Por categoría · con variación de puesto</div>
      </div>
      <div class="badge">Ranking oficial</div>
    </div>

    <form method="get" id="rankingForm" style="margin-top:12px;">
      <label class="muted">Categoría</label>
      <select name="cat" id="catSelect" class="ranking-select">
        <?php foreach($cats as $c): ?>
          <option value="<?= htmlspecialchars($c['categoria']) ?>" <?= $c['categoria']===$catSel?'selected':'' ?>>
            <?= htmlspecialchars($c['categoria']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <div id="rankingWrap" class="ranking-anim" style="margin-top:18px; overflow:auto;">
      <table class="table">
        <thead>
          <tr>
            <th width="160">PUESTO</th>
            <th>JUGADOR</th>
            <th width="160" style="text-align:right;">PUNTOS</th>
          </tr>
        </thead>
        <tbody>

        <?php foreach($rows as $r):
          $p = (int)($r['_rank'] ?? 0);
          $prev = ($r['prev_pos'] !== null) ? (int)$r['prev_pos'] : 0;

          if ($prev > 0) {
            $diff = $prev - $p;
            if ($diff > 0) $deltaHtml = "↑ <small>+$diff</small>";
            elseif ($diff < 0) $deltaHtml = "↓ <small>$diff</small>";
            else $deltaHtml = "→ <small>0</small>";
          } else {
            $deltaHtml = "🆕 <small>nuevo</small>";
          }

                    $cls = ($p===1?'tr-top1':($p===2?'tr-top2':($p===3?'tr-top3':'')));

          $fotoSrc = foto_url($r['foto'] ?? '');
        ?>
          <tr class="<?= $cls ?>">
            <td>
              <?= '<b>#'.$p.'</b>' ?>
              <div class="muted" style="margin-top:6px;">
                <span class="delta"><?= $deltaHtml ?></span>
              </div>
            </td>

            <td>
              <div style="display:flex;align-items:center;gap:14px;">
                <img
                  src="<?= htmlspecialchars($fotoSrc) ?>"
                  alt=""
                  data-phlist="<?= $phJson ?>"
                  style="width:68px;height:68px;border-radius:14px;object-fit:cover;border:1px solid rgba(255,255,255,.12);"
                  onerror="window.APIBA_imgFallback(this)"
                >
                <div style="font-weight:800;font-size:1.05em">
                  <?= htmlspecialchars($r['jugador']) ?>
                </div>
              </div>
            </td>

            <td style="text-align:right">
              <span class="points-wrap">
                <span class="points-num"><?= (int)$r['puntos'] ?></span>
                <span class="points-suf">-Pts</span>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>

        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
(function(){
  const wrap=document.getElementById('rankingWrap');
  const sel=document.getElementById('catSelect');
  requestAnimationFrame(()=>wrap.classList.add('is-in'));
  sel.addEventListener('change',()=>{
    wrap.classList.remove('is-in');
    setTimeout(()=>document.getElementById('rankingForm').submit(),160);
  });

  window.APIBA_imgFallback = function(img){
    try {
      const list = JSON.parse(img.getAttribute('data-phlist') || '[]');
      const tried = img.getAttribute('data-tried') ? parseInt(img.getAttribute('data-tried'), 10) : 0;
      if (!list.length) return;
      if (tried >= list.length) return;
      img.setAttribute('data-tried', String(tried + 1));
      img.src = list[tried];
    } catch(e) {
      img.src = '<?= htmlspecialchars($ph0, ENT_QUOTES, 'UTF-8') ?>';
    }
  };
})();
</script>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
