<?php
require "../config/db.php";
if ($_SESSION['rol'] != 'admin') die("No");

if ($_POST) {
    $jugador = $_POST['jugador_id'];
    $puntos  = $_POST['puntos'];

    $q = $conn->prepare("UPDATE jugadores SET ranking=? WHERE id=?");
    $q->bind_param("ii", $puntos, $jugador);
    $q->execute();
}
?>
<form method="POST">
    <input name="jugador_id" placeholder="ID jugador">
    <input name="puntos" placeholder="Ranking">
    <button>Guardar</button>
</form>
