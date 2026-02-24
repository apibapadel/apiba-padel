
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100),
  password VARCHAR(255),
  rol ENUM('admin','usuario'),
  activo TINYINT DEFAULT 1
);
