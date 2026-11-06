<?php

// Este alta se limita a la consola local para no exponer una página de registro pública.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function prompt(string $message): string
{
    fwrite(STDOUT, $message);
    $value = fgets(STDIN);

    if ($value === false) {
        throw new RuntimeException('No se pudo leer la respuesta.');
    }

    return trim($value);
}

function promptPassword(): string
{
    // PowerShell oculta la contraseña durante la captura en Windows.
    $command = '$secure = Read-Host "Contraseña (no se mostrará)" -AsSecureString; '
        . '$bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure); '
        . 'try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr) } '
        . 'finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr) }';
    $process = proc_open(
        ['powershell.exe', '-NoProfile', '-Command', $command],
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes
    );

    if (!is_resource($process)) {
        throw new RuntimeException('No se pudo abrir la solicitud segura de contraseña.');
    }

    fclose($pipes[0]);
    $password = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0 || $password === false) {
        throw new RuntimeException(
            'No se pudo leer la contraseña de forma segura: ' . trim($error)
        );
    }

    return rtrim($password, "\r\n");
}

try {
    // Valida los datos antes de consultar la base.
    $username = prompt('Nombre de usuario (3-20 letras o números): ');
    if (!preg_match('/^[A-Za-z0-9]{3,20}$/', $username)) {
        throw new RuntimeException('El nombre debe tener de 3 a 20 letras o números.');
    }

    $email = prompt('Correo (opcional, Enter para omitir): ');
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('El correo no tiene un formato válido.');
    }

    // Usa las variables de entorno si existen; de otro modo, usa la configuración local de XAMPP.
    $db = new mysqli(
        getenv('FRIKSIX_DB_HOST') ?: '127.0.0.1',
        getenv('FRIKSIX_DB_USER') ?: 'root',
        getenv('FRIKSIX_DB_PASSWORD') ?: '',
        getenv('FRIKSIX_DB_NAME') ?: 'friksix_arcade'
    );
    $db->set_charset('utf8mb4');

    // Muestra los perfiles existentes para que la cuenta quede asociada a uno válido.
    $roles = $db->query('SELECT id, codigo, nombre FROM perfiles ORDER BY id');
    if ($roles->num_rows === 0) {
        throw new RuntimeException('No hay perfiles. Ejecuta primero database/schema.sql.');
    }

    $roleOptions = [];
    echo PHP_EOL . "Perfiles disponibles:" . PHP_EOL;
    while ($role = $roles->fetch_assoc()) {
        $roleOptions[] = $role;
    }

    foreach ($roleOptions as $index => $role) {
        printf("  %d) %s\n", $index + 1, $role['nombre']);
    }

    $roleChoice = prompt('Elige el número del perfil: ');
    if (!ctype_digit($roleChoice)
        || !isset($roleOptions[(int) $roleChoice - 1])) {
        throw new RuntimeException('Selecciona uno de los números de la lista.');
    }

    $password = promptPassword();
    if (strlen($password) < 12 || strlen($password) > 72) {
        throw new RuntimeException('La contraseña debe tener entre 12 y 72 bytes.');
    }

    // Guarda un hash unidireccional y usa parámetros para insertar los datos de forma segura.
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $profileId = (int) $roleOptions[(int) $roleChoice - 1]['id'];
    $emailValue = $email === '' ? null : $email;
    $statement = $db->prepare(
        'INSERT INTO usuarios (nombre_usuario, correo, hash_contrasena, perfil_id)
         VALUES (?, ?, ?, ?)'
    );
    $statement->bind_param('sssi', $username, $emailValue, $hash, $profileId);
    $statement->execute();

    printf("Cuenta '%s' creada con el perfil %s.\n", $username, $roleOptions[(int) $roleChoice - 1]['nombre']);
} catch (mysqli_sql_exception $error) {
    if ((int) $error->getCode() === 1062) {
        fwrite(STDERR, "Error: ese nombre de usuario o correo ya está registrado.\n");
        exit(1);
    }

    fwrite(STDERR, 'Error de base de datos: ' . $error->getMessage() . PHP_EOL);
    exit(1);
} catch (Throwable $error) {
    fwrite(STDERR, 'Error: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
