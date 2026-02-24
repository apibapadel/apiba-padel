<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "apiba_padel";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error DB");
}
session_start();
