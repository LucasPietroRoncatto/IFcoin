<?php
require_once __DIR__ . '/includes/auth_functions.php';
require_once __DIR__ . '/includes/session_manager.php';

$session = new SessionManager();
$auth = new Auth();
$passwordPolicy = new PasswordPolicy();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }

        $auth->register($username, $email, $password);
        $success = "Registrado com sucesso, agora você pode logar";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFCoin - Registrar-se</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>IFCoin - Registrar-se</h1>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
            <p><a href="login.php">Clique aqui para logar</a></p>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Usuário:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-requirements">
                        <strong>Requisitos da senha:</strong>
                        <ul>
                            <?php foreach ($passwordPolicy->getRequirements() as $req): ?>
                                <li><?php echo htmlspecialchars($req); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar senha:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit">Registrar</button>
            </form>
            
            <p>Já possui conta? <a href="login.php">Login</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
