<?php
require "../config/db.php";

$email = $_POST['email'];
$pass  = $_POST['password'];

$q = $conn->prepare("SELECT * FROM usuarios WHERE email=?");
$q->bind_param("s", $email);
$q->execute();
$r = $q->get_result();

if ($r->num_rows == 0) die("Usuario no existe");

$u = $r->fetch_assoc();

if (!password_verify($pass, $u['password'])) die("Clave incorrecta");

$_SESSION['id']   = $u['id'];
$_SESSION['rol']  = $u['rol'];

if ($u['rol'] == 'admin') {
    header("Location: ../admin/panel.php");
} else {
    header("Location: ../jugador/panel.php");
}
