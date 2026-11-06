<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

// El cierre cambia el estado del servidor, por eso solo acepta solicitudes POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

startAppSession();
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

// Comprueba que el formulario de salida se originó en la sesión actual.
if (!is_string($submittedToken)
    || !is_string($sessionToken)
    || !hash_equals($sessionToken, $submittedToken)) {
    http_response_code(403);
    exit('Invalid request.');
}

// Borra la sesión del servidor y expira su cookie en el navegador.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => $cookie['samesite'] ?? 'Lax',
        ]
    );
}
session_destroy();

header('Location: ../logIn.html?logout=1', true, 303);
exit;
