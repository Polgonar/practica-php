-- Esquema
CREATE TABLE IF NOT EXISTS attractions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  image_url VARCHAR(255) DEFAULT NULL,
  maintenance TINYINT(1) NOT NULL DEFAULT 0,
  duration_minutes INT DEFAULT NULL,
  min_height_cm INT DEFAULT NULL,
  category VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  label VARCHAR(100) NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_email VARCHAR(150) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  ticket_type_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(8,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Semilla mínima (ajústala a tu gusto)
INSERT INTO ticket_types (code,label,price,description) VALUES
('ADULT','Adulto',25.00,'Entrada general adulto'),
('CHILD','Niño (4-12)',15.00,'Entrada reducida para niños'),
('SENIOR','Senior',18.00,'Entrada para mayores de 65')
ON DUPLICATE KEY UPDATE price=VALUES(price), label=VALUES(label), description=VALUES(description);

INSERT INTO attractions (name, description, image_url, maintenance, duration_minutes, min_height_cm, category) VALUES
('Montaña Rusa Galactica', 'Montaña rusa con multiples Galaxias a explorar', 'http://localhost:8000/assets/images/montaña-rusa.png', 0, 120, 140, 'Montaña rusa'),
('Casa del Terror Alienigena', 'Recorrido oscuro con efectos en otro planeta desconocido', 'http://localhost:8000/assets/images/alien-casa-terror.jpg', 1, 15, NULL, 'Sustos'),
('Carrusel Estelar', 'Carrusel familiar donde podras explorar hasta la ultima estrella', 'http://localhost:8000/assets/images/carrusel-del-parque.png', 0, 5, NULL, 'Familiar'),
('Torre de Caída Meteorica', 'Caída libre a bordo de un meteorito', 'http://localhost:8000/assets/images/meteorito.png', 0, 2, 120, 'Adrenalina'),
('Simulador Espacial', 'Simulador 4D interactivo', 'http://localhost:8000/assets/images/simulador.png', 0, 20, NULL, 'Simulador'),
('Paseo por la Galaxia', 'Paseo en cohete acuatico para toda la familia por toda la Via Lactea', 'http://localhost:8000/assets/images/paseo.png', 0, 10, NULL, 'Acuático'),
('Apolo11', 'Despegue de la nave escapial mas famosa', 'http://localhost:8000/assets/images/Apolo11.png', 1, 90, 130, 'Montaña rusa'),
('Zona Infantil', 'Juegos y mini-atracciones de otra Galaxia', 'http://localhost:8000/assets/images/nens.png', 0, NULL, NULL, 'Infantil'),
('Pendulum', 'Péndulo gigante que impulsa hacia nuevos astros', 'http://localhost:8000/assets/images/pendulo.png', 0, 3, 125, 'Adrenalina'),
('La Noria', 'Noria panorámica para ver todo el Universo', 'http://localhost:8000/assets/images/noria.png', 0, 15, NULL, 'Familiar');
