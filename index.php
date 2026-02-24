<?php

$section = 'public';
$active = 'inicio';
$header_variant = 'home';
$page_title = 'Inicio - APIBA Pádel';

require_once __DIR__ . '/includes/site_header.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDB();

// ✅ Noticias destacadas
$noticias_destacadas = [];
try {
  $stmt = $pdo->query("
    SELECT id, titulo, contenido, imagen, destacada, fecha_publicacion
    FROM noticias
    WHERE destacada = 1 AND activa = 1
    ORDER BY fecha_publicacion DESC, id DESC
    LIMIT 3
  ");
  $noticias_destacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ✅ Últimas noticias
$ultimas_noticias = [];
try {
  $stmt = $pdo->query("
    SELECT id, titulo, contenido, imagen, destacada, fecha_publicacion
    FROM noticias
    WHERE activa = 1
    ORDER BY fecha_publicacion DESC, id DESC
    LIMIT 6
  ");
  $ultimas_noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ✅ Próximos torneos: filtra por Abierto/En Curso
$proximos_torneos = [];
try {
  $stmt = $pdo->query("
    SELECT *
    FROM torneos
    WHERE estado IN ('Abierto','En Curso')
    ORDER BY fecha_inicio ASC
    LIMIT 3
  ");
  $proximos_torneos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

function estado_badge_class($estado) {
  $e = mb_strtolower(trim((string)$estado));
  if ($e === 'abierto') return 'badge--ok';
  if ($e === 'en curso') return 'badge--warn';
  if ($e === 'finalizado') return 'badge';
  if ($e === 'cancelado') return 'badge--danger';
  return 'badge';
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function fmt_fecha($dt){
  if (!$dt) return '';
  $ts = strtotime((string)$dt);
  if (!$ts) return (string)$dt;
  return date('d/m/Y H:i', $ts);
}
function noticia_img_url($base, $img){
  $img = trim((string)$img);
  if ($img === '') return '';
  return $base . "/uploads/noticias/" . $img;
}
function resumen_auto($text, $max = 160){
  $t = trim(preg_replace('/\s+/', ' ', (string)$text));
  if (mb_strlen($t) <= $max) return $t;
  return mb_substr($t, 0, $max-1) . "…";
}

function sede_img(string $sede): string {
  $s = mb_strtolower(trim($sede));
  $s = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
  if (str_contains($s, 'casbas')) return '/apiba-padel/assets/img/sedes/casbas-padel.jpg';
  if (str_contains($s, 'fray')) return '/apiba-padel/assets/img/sedes/fray-padel.jpg';
  if (str_contains($s, 'quinta')) return '/apiba-padel/assets/img/sedes/la-quinta-padel.jpg';
  if (str_contains($s, '90')) return '/apiba-padel/assets/img/sedes/90s-padel.jpg';
  return '/apiba-padel/assets/img/hero.jpg';
}
?>
<div class="hero hero--simple" style="margin-bottom:14px;">
  <div class="hero__bg" style="background-image:url('/apiba-padel/assets/img/hero.jpg');"></div>
  <div class="hero__overlay"></div>

  <div class="hero__content">
    <div class="hero__left">
      <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
        <span class="badge">🏆 Torneos oficiales</span>
        <span class="badge">📍 Buenos Aires</span>
      </div>

      <h1 class="h1" style="font-size:clamp(30px,4vw,52px); line-height:1.03;">
        EL PÁDEL DEL <br>
        CIRCUITO APIBA  <br>
        <span style="background:linear-gradient(90deg,var(--primary),#9bff67);-webkit-background-clip:text;background-clip:text;color:transparent;">
          SE VIVE EN FAMILIA
        </span>
      </h1>

      <p class="p" style="max-width:560px; margin-top:10px;">
        Consultá torneos, ranking, fixtures y novedades. Sumate a la comunidad APiBA.
      </p>

      <div class="form-actions" style="margin-top:14px;">
        <a class="btn btn--primary" href="/apiba-padel/torneos/">Torneos disponibles</a>
        <a class="btn" href="/apiba-padel/ranking/">Ver ranking</a>
        <a class="btn" href="/apiba-padel/login.php">Ingresar</a>
      </div>

    </div>
  </div>
</div>

<!--
  FIX (2026-02): en algunos combos de CSS el bloque siguiente podía quedar
  visualmente “por detrás” del hero (imagen de cancha). Forzamos stacking context.
-->
<div class="after-hero" style="position:relative; z-index:2;">

<div class="card" style="margin:14px 0;">
  


<div class="grid grid--2">

  <!-- Card: Próximos torneos -->
  <div class="card" id="card-proximos-torneos">
    <div class="card__header">
      <h2 class="h2">🎾 Próximos torneos</h2>
      <span class="badge badge--warn">Abierto / En curso</span>
    </div>
    <div class="card__body">

      <?php if (empty($proximos_torneos)): ?>
        <p class="p">No hay torneos abiertos por ahora.</p>
      <?php else: ?>
        <div class="ml-list">
          <?php foreach($proximos_torneos as $t): ?>
            <div class="ml-item">
              <div class="ml-item__img" style="background-image:url('<?= h(sede_img($t['sede'] ?? '')) ?>');"></div>
              <div class="ml-item__body">
                <p class="ml-item__title"><?= h($t['nombre'] ?? '') ?></p>
                <div class="ml-item__meta">
                  <?php if (!empty($t['sede'])): ?><span class="badge">📍 <?= h($t['sede']) ?></span><?php endif; ?>
                  <?php if (!empty($t['fecha_inicio'])): ?><span class="badge">📅 <?= h($t['fecha_inicio'] ?? '') ?></span><?php endif; ?>
                  <span class="badge <?= estado_badge_class($t['estado'] ?? '') ?>"><?= h($t['estado'] ?? '') ?></span>
                </div>
                <div class="ml-item__cta">
                  <a class="btn btn--primary" href="/apiba-padel/torneos/ver.php?id=<?= (int)$t['id'] ?>">Ver torneo</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>

<div style="height:14px;"></div>

<!-- ✅ HOME PRO: Noticias en CARDS -->
<div class="card" style="margin-bottom:14px;">
  <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
    <h2 class="h2" style="margin:0;">📰 Noticias</h2>
    <a class="btn btn--primary" href="<?= $base ?>/public/noticias.php" style="text-decoration:none;">Ver todas</a>
  </div>
  <div class="card__body">
    <p class="p" style="margin:0;color:var(--muted);">
      Novedades del circuito, anuncios y actualizaciones.
    </p>
  </div>
</div>

<div class="grid grid--2">

  <!-- Destacadas (cards) -->
  <div class="card">
    <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
      <h2 class="h2" style="margin:0;">⭐ Destacadas</h2>
      <span class="badge badge--ok">Top</span>
    </div>
    <div class="card__body">

      <?php if (empty($noticias_destacadas)): ?>
        <p class="p">No hay noticias destacadas.</p>
      <?php else: ?>
        <div style="display:grid; gap:12px;">
          <?php foreach ($noticias_destacadas as $n): ?>
            <?php
              $imgUrl = noticia_img_url($base, $n['imagen'] ?? '');
              $texto = resumen_auto($n['contenido'] ?? '', 160);
            ?>
            <div class="card" style="margin:0;">
              <?php if ($imgUrl !== ''): ?>
                <div style="margin:-10px -10px 12px -10px; border-radius:14px; overflow:hidden;">
                  <img src="<?= h($imgUrl) ?>" alt="<?= h($n['titulo'] ?? '') ?>"
                       style="width:100%; height:170px; object-fit:cover; display:block;"
                       onerror="this.style.display='none';">
                </div>
              <?php endif; ?>

              <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
                <span class="badge"><?= h(fmt_fecha($n['fecha_publicacion'] ?? '')) ?></span>
                <span class="badge badge--ok">⭐ Destacada</span>
              </div>

              <div style="height:10px;"></div>
              <h3 class="h2" style="margin:0 0 8px 0;"><?= h($n['titulo'] ?? '') ?></h3>
              <p class="p" style="margin:0 0 12px 0; color:var(--muted);"><?= h($texto) ?></p>

              <a class="btn btn--primary" href="<?= $base ?>/public/noticia.php?id=<?= (int)$n['id'] ?>" style="text-decoration:none;">Leer</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Últimas (cards) -->
  <div class="card">
    <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
      <h2 class="h2" style="margin:0;">🗞️ Últimas</h2>
      <span class="badge">Recientes</span>
    </div>
    <div class="card__body">

      <?php if (empty($ultimas_noticias)): ?>
        <p class="p">No hay noticias cargadas.</p>
      <?php else: ?>
        <div style="display:grid; gap:12px;">
          <?php foreach ($ultimas_noticias as $n): ?>
            <?php
              $imgUrl = noticia_img_url($base, $n['imagen'] ?? '');
              $texto = resumen_auto($n['contenido'] ?? '', 135);
            ?>
            <div class="card" style="margin:0;">
              <?php if ($imgUrl !== ''): ?>
                <div style="margin:-10px -10px 12px -10px; border-radius:14px; overflow:hidden;">
                  <img src="<?= h($imgUrl) ?>" alt="<?= h($n['titulo'] ?? '') ?>"
                       style="width:100%; height:150px; object-fit:cover; display:block;"
                       onerror="this.style.display='none';">
                </div>
              <?php endif; ?>

              <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                <span class="badge"><?= h(fmt_fecha($n['fecha_publicacion'] ?? '')) ?></span>
                <?php if (!empty($n['destacada'])): ?>
                  <span class="badge badge--ok">⭐ Destacada</span>
                <?php endif; ?>
              </div>

              <div style="height:10px;"></div>
              <h3 class="h2" style="margin:0 0 8px 0;"><?= h($n['titulo'] ?? '') ?></h3>
              <p class="p" style="margin:0 0 12px 0; color:var(--muted);"><?= h($texto) ?></p>

              <a class="btn" href="<?= $base ?>/public/noticia.php?id=<?= (int)$n['id'] ?>" style="text-decoration:none;">Leer</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>



<!-- Card: Sedes (movido abajo para no romper el encuadre del HERO) -->
<div class="card" id="card-sedes">
<div class="card__header" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
    <h2 class="h2" style="margin:0;">🏟️ Sedes</h2>
    <button class="btn" type="button" id="sedesToggle">Pausar</button>
  </div>
  <div class="card__body" style="padding-top:10px;">
    <div class="sedes-scroll" id="sedes-scroll">
      <?php
        $sedes_imgs = [
          ['CASBAS PADEL','/apiba-padel/assets/img/sedes/casbas-padel.jpg'],
          ['FRAY PADEL','/apiba-padel/assets/img/sedes/fray-padel.jpg'],
          ['LA QUINTA PADEL','/apiba-padel/assets/img/sedes/la-quinta-padel.jpg'],
          ['90s PADEL','/apiba-padel/assets/img/sedes/90s-padel.jpg'],
        ];
        foreach($sedes_imgs as $si):
      ?>
        <a class="sede-tile" href="/apiba-padel/torneos/?sede=<?= urlencode($si[0]) ?>" title="<?= h($si[0]) ?>">
          <span class="sede-tile__img" style="background-image:url('<?= h($si[1]) ?>');"></span>
          <span class="sede-tile__cap"><?= h($si[0]) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
(function(){
  const sc = document.getElementById('sedes-scroll');
  const btn = document.getElementById('sedesToggle');
  if(!sc || !btn) return;

  let playing = true;
  let t = null;

  function tick(){
    if(!playing) return;
    sc.scrollLeft += 1;
    if (sc.scrollLeft + sc.clientWidth >= sc.scrollWidth - 2) sc.scrollLeft = 0;
    t = window.requestAnimationFrame(tick);
  }

  function play(){ playing = true; btn.textContent='Pausar'; tick(); }
  function pause(){ playing = false; btn.textContent='Reanudar'; if(t) cancelAnimationFrame(t); t=null; }

  btn.addEventListener('click', ()=> playing ? pause() : play());
  sc.addEventListener('mouseenter', pause);
  sc.addEventListener('mouseleave', play);

  // mobile: pause while touching
  sc.addEventListener('touchstart', pause, {passive:true});
  sc.addEventListener('touchend', play, {passive:true});

  play();
})();
</script>

</div><!-- /.after-hero -->

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>
