<?php

declare(strict_types=1);

final class Auth
{
    /**
     * Verify credentials and, on success, establish the session.
     * Same failure for "unknown user" and "wrong password" (FR2).
     */
    public static function attempt(string $usernameOrEmail, string $password): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, username, password_hash FROM users WHERE username = :identifier OR email = :identifier LIMIT 1'
        );
        $stmt->execute(['identifier' => $usernameOrEmail]);
        $user = $stmt->fetch();

        if ($user === false || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];

        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function username(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }
}
