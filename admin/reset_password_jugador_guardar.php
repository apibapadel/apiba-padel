<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';

if ($id <= 0) die("ID inválido");
if (trim($password) === '') die("Contraseña vacía");

$stmt = $pdo->prepare("UPDATE jugadores SET password=? WHERE id=?");
$stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);

header("Location: jugadores.php?msg=" . urlencode("Contraseña reseteada correctamente para el jugador ID #{$id}."));
exit;
