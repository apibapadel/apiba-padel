<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$j = $pdo->query("SELECT apellido,nombre,categoria FROM jugadores WHERE id=".$_GET['id'])->fetch();
?>

<h2><?= $j['apellido'] ?> <?= $j['nombre'] ?></h2>
<p>Categoría: <?= $j['categoria'] ?></p>
