-- Selecciona la base donde está instalado el sistema.
USE friksix_arcade;

-- Define el permiso específico para registrar empresas proveedoras.
INSERT IGNORE INTO permisos (codigo, descripcion)
VALUES ('suppliers.manage', 'Registrar proveedores.');

-- Autoriza solamente al perfil Contador a usar el formulario de proveedores.
INSERT IGNORE INTO perfil_permisos (perfil_id, permiso_id)
SELECT perfiles.id, permisos.id
FROM perfiles
JOIN permisos ON permisos.codigo = 'suppliers.manage'
WHERE perfiles.codigo = 'accountant';
