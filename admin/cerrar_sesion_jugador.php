<?php
session_start();

// Si hay sesión de jugador, la eliminamos
if (isset($_SESSION['jugador'])) {
    unset($_SESSION['jugador']);
}

// Volver al panel admin con mensaje
header("Location: /apiba-padel/admin/index.php?msg=" . urlencode("Sesión de jugador cerrada correctamente."));
exit;
