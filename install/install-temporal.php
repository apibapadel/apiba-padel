<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();

    $email = 'admin@apiba.com';
    $password = password_hash('123456', PASSWORD_DEFAULT);
    $rol = 'admin';
    $activo = 1;

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (email, password, rol, activo)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$email, $password, $rol, $activo]);

    echo "✅ Instalación completa.<br><br>";
    echo "🔐 Admin creado:<br>";
    echo "Email: <b>admin@apiba.com</b><br>";
    echo "Clave: <b>123456</b><br><br>";
    echo "⚠️ Eliminá la carpeta <b>/install</b> por seguridad.";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
