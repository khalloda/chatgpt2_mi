<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use function App\Core\verify_csrf_post;
use function App\Core\csrf_token;
use App\Core\Logger;

final class AuthController extends Controller
{
    public function loginform(): void
    {
        // just show the form
        $this->view('auth/login', []);
    }

    public function login(): void
    {
        if (!verify_csrf_post()) {
            http_response_code(419);
            $this->view('auth/login', ['error' => 'Invalid session token. Please try again.']);
            return;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $pass  = (string)($_POST['password'] ?? '');

        if ($email === '' || $pass === '') {
            $this->view('auth/login', ['error' => 'Email and password are required.']);
            return;
        }

        try {
            $stmt = DB::conn()->prepare('SELECT id, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($pass, $user['password_hash'])) {
                Logger::info('Login failed', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
                $this->view('auth/login', ['error' => 'Invalid credentials.']);
                return;
            }

            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'    => (int)$user['id'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];
            Logger::info('Login success', ['user_id' => (int)$user['id'], 'email' => $user['email']]);

            header('Location: /');
            exit;
        } catch (\Throwable $e) {
            Logger::error('Login exception', ['err' => $e->getMessage()]);
            $this->view('auth/login', ['error' => 'Server error, please try again.']);
        }
    }

    public function logout(): void
    {
        if (!verify_csrf_post()) {
            http_response_code(419);
            $this->view('auth/login', ['error' => 'Invalid session token.']);
            return;
        }
        $uid = $_SESSION['user']['id'] ?? null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
        if ($uid) { Logger::info('Logout', ['user_id' => $uid]); }
        header('Location: /login');
        exit;
    }
}
