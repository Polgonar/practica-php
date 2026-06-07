<?php
//Afegir lògica PHP al principi per recuperar el pedido PENDING
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

$pdo = Database::getInstance();

//Recuperar el último pedido PENDING del email en sesión
$stmt = $pdo->prepare(
    'SELECT id, total FROM orders
     WHERE buyer_email = ? AND status = ?
     ORDER BY created_at DESC
     LIMIT 1'
);
$stmt->execute([$email, 'PENDING']);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no hi ha pedido PENDING, redirigim a buy.php
if (!$order) {
    $_SESSION['flash'] = 'No tienes ningún pedido pendiente.';
    header('Location: buy.php');
    exit;
}

$orderId = (int)$order['id'];
$total = (float)$order['total'];


//Recuperar els order_items amb info del ticket_type
$stmt = $pdo->prepare(
    'SELECT oi.quantity, oi.unit_price, tt.label
     FROM order_items oi
     JOIN ticket_types tt ON oi.ticket_type_id = tt.id
     WHERE oi.order_id = ?'
);
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Gestionar missatges flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Vista previa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/preview.css">
</head>
<body>

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite"></div>

  <header>
    <h1>Vista previa del pedido</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="buy.php">Editar compra</a>
    </nav>
  </header>

  <!-- Contenido del carrito/pedido -->
  <section aria-labelledby="cart-title">
    <h2 id="cart-title">Resumen</h2>
    <div id="cart-preview">
      <!-- Rellenar con líneas de pedido desde BBDD (pedido PENDING) -->
      <!-- Ejemplo:
      <div class="cart-item">
        <span>Adulto x 2</span>
        <span>50.00 €</span>
      </div>
      <div class="cart-total"><strong>Total: 68.00 €</strong></div>
      -->

      <!--Mostrar les línies del pedido des de BBDD-->
      <?php if (!$items): ?>
        <p>No hay items en el pedido.</p>
      <?php else: ?>
        <?php foreach ($items as $item): ?>
          <div class="cart-item">
            <span><?= e($item['label']) ?> x <?= e((string)$item['quantity']) ?></span>
            <span><?= e(number_format($item['unit_price'] * $item['quantity'], 2)) ?> €</span>
          </div>
        <?php endforeach; ?>

        <!--Mostrar el total del pedido-->
        <div class="cart-total">
          <strong>Total: <?= e(number_format($total, 2)) ?> €</strong>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- Acciones: confirmar o cancelar -->
  <form action="confirm.php" method="post" style="display:inline">
    <button id="finalize-button" type="submit" name="action" value="confirm">Confirmar compra</button>
  </form>

  <form action="confirm.php" method="post" style="display:inline">
    <button id="cancel-button" type="submit" name="action" value="cancel">Cancelar pedido</button>
  </form>

</body>
</html>
