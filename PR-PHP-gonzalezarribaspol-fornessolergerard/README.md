[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/abb7pIlM)
# Proyecto Taquilla — Gestión de pedidos

## 1. Conexión a la base de datos (Singleton)
La conexión se gestiona mediante una clase `Database` que implementa el patrón **Singleton**.  
De este modo, toda la aplicación reutiliza una única instancia de `PDO`:

```php
namespace App\DB;

use PDO;

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (!self::$instance) {
            self::$instance = new PDO(
                'mysql:host=db;dbname=taquilla;charset=utf8mb4',
                'root',
                'root',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return self::$instance;
    }
}
```
  
## 2. Recuperación del pedido pendiente
Cuando el usuario confirma o cancela, se busca el último pedido pendiente (status = 'PENDING') asociado a su email:

```php
$stmt = $pdo->prepare(
    'SELECT id FROM orders
     WHERE buyer_email = ? AND status = ?
     ORDER BY created_at DESC
     LIMIT 1'
);
$stmt->execute([$email, 'PENDING']);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
```
Si no existe un pedido pendiente, se muestra un mensaje flash y se redirige a buy.php.

## 3. Ejemplo de consulta con Prepared Statement

Para confirmar o cancelar el pedido se actualiza el estado del registro mediante un prepared statement, evitando inyecciones SQL:
```php
$stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
$stmt->execute(['COMPLETED', $orderId]);
```
## 4.Vídeo de demostración:
