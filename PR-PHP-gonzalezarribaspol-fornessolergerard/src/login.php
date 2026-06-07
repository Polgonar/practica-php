<?php
declare(strict_types=1);
session_start();


// Helper per escapar text en HTML
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Recuperem i netegem el flash anterior
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$error = null;        // Missatge d'error per a validacions
$email = '';          // Per repintar el valor al camp si hi ha error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Llegim l'email enviat al formulari
    $email = trim($_POST['email'] ?? '');

    //Validem format d'email al servidor
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Introduce un email válido (p. ej., nombre@dominio.com).';
    } else {
        //Guardem l'email a la sessió amb la clau 'username'
        $_SESSION['username'] = $email;

        //Redirigim a buy.php i aturem l'execució
        header('Location: buy.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

  <!-- Mensajes flash -->
  <?php if ($flash || $error): ?>
  <div id="flash-message" aria-live="polite">
    <?php if ($flash): ?>
      <?= e($flash) ?>
    <?php endif; ?>
    <?php if ($error): ?>
      <div><?= e($error) ?></div>
    <?php endif; ?>
  </div>
<?php endif; ?>


  <header>
    <h1>Identificación</h1>
    <nav>
      <a href="index.php">Volver a Home</a>
    </nav>
  </header>

  <!-- Formulario de login con email -->
  <form id="login-form" action="login.php" method="post" novalidate>
    <div>
      <label for="email-input">Email:</label>
      <input
        id="email-input"
        name="email"
        type="email"
        required
        placeholder="nombre@dominio.com"
        value="<?= e($email) ?>" />
    </div>
    <button type="submit">Continuar a compra</button>
  </form>

</body>
</html>
