# Migraciones

Guarda aquí los cambios incrementales para instalaciones que ya tienen la base
de datos creada. Nombra cada archivo con un número consecutivo y una breve
descripción, por ejemplo `001_crear_consolas.sql`.

Ejecuta las migraciones en orden y registra cuáles se aplicaron en cada entorno.
El esquema inicial está en `../schema.sql`. Para una base que ya ejecutó ese
esquema, `001_usuarios_perfiles_permisos.sql` agrega los usuarios, perfiles y
permisos iniciales.
`003_login_attempts.sql` agrega el registro necesario para limitar intentos de
inicio de sesión en bases existentes.
