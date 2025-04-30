<?php
class SessionManager {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 0); 
            ini_set('session.use_strict_mode', 1);
            
            session_start();
        }
    }

    public function regenerate() {
        session_regenerate_id(true);
    }

    public function destroy() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
    }

    public function isSessionExpired($timeout = 1800) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            return true;
        }
        $_SESSION['last_activity'] = time();
        return false;
    }
}
?>
