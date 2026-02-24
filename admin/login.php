<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Completá usuario y contraseña.";
    } else {
        $pdo = getDB();

        $stmt = $pdo->prepare("
            SELECT * 
            FROM usuarios 
            WHERE email = ? 
              AND rol = 'admin' 
              AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            // Login OK
            $_SESSION['admin'] = [
                'id'    => $admin['id'],
                'email' => $admin['email'],
                'rol'   => $admin['rol']
            ];

            header("Location: /apiba-padel/admin/index.php");
            exit;
        } else {
            $error = "Credenciales incorrectas.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Administrador</title>
  <style>
    :root{
      --brand:#D9F7D5;
      --brand-strong:#2DB31F;
      --brand-strong-2:#239816;
      --bg:#EBEBEB;
      --surface:#FFFFFF;
      --surface-2:#F6F6F6;
      --ink:#111827;
      --muted:#6B7280;
      --line:#E5E7EB;
      --radius:12px;
      --shadow: 0 1px 2px rgba(0,0,0,.08);
      --shadow2: 0 10px 30px rgba(0,0,0,.10);
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: var(--bg);
      color: var(--ink);
    }
    .top{
      background: var(--brand);
      border-bottom: 1px solid rgba(0,0,0,.08);
      padding: 14px;
    }
    .top .inner{
      max-width: 980px;
      margin:0 auto;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
    }
    .brand{
      display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit;
    }
    .badge{
      height:34px; width:34px; border-radius:8px;
      background: var(--brand-strong);
      color:#fff; font-weight:900;
      display:flex; align-items:center; justify-content:center;
      box-shadow: var(--shadow);
    }
    .title{font-weight:900; line-height:1.05}
    .subtitle{display:block; font-size:12px; color: rgba(0,0,0,.55); font-weight:600}

    .wrap{
      max-width: 420px;
      margin: 26px auto 40px;
      padding: 0 14px;
    }
    .card{
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: var(--shadow2);
      padding: 16px;
    }
    h1{margin:0 0 10px; font-size: 20px}
    .muted{color: var(--muted); font-size: 13px}

    label{display:block; font-size:13px; color: rgba(0,0,0,.72); margin: 12px 0 6px}
    input{
      width:100%;
      background: var(--surface-2);
      border: 1px solid var(--line);
      border-radius: 10px;
      padding: 10px 10px;
      outline:none;
      font-size:14px;
    }
    input:focus{border-color: rgba(45,179,31,.55); box-shadow: 0 0 0 3px rgba(45,179,31,.15)}
    .btn{
      width:100%;
      margin-top: 14px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid rgba(0,0,0,.06);
      background: var(--brand-strong);
      color:#fff;
      font-weight:700;
      cursor:pointer;
      box-shadow: var(--shadow);
    }
    .btn:hover{background: var(--brand-strong-2)}
    .error{
      margin: 10px 0 0;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid rgba(220,38,38,.25);
      background: rgba(220,38,38,.08);
      color:#7F1D1D;
      font-size: 13px;
    }
    .foot{
      margin-top: 12px;
      display:flex;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
    }
    .link{font-size:13px; color: rgba(0,0,0,.70); text-decoration:none}
    .link:hover{text-decoration:underline}
  </style>
</head>
<body>

<header class="top">
  <div class="inner">
    <a class="brand" href="/apiba-padel/index.php">
      <span class="badge">A</span>
      <span class="title">APiBA Admin<span class="subtitle">ingreso al panel</span></span>
    </a>
    <a class="link" href="/apiba-padel/index.php">Ir al sitio</a>
  </div>
</header>

<div class="wrap">
  <div class="card">
    <h1>Ingresar</h1>
    <div class="muted">Acceso para administradores</div>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" style="margin-top:10px">
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>

      <label>Contraseña</label>
      <input type="password" name="password" required>

      <button class="btn" type="submit">Entrar</button>
    </form>

    <div class="foot">
      <a class="link" href="/apiba-padel/index.php">← Volver</a>
    </div>
  </div>
</div>

</body>
</html>
