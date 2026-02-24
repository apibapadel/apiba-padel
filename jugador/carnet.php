<?php
require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$jid = $_SESSION['jugador']['id'];

$stmt = $pdo->prepare("SELECT * FROM jugadores WHERE id=? LIMIT 1");
$stmt->execute([$jid]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$j) die("Jugador no encontrado");

$stmt = $pdo->prepare("SELECT * FROM carnets WHERE jugador_id=? LIMIT 1");
$stmt->execute([$jid]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header("Location: generar_carnet.php"); exit; }
?>

<h1>🎫 Carnet APiBA</h1>

<div style="border:1px solid #000; width:340px; padding:12px; border-radius:10px; background:#fafafa">
  <?php if (!empty($j['foto'])): ?>
    <img src="/apiba-padel/uploads/jugadores/<?= htmlspecialchars($j['foto']) ?>" width="100"><br><br>
  <?php endif; ?>

  <b><?= htmlspecialchars($j['apellido'].' '.$j['nombre']) ?></b><br>
  DNI: <?= htmlspecialchars($j['dni'] ?? '') ?><br>
  Categoría: <?= htmlspecialchars($j['categoria'] ?? '') ?><br>
  Sexo: <?= htmlspecialchars($j['sexo'] ?? '') ?><br>
  Carnet Nº: <?= htmlspecialchars($c['numero_carnet'] ?? '') ?><br><br>

  <?php if (!empty($c['qr_code'])): ?>
    <img src="/apiba-padel/uploads/qr/<?= htmlspecialchars($c['qr_code']) ?>" width="120">
  <?php endif; ?>
</div>

<br>
<a href="descargar_carnet.php">⬇ Descargar PDF</a><br>
<a href="descargar_carnet_jpg.php">⬇ Descargar JPG</a><br>
<a href="perfil.php">⬅ Volver al perfil</a>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
