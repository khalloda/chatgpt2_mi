<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class UserController extends Controller
{
    public function profile(): void
    {
        require_auth();
        $this->view('user/profile', []);
    }

    public function changepassword(): void
    {
        require_auth();

        if (!verify_csrf_post()) {
            http_response_code(419);
            flash_set('error', 'Invalid session token. Please try again.');
            redirect('/profile');
        }

        $current = (string)($_POST['current_password'] ?? '');
        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['new_password_confirm'] ?? '');

        if ($new === '' || $confirm === '' || $current === '') {
            flash_set('error', 'All fields are required.');
            redirect('/profile');
        }
        if ($new !== $confirm) {
            flash_set('error', 'New passwords do not match.');
            redirect('/profile');
        }
        if (strlen($new) < 8) {
            flash_set('error', 'New password must be at least 8 characters.');
            redirect('/profile');
        }

        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $stmt = DB::conn()->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($current, $row['password_hash'])) {
            flash_set('error', 'Current password is incorrect.');
            redirect('/profile');
        }

        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $upd = DB::conn()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $upd->execute([$newHash, $uid]);

        flash_set('success', 'Password updated successfully.');
        redirect('/profile');
    }
}
