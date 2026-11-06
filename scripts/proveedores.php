<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

startAppSession();

if (!isset($_SESSION['user_id'], $_SESSION['csrf_token'])) {
    header('Location: ../logIn.html', true, 303);
    exit;
}

$formValues = [
    'empresa' => '',
    'persona_contacto' => '',
    'rfc' => '',
    'correo' => '',
    'telefono' => '',
    'direccion' => '',
    'sitio_web' => '',
    'notas' => '',
];
$errorMessage = '';
$successMessage = '';

try {
    $database = appDatabase();
    $userQuery = $database->prepare(
        'SELECT u.id
         FROM usuarios AS u
         INNER JOIN perfiles AS p ON p.id = u.perfil_id
         INNER JOIN perfil_permisos AS pp ON pp.perfil_id = p.id
         INNER JOIN permisos AS pe ON pe.id = pp.permiso_id
         WHERE u.id = ?
           AND u.activo = 1
           AND p.codigo = ?
           AND pe.codigo = ?
         LIMIT 1'
    );
    $userId = (int) $_SESSION['user_id'];
    $requiredProfile = 'accountant';
    $requiredPermission = 'suppliers.manage';
    $userQuery->bind_param('iss', $userId, $requiredProfile, $requiredPermission);
    $userQuery->execute();

    if (!$userQuery->get_result()->fetch_assoc()) {
        http_response_code(403);
        exit('Access denied.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($formValues as $field => $_) {
            $value = $_POST[$field] ?? '';
            if (!is_string($value)) {
                throw new InvalidArgumentException('Revisa los datos del formulario.');
            }
            $formValues[$field] = trim($value);
        }

        $submittedToken = $_POST['csrf_token'] ?? '';
        if (!is_string($submittedToken)
            || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            http_response_code(403);
            exit('Invalid request.');
        }

        if ($formValues['empresa'] === ''
            || mb_strlen($formValues['empresa'], 'UTF-8') > 150) {
            throw new InvalidArgumentException('El nombre de la empresa es obligatorio y admite hasta 150 caracteres.');
        }
        if (mb_strlen($formValues['persona_contacto'], 'UTF-8') > 150
            || mb_strlen($formValues['direccion'], 'UTF-8') > 255
            || mb_strlen($formValues['telefono'], 'UTF-8') > 30
            || strlen($formValues['notas']) > 65535) {
            throw new InvalidArgumentException('Uno de los campos supera la longitud permitida.');
        }

        $formValues['rfc'] = strtoupper($formValues['rfc']);
        if ($formValues['rfc'] !== ''
            && !preg_match('/^(?:[A-ZÑ&]{3,4})\d{6}[A-Z0-9]{3}$/', $formValues['rfc'])) {
            throw new InvalidArgumentException('El RFC debe tener 12 o 13 caracteres y un formato válido.');
        }
        if ($formValues['correo'] !== ''
            && (!filter_var($formValues['correo'], FILTER_VALIDATE_EMAIL)
                || mb_strlen($formValues['correo'], 'UTF-8') > 254)) {
            throw new InvalidArgumentException('Escribe un correo válido de hasta 254 caracteres.');
        }
        if ($formValues['sitio_web'] !== '') {
            $website = filter_var($formValues['sitio_web'], FILTER_VALIDATE_URL);
            $scheme = $website ? strtolower((string) parse_url($website, PHP_URL_SCHEME)) : '';
            if (!$website || !in_array($scheme, ['http', 'https'], true)
                || mb_strlen($formValues['sitio_web'], 'UTF-8') > 2048) {
                throw new InvalidArgumentException('El sitio web debe ser una dirección válida que empiece con http o https.');
            }
        }

        $company = $formValues['empresa'];
        $contact = $formValues['persona_contacto'] === '' ? null : $formValues['persona_contacto'];
        $taxId = $formValues['rfc'] === '' ? null : $formValues['rfc'];
        $email = $formValues['correo'] === '' ? null : $formValues['correo'];
        $phone = $formValues['telefono'] === '' ? null : $formValues['telefono'];
        $address = $formValues['direccion'] === '' ? null : $formValues['direccion'];
        $website = $formValues['sitio_web'] === '' ? null : $formValues['sitio_web'];
        $notes = $formValues['notas'] === '' ? null : $formValues['notas'];

        $insert = $database->prepare(
            'INSERT INTO proveedores
                (empresa, persona_contacto, rfc, correo, telefono, direccion, sitio_web, notas)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->bind_param(
            'ssssssss',
            $company,
            $contact,
            $taxId,
            $email,
            $phone,
            $address,
            $website,
            $notes
        );
        $insert->execute();

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: proveedores.php?saved=1', true, 303);
        exit;
    }

    if (isset($_GET['saved'])) {
        $successMessage = 'La empresa se registró correctamente.';
    }
} catch (InvalidArgumentException $error) {
    $errorMessage = $error->getMessage();
    http_response_code(422);
} catch (mysqli_sql_exception $error) {
    if ((int) $error->getCode() === 1062) {
        $errorMessage = 'Ese RFC ya está registrado.';
        http_response_code(409);
    } else {
        error_log('FrikSix supplier registration failed: ' . $error->getMessage());
        $errorMessage = 'No se pudo guardar la empresa. Inténtalo más tarde.';
        http_response_code(500);
    }
} catch (Throwable $error) {
    error_log('FrikSix supplier form failed: ' . $error->getMessage());
    $errorMessage = 'El formulario no está disponible. Inténtalo más tarde.';
    http_response_code(500);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar proveedor - FrikSix Arcade</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../css/logIn.css">
</head>
<body>
  <header class="bar">
    <a class="brand" href="dashboard.php">FrikSix Arcade</a>
  </header>
  <main class="contenedor login-card">
    <h1>Registrar empresa proveedora</h1>
    <p>Los campos marcados con * son obligatorios.</p>
    <?php if ($errorMessage !== ''): ?>
      <p role="alert"><?= escapeHtml($errorMessage) ?></p>
    <?php endif; ?>
    <?php if ($successMessage !== ''): ?>
      <p role="status"><?= escapeHtml($successMessage) ?></p>
    <?php endif; ?>
    <form class="login-form" method="post" action="proveedores.php">
      <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['csrf_token']) ?>">

      <label for="empresa">Nombre de la empresa *</label>
      <input id="empresa" name="empresa" type="text" maxlength="150" required
             value="<?= escapeHtml($formValues['empresa']) ?>">

      <label for="persona_contacto">Persona de contacto</label>
      <input id="persona_contacto" name="persona_contacto" type="text" maxlength="150"
             value="<?= escapeHtml($formValues['persona_contacto']) ?>">

      <label for="rfc">RFC (opcional)</label>
      <input id="rfc" name="rfc" type="text" maxlength="13" autocomplete="off"
             value="<?= escapeHtml($formValues['rfc']) ?>">

      <label for="correo">Correo electrónico</label>
      <input id="correo" name="correo" type="email" maxlength="254"
             value="<?= escapeHtml($formValues['correo']) ?>">

      <label for="telefono">Teléfono</label>
      <input id="telefono" name="telefono" type="tel" maxlength="30"
             value="<?= escapeHtml($formValues['telefono']) ?>">

      <label for="direccion">Dirección</label>
      <input id="direccion" name="direccion" type="text" maxlength="255"
             value="<?= escapeHtml($formValues['direccion']) ?>">

      <label for="sitio_web">Sitio web</label>
      <input id="sitio_web" name="sitio_web" type="url" maxlength="2048"
             placeholder="https://ejemplo.com"
             value="<?= escapeHtml($formValues['sitio_web']) ?>">

      <label for="notas">Notas</label>
      <textarea id="notas" name="notas" maxlength="65535" rows="4"><?= escapeHtml($formValues['notas']) ?></textarea>

      <button class="login-submit" type="submit">Guardar proveedor</button>
    </form>
    <p><a class="button_text" href="dashboard.php">Volver al panel</a></p>
  </main>
</body>
</html>
