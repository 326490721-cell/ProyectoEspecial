-- Crea la base del proyecto y selecciona codificación compatible con Unicode.
CREATE DATABASE IF NOT EXISTS friksix_arcade
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE friksix_arcade;

-- Información de empresas y personas que proveen materiales o servicios.
CREATE TABLE IF NOT EXISTS proveedores (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa VARCHAR(150) NOT NULL,
  persona_contacto VARCHAR(150) NULL,
  rfc VARCHAR(13) NULL,
  correo VARCHAR(254) NULL,
  telefono VARCHAR(30) NULL,
  direccion VARCHAR(255) NULL,
  sitio_web VARCHAR(2048) NULL,
  notas TEXT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_proveedores_rfc (rfc),
  KEY idx_proveedores_empresa (empresa),
  KEY idx_proveedores_activo (activo)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Perfiles que se asignan a las cuentas de usuario.
CREATE TABLE IF NOT EXISTS perfiles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(40) NOT NULL,
  nombre VARCHAR(80) NOT NULL,
  descripcion VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_perfiles_codigo (codigo)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Acciones específicas que la aplicación puede autorizar.
CREATE TABLE IF NOT EXISTS permisos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permisos_codigo (codigo)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Relaciona perfiles con permisos; la clave compuesta evita duplicar una relación.
CREATE TABLE IF NOT EXISTS perfil_permisos (
  perfil_id BIGINT UNSIGNED NOT NULL,
  permiso_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (perfil_id, permiso_id),
  CONSTRAINT fk_perfil_permisos_perfil
    FOREIGN KEY (perfil_id) REFERENCES perfiles (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_perfil_permisos_permiso
    FOREIGN KEY (permiso_id) REFERENCES permisos (id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Cuentas de acceso; se almacena el hash de la contraseña, nunca el texto original.
CREATE TABLE IF NOT EXISTS usuarios (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre_usuario VARCHAR(20) NOT NULL,
  correo VARCHAR(254) NULL,
  hash_contrasena VARCHAR(255) NOT NULL,
  perfil_id BIGINT UNSIGNED NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_nombre_usuario (nombre_usuario),
  UNIQUE KEY uq_usuarios_correo (correo),
  KEY idx_usuarios_perfil (perfil_id),
  KEY idx_usuarios_activo (activo),
  CONSTRAINT fk_usuarios_perfil
    FOREIGN KEY (perfil_id) REFERENCES perfiles (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Registra fallos de acceso por IP para limitar intentos repetidos.
CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip_address VARCHAR(45) NOT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_attempts_ip_time (ip_address, attempted_at),
  KEY idx_login_attempts_time (attempted_at)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Agrega los tres perfiles iniciales sin duplicarlos al volver a ejecutar el esquema.
INSERT IGNORE INTO perfiles (codigo, nombre, descripcion) VALUES
  ('developer', 'Desarrollador', 'Acceso a la página del desarrollador.'),
  ('accountant', 'Contador', 'Acceso a la página del contador.'),
  ('teacher', 'Maestro', 'Acceso general a las páginas de la aplicación.');

-- Registra los permisos que la aplicación comprobará en el servidor.
INSERT IGNORE INTO permisos (codigo, descripcion) VALUES
  ('page.developer.access', 'Acceder a la página del desarrollador.'),
  ('page.accounting.access', 'Acceder a la página del contador.'),
  ('page.general.access', 'Acceder a todas las páginas de la aplicación.'),
  ('suppliers.manage', 'Registrar proveedores.');

-- Asigna a cada perfil su permiso inicial.
INSERT IGNORE INTO perfil_permisos (perfil_id, permiso_id)
SELECT perfiles.id, permisos.id
FROM perfiles
JOIN permisos ON
  (perfiles.codigo = 'developer' AND permisos.codigo = 'page.developer.access')
  OR (perfiles.codigo = 'accountant' AND permisos.codigo = 'page.accounting.access')
  OR (perfiles.codigo = 'teacher' AND permisos.codigo = 'page.general.access')
  OR (perfiles.codigo = 'accountant' AND permisos.codigo = 'suppliers.manage');
