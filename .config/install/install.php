<?php

$errors = [];
$success = false;

if (file_exists('.config/install/install.lock')) {
    die("Installation is already locked. Remove install.lock to reinstall.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = trim($_POST['db_pass'] ?? '');
    $appUrl = trim($_POST['app_url'] ?? '');

    $envTemplate = file_get_contents('/.config/.env.example');
    if ($envTemplate === false) {
        $errors[] = "Failed to read .env.example template.";
    }

    $htaccessTemplate = file_get_contents('/.config/.htaccess.example');
    if ($htaccessTemplate === false) {
        $errors[] = "Failed to read .htaccess.example template.";
    }

    if (empty($errors)) {
        $envContent = str_replace(
            ['{{DB_HOST}}', '{{DB_NAME}}', '{{DB_USER}}', '{{DB_PASS}}', '{{APP_URL}}'],
            [$dbHost, $dbName, $dbUser, $dbPass, $appUrl],
            $envTemplate
        );

        if (file_put_contents('/.env', $envContent) === false) {
            $errors[] = "Failed to write .env file.";
        }

        if (file_put_contents('/.htaccess', $htaccessTemplate) === false) {
            $errors[] = "Failed to write .htaccess file.";
        }

        if (empty($errors)) {
            file_put_contents('.config/install/install.lock', "Installed on " . date('c'));
            $success = true;
        }
    }
}

if ($success):
?>
    <p>✅ Installation completed successfully.</p>
    <p>For security, please delete or rename <code>install.php</code>.</p>
<?php else: ?>
    <?php foreach ($errors as $error): ?>
        <p>Error: <?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <form method="POST">
        <label>Database Host: <input name="db_host" required></label><br>
        <label>Database Name: <input name="db_name" required></label><br>
        <label>Database User: <input name="db_user" required></label><br>
        <label>Database Password: <input name="db_pass" type="password"></label><br>
        <label>App URL: <input name="app_url" required></label><br>
        <button type="submit">Install</button>
    </form>
<?php endif; ?>