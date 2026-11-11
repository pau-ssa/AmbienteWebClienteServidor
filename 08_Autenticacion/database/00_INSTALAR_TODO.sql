-- Instalador autónomo: ejecutar este archivo completo una sola vez.

-- Biblioteca Virtual Fidélitas | Prototipo 08
CREATE DATABASE IF NOT EXISTS `biblioteca_p08` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `biblioteca_p08`;


-- Biblioteca Virtual Fidélitas | Prototipo 08
USE `biblioteca_p08`;
CREATE TABLE IF NOT EXISTS categorias (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL UNIQUE,
 descripcion VARCHAR(300) NULL
) ENGINE=InnoDB;
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
CREATE TABLE IF NOT EXISTS documentos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 libro_id INT UNSIGNED NOT NULL UNIQUE,
 nombre_original VARCHAR(240) NOT NULL,
 nombre_interno VARCHAR(120) NOT NULL UNIQUE,
 mime VARCHAR(60) NOT NULL,
 bytes_archivo BIGINT UNSIGNED NOT NULL,
 cargado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_documento_libro FOREIGN KEY (libro_id) REFERENCES libros(id)
   ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS usuarios (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 clave_hash VARCHAR(255) NOT NULL,
 rol ENUM('admin','lector') NOT NULL DEFAULT 'lector',
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- Cola de limpieza: se escribe en la MISMA transacción que modifica o elimina el libro.
CREATE TABLE IF NOT EXISTS archivos_pendientes (
 nombre_interno VARCHAR(120) NOT NULL PRIMARY KEY,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Biblioteca Virtual Fidélitas | Prototipo 08
USE `biblioteca_p08`;
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_categoria_listar`$$
CREATE PROCEDURE `sp_categoria_listar`()
BEGIN
    SELECT id, nombre, descripcion FROM categorias ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS `sp_libro_listar`$$
CREATE PROCEDURE `sp_libro_listar`()
BEGIN
    SELECT l.id, l.titulo, l.autor, l.resumen, l.categoria_id, c.nombre AS categoria,
        d.nombre_original AS pdf_original, d.nombre_interno AS pdf_interno
    FROM libros l JOIN categorias c ON c.id=l.categoria_id LEFT JOIN documentos d ON
        d.libro_id=l.id ORDER BY l.titulo, l.id;
END$$

DROP PROCEDURE IF EXISTS `sp_libro_obtener`$$
CREATE PROCEDURE `sp_libro_obtener`(IN p_id INT)
BEGIN
    SELECT l.id, l.titulo, l.autor, l.resumen, l.categoria_id, c.nombre AS categoria,
        d.nombre_original AS pdf_original, d.nombre_interno AS pdf_interno
    FROM libros l JOIN categorias c ON c.id=l.categoria_id LEFT JOIN documentos d ON
        d.libro_id=l.id WHERE l.id=p_id LIMIT 1;
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

DROP PROCEDURE IF EXISTS `sp_libro_buscar`$$
CREATE PROCEDURE `sp_libro_buscar`(IN p_texto VARCHAR(200), IN p_categoria_id INT)
BEGIN
    SELECT l.id, l.titulo, l.autor, l.resumen, l.categoria_id, c.nombre AS categoria,
        d.nombre_original AS pdf_original, d.nombre_interno AS pdf_interno
    FROM libros l JOIN categorias c ON c.id=l.categoria_id LEFT JOIN documentos d ON d.libro_id=l.id
    WHERE (p_texto='' OR l.titulo LIKE CONCAT('%',p_texto,'%') OR l.autor LIKE
        CONCAT('%',p_texto,'%'))
      AND (p_categoria_id=0 OR l.categoria_id=p_categoria_id)
    ORDER BY l.titulo, l.id;
END$$

DROP PROCEDURE IF EXISTS `sp_categoria_obtener`$$
CREATE PROCEDURE `sp_categoria_obtener`(IN p_id INT)
BEGIN
    SELECT id,nombre,descripcion FROM categorias WHERE id=p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS `sp_categoria_crear`$$
CREATE PROCEDURE `sp_categoria_crear`(IN p_nombre VARCHAR(100), IN p_descripcion VARCHAR(300))
BEGIN
    INSERT INTO categorias(nombre,descripcion) VALUES(p_nombre,p_descripcion);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS `sp_categoria_actualizar`$$
CREATE PROCEDURE `sp_categoria_actualizar`(IN p_id INT, IN p_nombre VARCHAR(100), IN p_descripcion
    VARCHAR(300))
BEGIN
    UPDATE categorias SET nombre=p_nombre, descripcion=p_descripcion WHERE id=p_id;
    SELECT ROW_COUNT() AS afectados;
END$$

DROP PROCEDURE IF EXISTS `sp_categoria_eliminar`$$
CREATE PROCEDURE `sp_categoria_eliminar`(IN p_id INT)
BEGIN
    DELETE FROM categorias WHERE id=p_id;
    SELECT ROW_COUNT() AS afectados;
END$$

DROP PROCEDURE IF EXISTS `sp_documento_obtener`$$
CREATE PROCEDURE `sp_documento_obtener`(IN p_libro_id INT)
BEGIN
    SELECT id,libro_id,nombre_original,nombre_interno,mime,bytes_archivo FROM documentos WHERE
        libro_id=p_libro_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS `sp_documento_asignar`$$
CREATE PROCEDURE `sp_documento_asignar`(IN p_libro_id INT, IN p_original VARCHAR(240), IN
    p_interno VARCHAR(120), IN p_mime VARCHAR(60), IN p_bytes BIGINT)
BEGIN
    INSERT INTO documentos(libro_id,nombre_original,nombre_interno,mime,bytes_archivo)
    VALUES(p_libro_id,p_original,p_interno,p_mime,p_bytes)
    ON DUPLICATE KEY UPDATE nombre_original=p_original, nombre_interno=p_interno,
        mime=p_mime, bytes_archivo=p_bytes, cargado_en=CURRENT_TIMESTAMP;
    SELECT 1 AS ok;
END$$

DROP PROCEDURE IF EXISTS `sp_documento_nombres`$$
CREATE PROCEDURE `sp_documento_nombres`()
BEGIN
    SELECT libro_id,nombre_interno FROM documentos ORDER BY libro_id;
END$$

DROP PROCEDURE IF EXISTS `sp_usuario_buscar_por_correo`$$
CREATE PROCEDURE `sp_usuario_buscar_por_correo`(IN p_email VARCHAR(190))
BEGIN
    SELECT id,nombre,email,clave_hash,rol FROM usuarios WHERE email=p_email LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS `sp_usuario_crear`$$
CREATE PROCEDURE `sp_usuario_crear`(IN p_nombre VARCHAR(120), IN p_email VARCHAR(190), IN p_hash
    VARCHAR(255), IN p_rol VARCHAR(10))
BEGIN
    IF p_rol NOT IN ('admin','lector') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Rol no permitido';
    END IF;
    INSERT INTO usuarios(nombre,email,clave_hash,rol) VALUES(p_nombre,p_email,p_hash,p_rol);
    SELECT LAST_INSERT_ID() AS id;
END$$

-- Una operación de borrado SQL no garantiza el borrado físico en el servidor.
-- Estos procedimientos hacen persistir el trabajo pendiente después del COMMIT.
DROP PROCEDURE IF EXISTS `sp_archivo_pendiente_agregar`$$
CREATE PROCEDURE `sp_archivo_pendiente_agregar`(IN p_nombre VARCHAR(120))
BEGIN
    INSERT IGNORE INTO archivos_pendientes(nombre_interno) VALUES (p_nombre);
    SELECT 1 AS registrado;
END$$

DROP PROCEDURE IF EXISTS `sp_archivo_pendiente_listar`$$
CREATE PROCEDURE `sp_archivo_pendiente_listar`()
BEGIN
    SELECT nombre_interno, creado_en FROM archivos_pendientes ORDER BY creado_en, nombre_interno;
END$$

DROP PROCEDURE IF EXISTS `sp_archivo_pendiente_quitar`$$
CREATE PROCEDURE `sp_archivo_pendiente_quitar`(IN p_nombre VARCHAR(120))
BEGIN
    DELETE FROM archivos_pendientes WHERE nombre_interno = p_nombre;
    SELECT ROW_COUNT() AS afectados;
END$$
DELIMITER ;


-- Biblioteca Virtual Fidélitas | Prototipo 08
USE `biblioteca_p08`;
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
INSERT INTO documentos(libro_id,nombre_original,nombre_interno,mime,bytes_archivo) VALUES
 (1,'Fundamentos_PHP.pdf','demo_php.pdf','application/pdf',3956),
 (2,'Introduccion_BD.pdf','demo_bd.pdf','application/pdf',3985),
 (3,'Diseno_Web.pdf','demo_diseno.pdf','application/pdf',3952)
ON DUPLICATE KEY UPDATE nombre_original=VALUES(nombre_original),
    nombre_interno=VALUES(nombre_interno),mime=VALUES(mime),bytes_archivo=VALUES(bytes_archivo);
INSERT INTO usuarios(id,nombre,email,clave_hash,rol) VALUES
 (1,'Administrador de laboratorio','admin@biblioteca.local',
     '$2y$12$2m5D3hEnV2ojb0qhY6LJ6Olrgtdcq5xCryILZBNlDm08m/l3mz4vG','admin')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), email=VALUES(email),
    clave_hash=VALUES(clave_hash),rol=VALUES(rol);

