<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Core\Env;

final class HomeController extends Controller
{
    public function index(): void
    {
        $dbOk = false;
        $dbError = null;

        try {
            $dbOk = ((int) DB::conn()->query('SELECT 1')->fetchColumn() === 1);
        } catch (\Throwable $e) {
            $dbOk = false;
            $dbError = $e->getMessage(); // safe: no credentials
        }

        $this->view('home/index', [
            'db_ok'    => $dbOk,
            'db_error' => $dbError,
            'debug'    => Env::get('APP_DEBUG', 'false') === 'true',
        ]);
    }
}
