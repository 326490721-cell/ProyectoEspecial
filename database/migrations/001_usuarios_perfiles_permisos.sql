-- Selecciona la base existente para agregarle cuentas y control de acceso.
USE friksix_arcade;

-- Perfiles disponibles para las cuentas del sistema.
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

-- Acciones que pueden asignarse a los perfiles.
CREATE TABLE IF NOT EXISTS permisos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permisos_codigo (codigo)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Tabla de unión entre perfiles y permisos; permite varios permisos por perfil.
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

-- Guarda las cuentas y su perfil; hash_contrasena nunca contiene la contraseña original.
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

-- Crea los perfiles iniciales sin insertar duplicados si se vuelve a ejecutar.
INSERT IGNORE INTO perfiles (codigo, nombre, descripcion) VALUES
  ('developer', 'Desarrollador', 'Acceso a la página del desarrollador.'),
  ('accountant', 'Contador', 'Acceso a la página del contador.'),
  ('teacher', 'Maestro', 'Acceso general a las páginas de la aplicación.');

-- Define los permisos que la aplicación debe verificar al atender una solicitud.
INSERT IGNORE INTO permisos (codigo, descripcion) VALUES
  ('page.developer.access', 'Acceder a la página del desarrollador.'),
  ('page.accounting.access', 'Acceder a la página del contador.'),
  ('page.general.access', 'Acceder a todas las páginas de la aplicación.');

-- Vincula cada perfil con su permiso inicial.
INSERT IGNORE INTO perfil_permisos (perfil_id, permiso_id)
SELECT perfiles.id, permisos.id
FROM perfiles
JOIN permisos ON
  (perfiles.codigo = 'developer' AND permisos.codigo = 'page.developer.access')
  OR (perfiles.codigo = 'accountant' AND permisos.codigo = 'page.accounting.access')
  OR (perfiles.codigo = 'teacher' AND permisos.codigo = 'page.general.access');
