<?php
require_once __DIR__ . '/includes/auth_functions.php';
require_once __DIR__ . '/includes/session_manager.php';

$session = new SessionManager();
$auth = new Auth();

// Verificar se está logado
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';
$is_expired = isset($_GET['expired']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            throw new Exception("New passwords do not match.");
        }
        
        $auth->changePassword($_SESSION['user_id'], $current_password, $new_password);
        $success = "Password changed successfully!";
        
        // Se estava expirada, redirecionar para o dashboard após 3 segundos
        if ($is_expired) {
            header("Refresh: 3; url=dashboard.php");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obter informações do usuário
$user = $auth->getUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFCoin - Alterar senha</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Alterar senha</h1>
        
        <?php if ($is_expired): ?>
            <div class="alert warning">
                Sua senha expirou. Você deve alterá-la para continuar.
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
            <?php if ($is_expired): ?>
                <p>Redirecionando para o painel...</p>
            <?php else: ?>
                <p><a href="dashboard.php">Voltando para o painel</a></p>
            <?php endif; ?>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label for="current_password">Senha atual:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nova Senha:</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <div class="password-requirements">
                        <strong>Requisitos da senha:</strong>
                        <ul>
                            <li>Tamanho mínimo de 8 caractéres</li>
                            <li>Ao menos uma letra maiúscula(A-Z)</li>
                            <li>Ao menos uma letra minúscula (a-z)</li>
                            <li>Ao menos um número (0-9)</li>
                            <li>Ao menos um caractér especial (!@#$%^&* etc.)</li>
                            <li>Não pode ser igual a uma de suas últimas 7 senhas</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar nova senha:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit">Alterar senha</button>
                <a href="dashboard.php" class="btn cancel">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>