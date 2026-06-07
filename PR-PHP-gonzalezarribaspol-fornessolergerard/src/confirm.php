<?php
//Afegir tota la lògica PHP per processar les accions de confirmar/cancelar
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';
use App\DB\Database;

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

//Verificar que l'usuari està loguejat
$email = $_SESSION['username'] ?? null;
if (!$email) {
    $_SESSION['flash'] = 'Debes iniciar sesión.';
    header('Location: login.php');
    exit;
}

//Processar el POST només si arriba d'un formulari
$orderNumber = null;
$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    $pdo = Database::getInstance();

        //Recuperar el último pedido PENDING de l'usuari
    $stmt = $pdo->prepare(
        'SELECT id FROM orders
         WHERE buyer_email = ? AND status = ?
         ORDER BY created_at DESC
         LIMIT 1'
    );
    $stmt->execute([$email, 'PENDING']);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $_SESSION['flash'] = 'No se encontró ningún pedido pendiente.';
        header('Location: buy.php');
        exit;
    }

    $orderId = (int)$order['id'];

        //Segons l'acció (confirm o cancel), actualitzar l'status del pedido
    if ($action === 'confirm') {
        // Canviar status a COMPLETED
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute(['COMPLETED', $orderId]);

        $orderNumber = $orderId;
        $_SESSION['flash'] = '¡Compra confirmada con éxito!';

    } elseif ($action === 'cancel') {
        // Canviar status a CANCELLED
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute(['CANCELLED', $orderId]);

        $_SESSION['flash'] = 'Pedido cancelado correctamente.';

    } else {
        $_SESSION['flash'] = 'Acción no reconocida.';
        header('Location: preview.php');
        exit;
    }

}

//Gestionar missatges flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Confirmación</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/confirm.css">
</head>
<body>

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite">
    <?php if ($flash): ?>
      <?= e($flash) ?>
    <?php endif; ?>
  </div>

  <header>
    <h1>Resultado de la operación</h1>
    <nav>
      <a href="index.php">Volver a Home</a>
      <a href="buy.php">Nueva compra</a>
    </nav>
  </header>

  <main>
    <!--Mostrar el número de pedido només quan estigui COMPLETED -->
    <?php if ($orderNumber !== null): ?>
      <p>Tu número de pedido es: <strong id="order-number"><?= e((string)$orderNumber) ?></strong></p>
    <?php else: ?>
      <p><strong id="order-number">—</strong></p>
    <?php endif; ?>
    <!-- Si se cancela, el mensaje flash ja ho indica -->
  </main>

</body>
</html>
