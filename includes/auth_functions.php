<?php
require_once __DIR__ . '/password_policy.php';
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    private $passwordPolicy;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->passwordPolicy = new PasswordPolicy();
    }

    // Registrar novo usuário
    public function register($username, $email, $password) {
        // Validar senha
        if (!$this->passwordPolicy->validate($password)) {
            throw new Exception("Password does not meet security requirements.");
        }

        // Verificar se usuário ou email já existem
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("Username or email already exists.");
        }

        // Hash da senha
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Inserir usuário
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, password_changed_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$username, $email, $password_hash]);

        // Registrar no histórico de senhas
        $user_id = $this->db->lastInsertId();
        $this->addToPasswordHistory($user_id, $password_hash);

        return $user_id;
    }

    // Login do usuário
    public function login($username, $password) {
        // Buscar usuário
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() == 0) {
            throw new Exception("Invalid username or password.");
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar senha
        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception("Invalid username or password.");
        }

        // Verificar se a senha expirou
        $password_age = time() - strtotime($user['password_changed_at']);
        $max_password_age = 45 * 24 * 60 * 60; // 45 dias em segundos
        
        if ($password_age > $max_password_age) {
            $_SESSION['user_id'] = $user['id']; // Temporário para permitir troca de senha
            throw new Exception("Your password has expired. Please change it now.");
        }

        // Iniciar sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_activity'] = time();

        return true;
    }

    // Trocar senha
    public function changePassword($user_id, $current_password, $new_password) {
        // Verificar senha atual
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($current_password, $user['password_hash'])) {
            throw new Exception("Current password is incorrect.");
        }

        // Validar nova senha
        if (!$this->passwordPolicy->validate($new_password)) {
            throw new Exception("New password does not meet security requirements.");
        }

        // Verificar se a senha foi usada recentemente
        if ($this->isPasswordInHistory($user_id, $new_password, 7)) {
            throw new Exception("You cannot reuse any of your last 7 passwords.");
        }

        // Atualizar senha
        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, password_changed_at = NOW() WHERE id = ?");
        $stmt->execute([$new_password_hash, $user_id]);

        // Adicionar ao histórico de senhas
        $this->addToPasswordHistory($user_id, $new_password_hash);

        return true;
    }

    // Adicionar senha ao histórico
    private function addToPasswordHistory($user_id, $password_hash) {
        $stmt = $this->db->prepare("INSERT INTO password_history (user_id, password_hash) VALUES (?, ?)");
        $stmt->execute([$user_id, $password_hash]);
    }

    // Verificar se senha está no histórico
    private function isPasswordInHistory($user_id, $password, $last_n = 7) {
        $stmt = $this->db->prepare("
            SELECT password_hash FROM password_history 
            WHERE user_id = ? 
            ORDER BY changed_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $user_id);
        $stmt->bindValue(2, $last_n, PDO::PARAM_INT);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $row['password_hash'])) {
                return true;
            }
        }
        
        return false;
    }

    // Verificar se usuário está logado
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Obter informações do usuário
    public function getUser($user_id) {
        $stmt = $this->db->prepare("SELECT id, username, email, password_changed_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
