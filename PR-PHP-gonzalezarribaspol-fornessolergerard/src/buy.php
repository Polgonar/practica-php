<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';
use App\DB\Database;

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['username'] ?? null;
    if (!$email) {
        $_SESSION['flash'] = 'Debes iniciar sesión para comprar.';
        header('Location: login.php');
        exit;
    }
    $pdo = Database::getInstance();
    // Carreguem els tipus de ticket vigents per validar IDs i preus reals
    $stmt = $pdo->prepare('SELECT id, label, price FROM ticket_types ORDER BY id ASC');
    $stmt->execute();
    $allTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allowedIds = [];
    $priceById  = [];
    foreach ($allTypes as $row) {
        $allowedIds[(int)$row['id']] = true;
        $priceById[(int)$row['id']]  = (float)$row['price'];
    }

    $quantities = $_POST['quantity'] ?? null;
    if (!is_array($quantities)) {
        $_SESSION['flash'] = 'Formato de cantidades inválido.';
        header('Location: buy.php');
        exit;
    }

    $clean  = [];   // [ticket_type_id => qty netejada]
    $hasAny = false;

    foreach ($quantities as $idStr => $qtyRaw) {
        // ID ha de ser dígits
        if (!is_int($idStr) && !(is_string($idStr) && ctype_digit($idStr))) {        $_SESSION['flash'] = 'IDs de ticket inválidos.';
            header('Location: buy.php');
            exit;
        }
        $id = (int)$idStr;

        // ID ha d'existir a BBDD
        if (!isset($allowedIds[$id])) {
            $_SESSION['flash'] = 'Algún tipo de entrada no existe.';
            header('Location: buy.php');
            exit;
        }

        // Quantitat 0–100 i enter
        $qty = filter_var($qtyRaw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 100]
        ]);
        if ($qty === false) {
            $_SESSION['flash'] = 'Las cantidades deben ser enteros entre 0 y 100.';
            header('Location: buy.php');
            exit;
        }

        $clean[$id] = $qty;
        if ($qty > 0) $hasAny = true;
    }

// Almenys una línia amb quantitat > 0
    if (!$hasAny) {
        $_SESSION['flash'] = 'Selecciona al menos una cantidad mayor que 0.';
        header('Location: buy.php');
        exit;
    }

    // Calculem el total amb els preus reals de BBDD
    $total = 0.0;
    foreach ($clean as $ticketId => $qty) {
        if ($qty > 0) {
            $total += $priceById[$ticketId] * $qty;
        }
    }

        // **CANVI 2: Inserim l'order amb status PENDING**
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO orders (buyer_email, total, status) VALUES (?, ?, ?)'
        );
        $stmt->execute([$email, $total, 'PENDING']);
        $orderId = (int)$pdo->lastInsertId();

        // **CANVI 3: Inserim els order_items amb prepared statements**
        $stmtItem = $pdo->prepare(
            'INSERT INTO order_items (order_id, ticket_type_id, quantity, unit_price)
             VALUES (?, ?, ?, ?)'
        );
        foreach ($clean as $ticketId => $qty) {
            if ($qty > 0) {
                $stmtItem->execute([
                    $orderId,
                    $ticketId,
                    $qty,
                    $priceById[$ticketId]
                ]);
            }
        }

        $pdo->commit();

        // **CANVI 4: Redirigim a preview.php segons enunciat**
        header('Location: preview.php');
        exit;

    } catch (\Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = 'Error al crear el pedido: ' . $e->getMessage();
        header('Location: buy.php');
        exit;
    }

}

$pdo = Database::getInstance();
$stmt = $pdo->prepare('SELECT id, label, price FROM ticket_types ORDER BY id ASC');
$stmt->execute();
$ticketTypes = $stmt->fetchAll();

// AFEGEIX just abans de treure HTML (després del bloc POST si n'hi ha)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Compra</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/buy.css">
</head>
<body>

  <!-- Mensajes flash -->
  <?php if ($flash): ?>
    <div id="flash-message" aria-live="polite"><?= e($flash) ?></div>
  <?php endif; ?>


  <header>
    <h1>Compra de entradas</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="login.php">Cambiar de usuario</a>
    </nav>
  </header>

  <!-- Formulario de compra -->
  <form id="buy-form" action="buy.php" method="post" novalidate>
    <p>Selecciona cantidades (0–100). El precio se muestra junto al tipo:</p>

    <fieldset>
      <legend>Tipos de entrada</legend>

      <?php if (!$ticketTypes): ?>
        <p>No hay tipos de entrada disponibles.</p>
      <?php else: ?>
        <?php foreach ($ticketTypes as $t): ?>
          <?php
            // Per a cada tipus: construïm IDs i noms segons els requisits
            $id    = (int)$t['id'];
            $label = (string)$t['label'];
            $price = (string)$t['price'];
            $inputId   = "quantity-{$id}";     // ID requerit: #quantity-<id>
            $labelId   = "ticket-type-{$id}";  // ID requerit: #ticket-type-<id>
          ?>
          <div class="ticket-row">
            <label for="<?= e($inputId) ?>" id="<?= e($labelId) ?>">
              <?= e($label) ?> — <span class="ticket-price"><?= e($price) ?> €</span>
            </label>

            <!-- Input numèric 0–100 per seleccionar quantitat -->
            <input
              id="<?= e($inputId) ?>"
              name="quantity[<?= e((string)$id) ?>]"
              type="number"
              min="0"
              max="100"
              step="1"
              value="0"
              inputmode="numeric"
            />
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </fieldset>

    <button type="submit">Ir a vista previa</button>
  </form>

</body>
</html>
