<?php
require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

$stmt = $pdo->prepare("SELECT apellido, nombre, dni, sexo, categoria, foto FROM jugadores WHERE id=? AND activo=1 LIMIT 1");
$stmt->execute([$id]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$j) die("Jugador no encontrado o inactivo");

$stmt = $pdo->prepare("SELECT numero_carnet, qr_code FROM carnets WHERE jugador_id=? LIMIT 1");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h1>✅ Jugador verificado</h1>

<div style="border:1px solid #ddd;border-radius:12px;padding:14px;background:#fafafa;max-width:520px">
  <?php if(!empty($j['foto'])): ?>
    <img src="/apiba-padel/uploads/jugadores/<?= htmlspecialchars($j['foto']) ?>" style="width:120px;border-radius:12px;border:1px solid #ddd"><br><br>
  <?php endif; ?>

  <div><b>Apellido:</b> <?= htmlspecialchars($j['apellido']) ?></div>
  <div><b>Nombre:</b> <?= htmlspecialchars($j['nombre']) ?></div>
  <div><b>DNI:</b> <?= htmlspecialchars($j['dni']) ?></div>
  <div><b>Sexo:</b> <?= htmlspecialchars($j['sexo']) ?></div>
  <div><b>Categoría:</b> <?= htmlspecialchars($j['categoria']) ?></div>

  <?php if($c): ?>
    <div><b>Carnet Nº:</b> <?= htmlspecialchars($c['numero_carnet']) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
