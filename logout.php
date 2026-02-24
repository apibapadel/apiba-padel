<?php
session_start();
session_destroy();

header("Location: /apiba-padel/sesion_cerrada.php");
exit;
