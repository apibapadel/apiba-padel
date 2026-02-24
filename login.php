<?php
if (!ob_get_level()) { ob_start(); }
$section = 'public';
$active = 'ingresar';
$page_title = 'Ingresar - APiBA Pádel';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/config/database.php';
$pdo = getDB();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = strtolower(trim($_POST['email'] ?? ''));
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM jugadores WHERE email=? AND activo=1 LIMIT 1");
  $stmt->execute([$email]);
  $j = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($j && password_verify($password, $j['password'])) {
    // ✅ seguridad: nueva sesión (antes de cualquier output)
    session_regenerate_id(true);

    $_SESSION['jugador'] = $j;

    header("Location: /apiba-padel/jugador/perfil.php");
    exit;
  } else {
    $error = "Email o clave incorrectos.";
  }
}

require_once __DIR__ . '/includes/site_header.php';
?>

<div class="auth-page">
  <div class="auth-wrap">

    <div class="auth-side">
      <div class="auth-side__inner">
        <span class="badge">🎾 APiBA Pádel</span>
        <h1 class="auth-title">Ingresar</h1>
        <p class="p auth-subtitle">Accedé para inscribirte a torneos y ver tu perfil.</p>
      </div>
    </div>

    <div class="card auth-card">
      <div class="card__body">

        <h1 class="h1" style="margin-bottom:10px;">Ingresar</h1>

        <?php if ($error): ?>
          <div style="margin-bottom:12px;">
            <span class="badge badge--danger">⚠ <?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>

        <form method="post" autocomplete="off" class="auth-form">
          <div class="field">
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" required placeholder="tu@email.com">
          </div>

          <div class="field">
            <label class="label" for="password">Clave</label>
            <input id="password" name="password" type="password" required placeholder="••••••••">
          </div>

          <button class="btn btn--primary auth-submit" type="submit">Ingresar</button>

          <div class="auth-links">
            <span class="muted">¿No tenés cuenta?</span>
            <a class="auth-link" href="/apiba-padel/registro.php">Registrate</a>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>