<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

startAppSession();

// Sin sesión autenticada, el visitante debe volver al formulario.
if (!isset($_SESSION['user_id'], $_SESSION['csrf_token'])) {
    header('Location: ../logIn.html', true, 303);
    exit;
}

try {
    $database = appDatabase();
    // Vuelve a leer la cuenta y el perfil para no confiar solo en datos del navegador.
    $statement = $database->prepare(
        'SELECT u.id, u.nombre_usuario, p.id AS perfil_id, p.codigo, p.nombre
         FROM usuarios AS u
         INNER JOIN perfiles AS p ON p.id = u.perfil_id
         WHERE u.id = ? AND u.activo = 1
         LIMIT 1'
    );
    $userId = (int) $_SESSION['user_id'];
    $statement->bind_param('i', $userId);
    $statement->execute();
    $user = $statement->get_result()->fetch_assoc();

    if (!$user) {
        $_SESSION = [];
        session_destroy();
        header('Location: ../logIn.html?error=credentials', true, 303);
        exit;
    }

    // Traduce cada perfil conocido a su permiso requerido para el panel.
    $permissionByProfile = [
        'developer' => 'page.developer.access',
        'accountant' => 'page.accounting.access',
        'teacher' => 'page.general.access',
    ];
    $requiredPermission = $permissionByProfile[$user['codigo']] ?? null;
    if ($requiredPermission === null) {
        http_response_code(403);
        exit('Access denied.');
    }

    // El permiso general también autoriza este panel, además del permiso específico.
    $permissionQuery = $database->prepare(
        "SELECT COUNT(*)
         FROM perfil_permisos AS pp
         INNER JOIN permisos AS pe ON pe.id = pp.permiso_id
         WHERE pp.perfil_id = ?
           AND (pe.codigo = ? OR pe.codigo = 'page.general.access')"
    );
    $permissionQuery->bind_param('is', $user['perfil_id'], $requiredPermission);
    $permissionQuery->execute();
    $hasPermission = (int) $permissionQuery->get_result()->fetch_row()[0] > 0;

    // Detiene la respuesta si el perfil no tiene el permiso registrado en la base.
    if (!$hasPermission) {
        http_response_code(403);
        exit('Access denied.');
    }

    $canManageSuppliers = false;
    if ($user['codigo'] === 'accountant') {
        $supplierPermission = $database->prepare(
            "SELECT COUNT(*)
             FROM perfil_permisos AS pp
             INNER JOIN permisos AS pe ON pe.id = pp.permiso_id
             WHERE pp.perfil_id = ? AND pe.codigo = 'suppliers.manage'"
        );
        $supplierPermission->bind_param('i', $user['perfil_id']);
        $supplierPermission->execute();
        $canManageSuppliers =
            (int) $supplierPermission->get_result()->fetch_row()[0] > 0;
    }
} catch (Throwable $error) {
    error_log('FrikSix dashboard failed: ' . $error->getMessage());
    http_response_code(500);
    exit('The dashboard is temporarily unavailable.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FrikSix Arcade - Account</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <header class="bar">
    <a class="brand" href="../index.html">FrikSix Arcade</a>
  </header>
  <main class="contenedor">
    <h1>Welcome, <?= escapeHtml($user['nombre_usuario']) ?></h1>
    <h2>Profile: <?= escapeHtml($user['nombre']) ?></h2>
    <p>You are signed in. Your account has access according to its assigned profile.</p>
    <p>The dedicated developer and accounting pages have not been added yet.</p>
    <p><a class="button_text" href="../index.html">Home</a></p>
    <p><a class="button_text" href="../catalogo.html">Game catalog</a></p>
    <!-- El token evita que otro sitio fuerce el cierre de sesión del usuario. -->
    <p><?= escapeHtml(match ($user['codigo']) {
        'developer' => 'Contenido para el desarrollador',
        'accountant' => 'Contenido para el contador',
        'teacher' => 'Contenido para el maestro',
        default => 'Perfil no reconocido'
    }) ?></p>
    <?php if ($canManageSuppliers): ?>
      <p><a class="button_text" href="proveedores.php">Registrar empresa proveedora</a></p>
    <?php endif; ?>
    <form action="logout.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['csrf_token']) ?>">
      <button class="login-submit" type="submit">Log Out</button>
    </form>

  </main>
</body>
</html>
