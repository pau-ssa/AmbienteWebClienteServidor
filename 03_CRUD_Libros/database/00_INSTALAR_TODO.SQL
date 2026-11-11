-- Instalación autónoma de P03.

-- Biblioteca Virtual Fidélitas | Prototipo 03
CREATE DATABASE IF NOT EXISTS `biblioteca_p03` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `biblioteca_p03`;


-- Biblioteca Virtual Fidélitas | Prototipo 03
USE `biblioteca_p03`;
CREATE TABLE IF NOT EXISTS categorias (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL UNIQUE,
 descripcion VARCHAR(300) NULL
) ENGINE=InnoDB;

USE `biblioteca_p03`;
CREATE TABLE IF NOT EXISTS libros (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 titulo VARCHAR(180) NOT NULL,
 autor VARCHAR(160) NOT NULL,
 resumen TEXT NOT NULL,
 categoria_id INT UNSIGNED NOT NULL,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_libro_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
 INDEX idx_libros_titulo (titulo),
 INDEX idx_libros_autor (autor)
) ENGINE=InnoDB;


-- Biblioteca Virtual Fidélitas | Prototipo 03
USE `biblioteca_p03`;
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_categoria_listar`$$
CREATE PROCEDURE `sp_categoria_listar`()
BEGIN
    SELECT id, nombre, descripcion FROM categorias ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS `sp_libro_listar`$$
CREATE PROCEDURE `sp_libro_listar`()
BEGIN
    SELECT l.id, l.titulo, l.autor, l.resumen, l.categoria_id, c.nombre AS categoria
    FROM libros l JOIN categorias c ON c.id=l.categoria_id ORDER BY l.titulo, l.id;
END$$

DROP PROCEDURE IF EXISTS `sp_libro_obtener`$$
CREATE PROCEDURE `sp_libro_obtener`(IN p_id INT)
BEGIN
    SELECT l.id, l.titulo, l.autor, l.resumen, l.categoria_id, c.nombre AS categoria
    FROM libros l JOIN categorias c ON c.id=l.categoria_id WHERE l.id=p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS `sp_libro_crear`$$
CREATE PROCEDURE `sp_libro_crear`(IN p_titulo VARCHAR(180), IN p_autor VARCHAR(160), IN p_resumen
    TEXT, IN p_categoria_id INT)
BEGIN
    INSERT INTO libros(titulo,autor,resumen,categoria_id)
        VALUES(p_titulo,p_autor,p_resumen,p_categoria_id);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS `sp_libro_actualizar`$$
CREATE PROCEDURE `sp_libro_actualizar`(IN p_id INT, IN p_titulo VARCHAR(180), IN p_autor
    VARCHAR(160), IN p_resumen TEXT, IN p_categoria_id INT)
BEGIN
    UPDATE libros SET titulo=p_titulo, autor=p_autor, resumen=p_resumen,
        categoria_id=p_categoria_id WHERE id=p_id;
    SELECT ROW_COUNT() AS afectados;
END$$

DROP PROCEDURE IF EXISTS `sp_libro_eliminar`$$
CREATE PROCEDURE `sp_libro_eliminar`(IN p_id INT)
BEGIN
    DELETE FROM libros WHERE id=p_id;
    SELECT ROW_COUNT() AS afectados;
END$$
DELIMITER ;


-- Biblioteca Virtual Fidélitas | Prototipo 03
USE `biblioteca_p03`;
INSERT INTO categorias (id,nombre,descripcion) VALUES
 (1,'Programación','Lenguajes y técnicas de programación'),
 (2,'Bases de datos','Diseño, integridad y consultas'),
 (3,'Diseño web','Interfaz y experiencia del usuario')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), descripcion=VALUES(descripcion);
INSERT INTO libros (id,titulo,autor,resumen,categoria_id) VALUES
 (1,'Fundamentos de PHP','Equipo académico',
     'Conceptos esenciales de PHP, funciones y formularios para el desarrollo web.',1),
 (2,'Introducción a las bases de datos','Equipo académico',
     'Tablas, claves primarias, relaciones e integridad de datos.',2),
 (3,'Diseño de interfaces web','Equipo académico','Buenas prácticas de HTML semántico, CSS y accesibilidad.',3)
ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), autor=VALUES(autor), resumen=VALUES(resumen),
    categoria_id=VALUES(categoria_id);

