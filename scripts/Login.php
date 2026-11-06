<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

// Solo se aceptan envíos del formulario; abrir este procesador directamente no inicia sesión.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

startAppSession();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
// Rechaza entradas mal formadas antes de consultar la base.
if (!is_string($username)
    || !preg_match('/^[A-Za-z0-9]{3,20}$/', $username)
    || !is_string($password)
    || strlen($password) < 12
    || strlen($password) > 72) {
    header('Location: ../logIn.html?error=credentials', true, 303);
    exit;
}

try {
    $database = appDatabase();
    // La consulta preparada busca una cuenta activa y su perfil.
    $statement = $database->prepare(
        'SELECT u.id, u.hash_contrasena, p.codigo, p.nombre
         FROM usuarios AS u
         INNER JOIN perfiles AS p ON p.id = u.perfil_id
         WHERE u.nombre_usuario = ? AND u.activo = 1
         LIMIT 1'
    );
    $statement->bind_param('s', $username);
    $statement->execute();
    $user = $statement->get_result()->fetch_assoc();

    // Compara la contraseña escrita con el hash guardado sin revelar qué dato falló.
    if (!$user || !password_verify($password, $user['hash_contrasena'])) {
        header('Location: ../logIn.html?error=credentials', true, 303);
        exit;
    }

    // Cambia el identificador de sesión tras autenticar para evitar fijación de sesión.
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: dashboard.php', true, 303);
    exit;
} catch (Throwable $error) {
    // El detalle se guarda en el registro del servidor, no se muestra al visitante.
    error_log('FrikSix login failed: ' . $error->getMessage());
    header('Location: ../logIn.html?error=server', true, 303);
    exit;
}