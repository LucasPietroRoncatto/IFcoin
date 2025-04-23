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

// Verificar se a sessão expirou
if ($session->isSessionExpired()) {
    $session->destroy();
    header("Location: login.php?expired=1");
    exit;
}

// Atualizar última atividade
$_SESSION['last_activity'] = time();

// Obter informações do usuário
$user = $auth->getUser($_SESSION['user_id']);

// Calcular dias até a expiração da senha
$password_changed = new DateTime($user['password_changed_at']);
$now = new DateTime();
$days_since_change = $now->diff($password_changed)->days;
$days_remaining = 45 - $days_since_change;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFCoin - Painel</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Seja bem-vindo, <?php echo htmlspecialchars($user['username']); ?>!</h1>
        
        <div class="user-info">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Última senha alterada em:</strong> <?php echo htmlspecialchars($user['password_changed_at']); ?></p>
            
            <div class="<?php echo $days_remaining <= 7 ? 'warning' : 'info'; ?>">
                <p>Sua senha vai expirar em: <?php echo $days_remaining; ?> dias.</p>
            </div>
        </div>
        <div class="actions">
            <a href="users_list.php" class="btn">Ver Usuários Cadastrados</a>
            <a href="change_password.php" class="btn">Alterar Senha</a>
            <a href="logout.php" class="btn logout">Sair</a>
        </div>
    </div>
</body>
</html>