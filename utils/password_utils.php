<?php
function passwordMeetsPolicy(string $password): bool {
    if (strlen($password) < 16) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/\d/', $password)) {
        return false;
    }
    if (!preg_match('/[\W_]/', $password)) {
        return false;
    }
    return true;
}

function generateSecurePassword(int $length = 16): string {
    $length = max(16, $length);
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $charLen = strlen($chars);
    do {
        $bytes = random_bytes($length);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[ord($bytes[$i]) % $charLen];
        }
    } while (!passwordMeetsPolicy($password));
    return $password;
}
?>
