<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) die("ID inválido");

$apellido   = trim($_POST['apellido'] ?? '');
$nombre     = trim($_POST['nombre'] ?? '');
$email      = strtolower(trim($_POST['email'] ?? ''));
$dni        = trim($_POST['dni'] ?? '');
$telefono   = trim($_POST['telefono'] ?? '');
$sexo       = $_POST['sexo'] ?? '';
$categoria  = trim($_POST['categoria'] ?? '');
$ranking    = (int)($_POST['ranking'] ?? 0);
$activo     = (int)($_POST['activo'] ?? 1);

// =========================
// LOCALIDAD (SELECT + OTRA)
// =========================
$localidad_select = trim($_POST['localidad_select'] ?? '');
$localidad_otra   = trim($_POST['localidad_otra'] ?? '');
$localidad        = '';

if ($localidad_select === '') {
    die("La localidad es obligatoria");
}
if ($localidad_select === 'Otra') {
    if ($localidad_otra === '') {
        die("Debés especificar la localidad");
    }
    $localidad = $localidad_otra;
} else {
    $localidad = $localidad_select;
}

// =========================
// VALIDACIONES
// =========================
if (
    $apellido === '' ||
    $nombre === '' ||
    $email === '' ||
    $dni === '' ||
    $sexo === '' ||
    $categoria === '' ||
    $localidad === ''
) {
    die("Faltan datos obligatorios");
}

// Validar categoría por sexo
$permitidasM = ['4TA Caballeros','5TA Caballeros','6TA Caballeros','7MA Caballeros'];
$permitidasF = ['4TA Damas','5TA Damas','6TA Damas','7MA Damas'];

if ($sexo === 'M' && !in_array($categoria, $permitidasM, true)) {
    die("Categoría inválida para Masculino");
}
if ($sexo === 'F' && !in_array($categoria, $permitidasF, true)) {
    die("Categoría inválida para Femenino");
}

// =========================
// DUPLICADOS
// =========================
$stmt = $pdo->prepare("
    SELECT id
    FROM jugadores
    WHERE (email = ? OR dni = ?) AND id <> ?
    LIMIT 1
");
$stmt->execute([$email, $dni, $id]);
if ($stmt->fetch()) {
    die("Ya existe otro jugador con ese Email o DNI");
}

// =========================
// UPDATE
// =========================
$stmt = $pdo->prepare("
  UPDATE jugadores
  SET
    apellido  = ?,
    nombre    = ?,
    email     = ?,
    dni       = ?,
    telefono  = ?,
    localidad = ?,
    sexo      = ?,
    categoria = ?,
    ranking   = ?,
    activo    = ?
  WHERE id = ?
");

$stmt->execute([
    $apellido,
    $nombre,
    $email,
    $dni,
    $telefono,
    $localidad,
    $sexo,
    $categoria,
    $ranking,
    $activo,
    $id
]);

header("Location: jugadores.php?msg=Jugador actualizado correctamente");
exit;
