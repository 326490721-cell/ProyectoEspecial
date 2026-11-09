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
    $supplierCount = 0;
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

        if ($canManageSuppliers) {
            $supplierCount = (int) $database
                ->query('SELECT COUNT(*) FROM proveedores WHERE activo = 1')
                ->fetch_row()[0];
        }
    }
} catch (Throwable $error) {
    error_log('FrikSix dashboard failed: ' . $error->getMessage());
    http_response_code(500);
    exit('The dashboard is temporarily unavailable.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de cuenta - FrikSix Arcade</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <header class="bar">
    <a class="brand" href="../index.html">FrikSix Arcade - Mi cuenta</a>
  </header>
  <main class="contenedor account-dashboard">
    <h1>Hola, <?= escapeHtml($user['nombre_usuario']) ?></h1>
    <section class="account-summary" aria-labelledby="account-profile">
      <h2 id="account-profile">Tu perfil: <?= escapeHtml($user['nombre']) ?></h2>
      <p><?= escapeHtml(match ($user['codigo']) {
          'developer' => 'Tu cuenta tiene acceso de desarrollador. Las herramientas de administración aún no están disponibles.',
          'accountant' => $canManageSuppliers
              ? 'Desde este panel puedes gestionar las empresas proveedoras.'
              : 'Tu perfil no tiene habilitada la gestión de proveedores. Contacta al administrador.',
          'teacher' => 'Tu perfil tiene acceso general a las páginas de FrikSix Arcade.',
          default => 'Tu perfil no tiene herramientas disponibles.'
      }) ?></p>
    </section>

    <section aria-labelledby="account-actions">
      <h2 id="account-actions">Accesos rápidos</h2>
      <nav class="dashboard-links" aria-label="Accesos rápidos">
        <a class="dashboard-action" href="../index.html">Inicio</a>
        <a class="dashboard-action" href="../catalogo.html">Ver catálogo</a>
        <?php if ($canManageSuppliers): ?>
          <a class="dashboard-action" href="proveedores.php">Registrar proveedor</a>
        <?php endif; ?>
      </nav>
    </section>

    <?php if ($canManageSuppliers): ?>
      <section class="supplier-summary" aria-labelledby="supplier-count">
        <h2 id="supplier-count">Proveedores activos</h2>
        <p><?= $supplierCount ?></p>
        <a href="proveedores.php">Registrar una empresa proveedora</a>
      </section>
    <?php endif; ?>

    <form class="dashboard-logout" action="logout.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['csrf_token']) ?>">
      <button class="dashboard-action dashboard-action--logout" type="submit">Cerrar sesión</button>
    </form>
  </main>
</body>
</html>
