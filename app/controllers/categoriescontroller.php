<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Note;
use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class CategoriesController extends Controller
{
    public function index(): void
    {
        require_auth();
        $list = Category::all();
        $this->view('categories/index', ['items' => $list]);
    }

    public function create(): void
    {
        require_auth();
        $options = Category::options();
        $this->view('categories/form', ['mode' => 'create', 'options' => $options, 'item' => null]);
    }

    public function store(): void
    {
        require_auth();
        if (!verify_csrf_post()) {
            flash_set('error', 'Invalid session token.');
            redirect('/categories');
        }

        $parentId = $_POST['parent_id'] === '' ? null : (int)$_POST['parent_id'];
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));

        if ($name === '' || $slug === '') {
            flash_set('error', 'Name and slug are required.');
            redirect('/categories/create');
        }

        try {
            Category::create($parentId, $name, $slug);
            flash_set('success', 'Category created.');
            redirect('/categories');
        } catch (\Throwable $e) {
            flash_set('error', 'Error: ' . $e->getMessage());
            redirect('/categories/create');
        }
    }

    public function edit(): void
    {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $item = Category::find($id);
        if (!$item) {
            flash_set('error', 'Category not found.');
            redirect('/categories');
        }
        $options = Category::options($id);
        $this->view('categories/form', [
    'mode'   => 'edit',
    'options'=> $options,
    'item'   => $item,
    'notes'  => \App\Models\Note::for('category', (int)$item['id']),
]);
    }

    public function update(): void
    {
        require_auth();
        if (!verify_csrf_post()) {
            flash_set('error', 'Invalid session token.');
            redirect('/categories');
        }

        $id = (int)($_POST['id'] ?? 0);
        $parentId = $_POST['parent_id'] === '' ? null : (int)$_POST['parent_id'];
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));

        if ($id <= 0 || $name === '' || $slug === '') {
            flash_set('error', 'Invalid form data.');
            redirect('/categories');
        }

        try {
            Category::update($id, $parentId, $name, $slug);
            flash_set('success', 'Category updated.');
            redirect('/categories');
        } catch (\Throwable $e) {
            flash_set('error', 'Error: ' . $e->getMessage());
            redirect('/categories/edit?id=' . $id);
        }
    }

    public function destroy(): void
    {
        require_auth();
        if (!verify_csrf_post()) {
            flash_set('error', 'Invalid session token.');
            redirect('/categories');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash_set('error', 'Invalid category id.');
            redirect('/categories');
        }

        if (!Category::delete($id)) {
            flash_set('error', 'Cannot delete: category has sub-categories.');
            redirect('/categories');
        }

        flash_set('success', 'Category deleted.');
        redirect('/categories');
    }
}
