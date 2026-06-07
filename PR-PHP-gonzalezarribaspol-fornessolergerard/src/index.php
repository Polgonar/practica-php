<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';
use App\DB\Database;

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Flash (opcional)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Filtre GET
$allowed = ['all','maintenance','available'];
$filter  = $_GET['filter'] ?? 'all';
if (!in_array($filter, $allowed, true)) { $filter = 'all'; }

// BBDD
$pdo = Database::getInstance();

// SQL segons filtre
$sqlBase = 'SELECT id, name, description, image_url, maintenance, duration_minutes, min_height_cm, category FROM attractions';
$params = [];

if ($filter === 'maintenance') {
  $sql = $sqlBase . ' WHERE maintenance = ?';
  $params[] = 1;
} elseif ($filter === 'available') {
  $sql = $sqlBase . ' WHERE maintenance = ?';
  $params[] = 0;
} else {
  $sql = $sqlBase;
}
$sql .= ' ORDER BY name ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$count = count($rows);
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/index.css"/>
</head>
<body>

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite">
    <?php if ($flash) echo e($flash); ?>
  </div>

  <header>
    <h1>Parque Temático</h1>
    <!-- Enlace a login -->
    <nav>
      <a href="login.php">Iniciar compra</a>
    </nav>
  </header>

  <!-- Imagen temática (el alumno la coloca directamente en el HTML) -->
  <figure>
    <img id="theme-image" src="../assets/images/spaceImage.jpg" alt="Imagen temática del parque" />
  </figure>

  <!-- Filtro tipo desplegable (select) -->
  <section aria-labelledby="filtro-title">
    <h2 id="filtro-title">Filtrar atracciones</h2>
    <label for="filter-maintenance">Estado:</label>
    <select id="filter-maintenance" name="filter" onchange="location.href='?filter='+this.value;">
        <option value="all" <?= $filter==='all' ? 'selected' : '' ?>>Todas</option>
        <option value="maintenance" <?= $filter==='maintenance' ? 'selected' : '' ?>>En mantenimiento</option>
        <option value="available" <?= $filter==='available' ? 'selected' : '' ?>>Disponibles</option>
    </select>
    <span>Mostrando: <strong id="attraction-count"><?= (int)$count ?></strong></span>
  </section>

  <!-- Lista de atracciones -->
  <section aria-labelledby="lista-title">
    <h2 id="lista-title">Atracciones</h2>
    <div id="attraction-list">
      <!-- Rellenar con tarjetas/filas de atracciones desde la BBDD -->
      <!-- Ejemplo:
      <article class="attraction">
        <h3>Montaña Rusa Titan</h3>
        <p>Descripción...</p>
        <span class="badge">Disponible</span>
      </article>
      -->
      <?php if ($count === 0): ?>
        <p class="muted">No hay atracciones para el filtro seleccionado.</p>
      <?php else: ?>
        <?php foreach ($rows as $a): ?>
          <?php
            $name   = (string)($a['name'] ?? '');
            $desc   = (string)($a['description'] ?? '');
            $img    = (string)($a['image_url'] ?? '');
            $maint  = (int)   ($a['maintenance'] ?? 0);
            $dur    = isset($a['duration_minutes']) ? (int)$a['duration_minutes'] : null;
            $height = isset($a['min_height_cm'])    ? (int)$a['min_height_cm']    : null;
            $cat    = (string)($a['category'] ?? '');
          ?>
          <article class="attraction" aria-label="<?= e($name) ?>">
            <?php if ($img !== ''): ?>
              <img class="thumb" src="<?= e($img) ?>" alt="Imagen de <?= e($name) ?>">
            <?php else: ?>
              <div class="thumb" role="img" aria-label="Sin imagen"></div>
            <?php endif; ?>
            <div>
              <h3><?= e($name) ?></h3>
              <?php if ($desc !== ''): ?><p><?= e($desc) ?></p><?php endif; ?>
              <p class="muted">
                <?php if ($dur !== null): ?>⏱ <?= $dur ?> min<?php endif; ?>
                <?php if ($height !== null): ?>&nbsp;•&nbsp;👶 Altura mínima: <?= $height ?> cm<?php endif; ?>
                <?php if ($cat !== ''): ?>&nbsp;•&nbsp;🏷 <?= e($cat) ?><?php endif; ?>
              </p>
              <?php if ($maint === 1): ?>
                <span class="badge warn">En mantenimiento</span>
              <?php else: ?>
                <span class="badge ok">Disponible</span>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

</body>
</html>
