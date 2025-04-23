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

// Obter lista de usuários
try {
    $database = new Database();
    $db = $database->connect();
    
    $stmt = $db->prepare("SELECT username, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erro ao carregar lista de usuários: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFCoin - Lista de Usuários</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Usuários Cadastrados</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="users-list">
            <table>
                <thead>
                    <tr>
                        <th>Nome de Usuário</th>
                        <th>Data de Cadastro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="actions">
            <a href="dashboard.php" class="btn">Voltar ao Painel</a>
        </div>
    </div>
</body>
</html>