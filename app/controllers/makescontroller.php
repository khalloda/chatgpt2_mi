<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Make;
use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class MakesController extends Controller
{
    public function index(): void
    {
        require_auth();
        $items = Make::all();
        $this->view('makes/index', ['items' => $items]);
    }

    public function create(): void
    {
        require_auth();
        $this->view('makes/form', ['mode' => 'create', 'item' => null]);
    }

    public function store(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error', 'Invalid session.'); redirect('/makes'); }
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($name === '' || $slug === '') { flash_set('error', 'Name and slug are required.'); redirect('/makes/create'); }

        try { Make::create($name, $slug); flash_set('success', 'Make created.'); }
        catch (\Throwable $e) { flash_set('error', 'Error: ' . $e->getMessage()); }
        redirect('/makes');
    }

    public function edit(): void
    {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $item = Make::find($id);
        if (!$item) { flash_set('error', 'Make not found.'); redirect('/makes'); }
        $this->view('makes/form', ['mode' => 'edit', 'item' => $item]);
    }

    public function update(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error', 'Invalid session.'); redirect('/makes'); }
        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($id <= 0 || $name === '' || $slug === '') { flash_set('error', 'Invalid form data.'); redirect('/makes'); }

        try { Make::update($id, $name, $slug); flash_set('success', 'Make updated.'); }
        catch (\Throwable $e) { flash_set('error', 'Error: ' . $e->getMessage()); redirect('/makes/edit?id='.(int)$id); }
        redirect('/makes');
    }

    public function destroy(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error', 'Invalid session.'); redirect('/makes'); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { flash_set('error', 'Invalid id.'); redirect('/makes'); }

        if (!Make::delete($id)) { flash_set('error', 'Cannot delete: there are models under this make.'); }
        else { flash_set('success', 'Make deleted.'); }
        redirect('/makes');
    }
}
