<?php
declare(strict_types=1);

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Refuerza la cookie de sesión y evita aceptar identificadores de sesión inventados.
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);

    if (!session_start()) {
        throw new RuntimeException('No se pudo iniciar la sesión.');
    }
}

function appDatabase(): mysqli
{
    // Reporta errores de conexión/consultas y permite capturarlos en el flujo que corresponda.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Permite cambiar la configuración de MariaDB sin guardar contraseñas en el código.
    $database = new mysqli(
        getenv('FRIKSIX_DB_HOST') ?: '127.0.0.1',
        getenv('FRIKSIX_DB_USER') ?: 'root',
        getenv('FRIKSIX_DB_PASSWORD') ?: '',
        getenv('FRIKSIX_DB_NAME') ?: 'friksix_arcade'
    );
    $database->set_charset('utf8mb4');

    return $database;
}

function escapeHtml(string $value): string
{
    // Escapa datos antes de insertarlos en el HTML.
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
