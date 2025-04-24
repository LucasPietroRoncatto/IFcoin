<?php
class PasswordPolicy {
    private $min_length = 8;
    private $require_uppercase = true;
    private $require_lowercase = true;
    private $require_number = true;
    private $require_special = true;
    private $special_chars = '!@#$%^&*()\-_=+{};:,<.>?';

    public function validate($password) {
        if (strlen($password) < $this->min_length) {
            return false;
        }

        if ($this->require_uppercase && !preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if ($this->require_lowercase && !preg_match('/[a-z]/', $password)) {
            return false;
        }

        if ($this->require_number && !preg_match('/[0-9]/', $password)) {
            return false;
        }

        if ($this->require_special && !preg_match('/[' . preg_quote($this->special_chars, '/') . ']/', $password)) {
            return false;
        }

        return true;
    }

    public function getRequirements() {
        $requirements = [
            "Minimum length of {$this->min_length} characters",
        ];

        if ($this->require_uppercase) {
            $requirements[] = "At least one uppercase letter (A-Z)";
        }

        if ($this->require_lowercase) {
            $requirements[] = "At least one lowercase letter (a-z)";
        }

        if ($this->require_number) {
            $requirements[] = "At least one number (0-9)";
        }

        if ($this->require_special) {
            $requirements[] = "At least one special character ({$this->special_chars})";
        }

        return $requirements;
    }
}
?>
