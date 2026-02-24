<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

$stmt = $pdo->prepare("SELECT id, apellido, nombre, email FROM jugadores WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$j) die("Jugador no encontrado");

include '_header.php';
?>

<h2>Resetear contraseña</h2>
<p><b>Jugador:</b> <?= htmlspecialchars($j['apellido'].' '.$j['nombre']) ?> (<?= htmlspecialchars($j['email']) ?>)</p>

<form method="post" action="reset_password_jugador_guardar.php" autocomplete="off">
  <input type="hidden" name="id" value="<?= (int)$j['id'] ?>">

  <label>Nueva contraseña</label><br>
  <input name="password" type="text" value="apiba123" required><br><br>

  <button>Guardar nueva contraseña</button>
</form>

<p><a href="jugadores.php">⬅ Volver</a></p>

<?php include '_footer.php'; ?>
