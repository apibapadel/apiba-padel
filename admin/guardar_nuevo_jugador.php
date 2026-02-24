<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$apellido   = trim($_POST['apellido'] ?? '');
$nombre     = trim($_POST['nombre'] ?? '');
$email      = strtolower(trim($_POST['email'] ?? ''));
$password   = $_POST['password'] ?? '';
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
    $password === '' ||
    $dni === '' ||
    $sexo === '' ||
    $categoria === ''
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
$stmt = $pdo->prepare("SELECT id FROM jugadores WHERE email = ? OR dni = ? LIMIT 1");
$stmt->execute([$email, $dni]);
if ($stmt->fetch()) {
    die("Ya existe un jugador con ese Email o DNI");
}

// =========================
// FOTO OPCIONAL
// =========================
$fotoNombre = null;

if (!empty($_FILES['foto']['name'])) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $permitidas, true)) {
        die("Foto: formato no permitido (jpg, jpeg, png, webp).");
    }
    if (($_FILES['foto']['size'] ?? 0) > 2 * 1024 * 1024) {
        die("Foto: máximo 2MB.");
    }

    $fotoNombre = uniqid('jug_') . "." . $ext;
    $destinoDir = $_SERVER['DOCUMENT_ROOT'] . "/apiba-padel/uploads/jugadores";
    $destino    = $destinoDir . "/" . $fotoNombre;

    if (!is_dir($destinoDir)) {
        mkdir($destinoDir, 0777, true);
    }

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
        die("No se pudo guardar la foto.");
    }
}

// =========================
// INSERT
// =========================
$stmt = $pdo->prepare("
  INSERT INTO jugadores
  (apellido,nombre,email,password,dni,telefono,localidad,sexo,categoria,ranking,activo,foto)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->execute([
  $apellido,
  $nombre,
  $email,
  password_hash($password, PASSWORD_DEFAULT),
  $dni,
  $telefono,
  $localidad,
  $sexo,
  $categoria,
  $ranking,
  $activo,
  $fotoNombre
]);

header("Location: jugadores.php?msg=Jugador creado correctamente");
exit;
