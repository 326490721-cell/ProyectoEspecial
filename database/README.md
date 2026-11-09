# Base de datos

El proyecto usa MySQL/MariaDB y una sola base de datos llamada
`friksix_arcade`. Las entidades del sistema se agregan como tablas separadas.

## Crear la base inicial

Para una instalación nueva, ejecuta `schema.sql` desde MySQL Workbench o desde
el cliente de MySQL:

```text
SOURCE ruta/completa/al/proyecto/database/schema.sql;
```

El esquema crea proveedores, perfiles, permisos y usuarios, y registra los tres
perfiles iniciales. No crea cuentas de usuario ni agrega proveedores de ejemplo.
El RFC es opcional; si se captura, debe ser único. Para dejarlo sin especificar,
guárdalo como `NULL`, no como una cadena vacía.

## Perfiles y usuarios

- `Desarrollador` tiene el permiso `page.developer.access`.
- `Contador` tiene el permiso `page.accounting.access`.
- `Maestro` tiene el permiso `page.general.access`, que debe interpretarse en
  la aplicación como acceso general a todas sus páginas.
- `perfil_permisos` relaciona perfiles con permisos; `usuarios` asigna a cada
  cuenta un único perfil.
- `hash_contrasena` debe contener un hash creado en el servidor (por ejemplo,
  con PHP `password_hash()`), nunca una contraseña en texto plano.
- La base define los permisos, pero las páginas deben comprobarlos en el
  servidor antes de mostrar información o procesar solicitudes. Ocultar un
  enlace en la interfaz no sustituye esa comprobación.

## Crear una cuenta en Windows con XAMPP

En VS Code, abre la paleta de comandos con `Ctrl+Shift+P`, elige **Tasks: Run
Task** y selecciona **FrikSix: crear usuario**. Contesta las preguntas en la
terminal integrada.

También puedes ejecutarlo desde PowerShell:

```powershell
& 'C:\xampp\php\php.exe' 'D:\Paginas\ProyectoEspecial\database\crear_usuario.php'
```

La herramienta pide nombre, correo opcional, perfil y contraseña. La contraseña
se introduce de forma oculta y se guarda como hash. El script solo funciona
desde la consola local, no como una página pública. Acepta contraseñas de al
menos 12 caracteres y hasta 72 bytes.

Si tu MariaDB tiene una contraseña configurada para la cuenta usada, define
`FRIKSIX_DB_PASSWORD` en PowerShell antes de ejecutar el comando. También puedes
configurar `FRIKSIX_DB_HOST`, `FRIKSIX_DB_USER` y `FRIKSIX_DB_NAME`; por defecto
usa `127.0.0.1`, `root` sin contraseña y `friksix_arcade` (configuración local
habitual de XAMPP).

## Iniciar sesión

En VS Code, ejecuta **Tasks: Run Task** > **FrikSix: iniciar sitio web**.
Abre `http://127.0.0.1:8000/logIn.html` en el navegador e inicia sesión con una
cuenta creada mediante la herramienta anterior. La contraseña debe ser la
misma que escribiste al crear la cuenta.
Los pasos para iniciar el servidor y probar accesos correctos e incorrectos
están en [`iniciarServidor.txt`](../iniciarServidor.txt).

No abras `logIn.html` como archivo (`file://`) ni uses Live Server (por ejemplo,
el puerto `5500`): esos métodos no ejecutan PHP y pueden mostrar el código
fuente de los procesadores. Usa únicamente la dirección local del servidor PHP
que aparece arriba; no escribas credenciales si la barra muestra otra dirección.

Al autenticarte, el servidor valida la contraseña con `password_verify()`,
guarda la sesión y verifica el permiso del perfil antes de mostrar
`scripts/dashboard.php`. El panel ofrece accesos al inicio y al catálogo; el
perfil Contador también puede registrar proveedores si tiene ese permiso. Usa
**Cerrar sesión** para salir.
Una contraseña debe tener al menos 12 caracteres y como máximo 72 bytes. El
inicio de sesión permite hasta 10 intentos fallidos por dirección IP en 15
minutos. Para una base existente, aplica una sola vez
`migrations/003_login_attempts.sql`; las instalaciones nuevas ya incluyen esa
tabla en `schema.sql`.
Las páginas exclusivas del desarrollador y del contador todavía no existen; el
panel informa qué perfil inició sesión mientras se preparan esas páginas.

## Registrar empresas proveedoras

El formulario está en `scripts/proveedores.php` y solo permite el acceso al
perfil Contador con el permiso `suppliers.manage`. En una base que ya existe,
aplica una sola vez `migrations/002_permisos_proveedores_contador.sql`; en una
instalación nueva, `schema.sql` ya incluye ese permiso. Después de iniciar
sesión como Contador, abre el panel y selecciona **Registrar empresa
proveedora**. El RFC es opcional, pero no se pueden repetir RFC registrados.

## Agregar entidades después

- Agrega tablas relacionadas al esquema inicial en `schema.sql` cuando todavía
  no se haya instalado en otros entornos.
- Para cambios en una base ya instalada, crea un archivo SQL nuevo y numerado
  dentro de `migrations/` (por ejemplo, `001_crear_consolas.sql`).
- Para una base que ya ejecutó el esquema inicial, aplica
  `migrations/001_usuarios_perfiles_permisos.sql` una sola vez para agregar
  usuarios, perfiles y permisos.
- Para habilitar el alta de proveedores a Contador en una base existente,
  aplica `migrations/002_permisos_proveedores_contador.sql` una sola vez.
- Cada migración debe contener solo el cambio incremental y aplicarse una vez,
  en orden. No edites una migración que ya se ejecutó en otro entorno.
- Relaciona tablas con claves foráneas y agrega índices a las columnas que se
  consulten frecuentemente.
- No guardes contraseñas de conexión ni datos reales sensibles en estos archivos.
