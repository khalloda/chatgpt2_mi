<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;

final class HomeController extends Controller
{
    public function index(): void
    {
        // ping database to verify credentials (safe SELECT 1)
        try {
            $ok = DB::conn()->query('SELECT 1')->fetchColumn() === '1';
        } catch (\Throwable $e) {
            $ok = false;
        }

        $this->view('home/index', [
            'db_ok' => $ok,
        ]);
    }
}
