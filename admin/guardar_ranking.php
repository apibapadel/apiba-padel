<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_POST['id'] ?? 0);
$sexo = $_POST['sexo'] ?? '';
$categoria = trim($_POST['categoria'] ?? '');
$ranking = (int)($_POST['ranking'] ?? 0);

if ($id <= 0) die("ID inválido");
if ($sexo === '' || $categoria === '') die("Faltan datos");

// Validar categoría por sexo
$permitidasM = ['4TA Caballeros','5TA Caballeros','6TA Caballeros','7MA Caballeros'];
$permitidasF = ['4TA Damas','5TA Damas','6TA Damas','7MA Damas'];

if ($sexo === 'M' && !in_array($categoria, $permitidasM)) die("Categoría inválida para Masculino");
if ($sexo === 'F' && !in_array($categoria, $permitidasF)) die("Categoría inválida para Femenino");

$stmt = $pdo->prepare("UPDATE jugadores SET sexo=?, categoria=?, ranking=? WHERE id=?");
$stmt->execute([$sexo, $categoria, $ranking, $id]);

header("Location: ranking.php");
exit;
