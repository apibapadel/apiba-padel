<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

$stmt = $pdo->prepare("SELECT * FROM jugadores WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$j) die("Jugador no encontrado");

$stmt = $pdo->prepare("SELECT * FROM carnets WHERE jugador_id=? LIMIT 1");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);

include '_header.php';
?>

<h2>Ver carnet</h2>
<p class="muted"><b><?= htmlspecialchars($j['apellido'].' '.$j['nombre']) ?></b> — DNI <?= htmlspecialchars($j['dni']) ?></p>

<?php if (!$c): ?>
  <div class="card">
    <p>⚠️ Este jugador todavía no tiene carnet.</p>
    <a class="btn btn-sm" href="generar_carnet_jugador.php?id=<?= (int)$j['id'] ?>">Generar carnet</a>
    <a class="btn btn-soft btn-sm" href="jugadores.php">Volver</a>
  </div>
<?php else: ?>
  <div class="card" style="max-width:520px">
    <div style="display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap">
      <div>
        <?php if (!empty($j['foto'])): ?>
          <img src="/apiba-padel/uploads/jugadores/<?= htmlspecialchars($j['foto']) ?>" style="width:120px;border-radius:12px;border:1px solid #ddd">
        <?php else: ?>
          <div style="width:120px;height:120px;border-radius:12px;border:1px solid #ddd;background:#eee;display:flex;align-items:center;justify-content:center">
            Sin foto
          </div>
        <?php endif; ?>
      </div>

      <div>
        <div><b>Apellido:</b> <?= htmlspecialchars($j['apellido']) ?></div>
        <div><b>Nombre:</b> <?= htmlspecialchars($j['nombre']) ?></div>
        <div><b>Categoría:</b> <?= htmlspecialchars($j['categoria']) ?></div>
        <div><b>Sexo:</b> <?= htmlspecialchars($j['sexo']) ?></div>
        <div><b>Carnet Nº:</b> <?= htmlspecialchars($c['numero_carnet'] ?? '') ?></div>
      </div>

      <div>
        <?php if (!empty($c['qr_code'])): ?>
          <img src="/apiba-padel/uploads/qr/<?= htmlspecialchars($c['qr_code']) ?>" style="width:120px;border-radius:12px;border:1px solid #ddd">
        <?php else: ?>
          <div style="width:120px;height:120px;border-radius:12px;border:1px solid #ddd;background:#eee;display:flex;align-items:center;justify-content:center">
            Sin QR
          </div>
        <?php endif; ?>
      </div>
    </div>

    <hr>

    <a class="btn btn-sm" href="/apiba-padel/jugador/descargar_carnet.php?id=<?= (int)$j['id'] ?>">⬇ PDF</a>
    <a class="btn btn-sm" href="/apiba-padel/jugador/descargar_carnet_jpg.php?id=<?= (int)$j['id'] ?>">⬇ JPG</a>
    <a class="btn btn-soft btn-sm" href="jugadores.php">Volver</a>
  </div>
<?php endif; ?>

<?php include '_footer.php'; ?>
