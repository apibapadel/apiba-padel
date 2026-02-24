<?php
// NO session_start() acá
if (!isset($_SESSION['jugador'])) {
    header("Location: /apiba-padel/login.php");
    exit;
}
