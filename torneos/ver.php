<?php
$section = 'public';
$active = 'torneos';
$page_title = 'Torneo - APIBA Pádel';

require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function sede_logo(string $sede): string {
  $s = mb_strtolower(trim($sede));
  $s = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
  if (str_contains($s, 'casbas')) return '/apiba-padel/assets/img/sedes/logos/casbas.jpg';
  if (str_contains($s, 'fray')) return '/apiba-padel/assets/img/sedes/logos/fray.jpg';
  if (str_contains($s, 'quinta')) return '/apiba-padel/assets/img/sedes/logos/la-quinta.jpg';
  if (str_contains($s, '90')) return '/apiba-padel/assets/img/sedes/logos/90s.jpg';
  return '';
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

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

// Torneo
$stmt = $pdo->prepare("SELECT * FROM torneos WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$t) die("Torneo no encontrado");

// Fixture (si existe tabla)
$fixture = [];
try {
  $stmt = $pdo->prepare("SELECT * FROM fixture WHERE torneo_id=? ORDER BY fecha, horario, id");
  $stmt->execute([$id]);
  $fixture = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $fixture = [];
}

function t_estado_badge($estado) {
  $e = mb_strtolower(trim((string)$estado));
  if ($e === 'abierto') return 'badge badge--ok';
  if ($e === 'en curso') return 'badge badge--warn';
  if ($e === 'finalizado') return 'badge';
  if ($e === 'cancelado') return 'badge badge--danger';
  return 'badge';
}

/* =========================
   CONTROL UX: mostrar/ocultar botón Inscribirme
   - Seguridad real ya está en inscribirse.php
   ========================= */
$puede_inscribirse = false;
$motivo_bloqueo = '';

$categoriaTorneo = trim((string)($t['categoria'] ?? ''));

if (!isset($_SESSION['jugador'])) {
  $puede_inscribirse = false;
  $motivo_bloqueo = 'Tenés que iniciar sesión para inscribirte.';
} else {
  $jugador_id = (int)$_SESSION['jugador']['id'];

  // Traer categoría real del jugador (no confiar 100% en session)
  $stmt = $pdo->prepare("SELECT categoria FROM jugadores WHERE id=? LIMIT 1");
  $stmt->execute([$jugador_id]);
  $jug = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$jug) {
    $puede_inscribirse = false;
    $motivo_bloqueo = 'Jugador no encontrado.';
  } else {
    $categoriaJugador = trim((string)$jug['categoria']);

    if ($categoriaJugador !== $categoriaTorneo) {
      $puede_inscribirse = false;
      $motivo_bloqueo = "NO CORRESPONDE A LA CATEGORÍA. Tu categoría: {$categoriaJugador}.";
    } else {
      $puede_inscribirse = true;
      $motivo_bloqueo = '';
    }
  }
}
?>

<div class="torneo-view">

  <!-- Breadcrumbs reales -->
  <div class="crumbs" style="margin:0 0 10px;">
    <a class="chip" href="/apiba-padel/">Inicio</a>
    <span class="chip">›</span>
    <a class="chip" href="/apiba-padel/torneos/">Torneos</a>
    <span class="chip">›</span>
    <span class="chip chip--cta">Torneo #<?= (int)$t['id'] ?></span>
  </div>

  <!-- Header / Hero del torneo (con imagen de sede) -->
  <div class="torneo-hero torneo-hero--withimg">
    <div class="torneo-hero__img" style="background-image:url('<?= h(sede_img($t['sede'] ?? '')) ?>');">
      <div class="crumb">
        <?php $logo = sede_logo($t['sede'] ?? ''); ?>
        <?php if ($logo !== ''): ?>
          <img class="sede-logo" src="<?= h($logo) ?>" alt="<?= h($t['sede'] ?? 'Sede') ?>">
        <?php else: ?>
          📍 <?= h($t['sede'] ?? 'Sin sede') ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="torneo-hero__content">

    <div class="torneo-hero__left">
      <h1 class="torneo-title"><?= htmlspecialchars($t['nombre'] ?? '') ?></h1>

      <div class="torneo-badges">
        <?php if (!empty($t['categoria'])): ?>
          <span class="badge">🎾 <?= htmlspecialchars($t['categoria']) ?></span>
        <?php endif; ?>

        <span class="badge">📍 <?= h($t['sede'] ?? 'Sin sede') ?></span>

        <?php if (!empty($t['fecha_inicio'])): ?>
          <span class="badge">📅 <?= htmlspecialchars($t['fecha_inicio']) ?></span>
        <?php endif; ?>

        <span class="<?= t_estado_badge($t['estado'] ?? '') ?>">
          <?= htmlspecialchars($t['estado'] ?? '') ?>
        </span>
      </div>

      <p class="p" style="margin-top:10px;">
        Mirá el fixture y los datos del torneo. Para participar, registrate e inscribite.
      </p>

      <div class="form-actions" style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
        <?php if ($puede_inscribirse): ?>
          <a class="btn btn--primary" href="/apiba-padel/torneos/inscribirse.php?id=<?= (int)$t['id'] ?>">
            ✅ Inscribirme
          </a>
        <?php else: ?>
          <?php if (!empty($motivo_bloqueo)): ?>
            <div class="badge badge--danger" style="padding:10px 12px; border-radius:12px;">
              ❌ <?= htmlspecialchars($motivo_bloqueo) ?>
            </div>
          <?php endif; ?>

          <?php if (!isset($_SESSION['jugador'])): ?>
            <a class="btn btn--primary" href="/apiba-padel/login.php">
              Iniciar sesión
            </a>
          <?php endif; ?>
        <?php endif; ?>

        <a class="btn" href="/apiba-padel/torneos/">← Volver a torneos</a>
      </div>
    </div>

    <div class="torneo-hero__right">
      <div class="torneo-mini">
        <div class="torneo-mini__label">Estado</div>
        <div class="torneo-mini__value"><?= htmlspecialchars($t['estado'] ?? '') ?></div>
        <div class="divider"></div>
        <div class="torneo-mini__label">Sede</div>
        <div class="torneo-mini__value"><?= htmlspecialchars($t['sede'] ?? 'Sin sede') ?></div>
      </div>
    </div>
  </div>

  </div> <!-- /torneo-hero__content -->

  <!-- Fixture -->
  <section class="section" style="padding:14px 0 0;">
    <div class="section-title">
      <h2>📅 Fixture</h2>
      <div class="right">Partidos programados</div>
    </div>

    <div class="card">
      <div class="card__body">
        <?php if (empty($fixture)): ?>
          <p class="p">No hay fixture cargado todavía.</p>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Horario</th>
                  <th>Cancha</th>
                  <th>Local</th>
                  <th>Visitante</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($fixture as $f): ?>
                  <tr>
                    <td><?= htmlspecialchars($f['fecha'] ?? '') ?></td>
                    <td><?= htmlspecialchars($f['horario'] ?? '') ?></td>
                    <td><?= htmlspecialchars($f['cancha'] ?? '') ?></td>
                    <td><b><?= htmlspecialchars($f['local'] ?? '') ?></b></td>
                    <td><?= htmlspecialchars($f['visitante'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

</div>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
