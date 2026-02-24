<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: /apiba-padel/admin/login.php");
    exit;
}
