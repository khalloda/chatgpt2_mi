<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\VehicleModel;
use App\Models\Make;
use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class ModelsController extends Controller
{
    public function index(): void
    {
        require_auth();
        $makeId = isset($_GET['make_id']) ? (int)$_GET['make_id'] : null;
        $items = VehicleModel::all($makeId);
        $makes = Make::options();
        $this->view('models/index', ['items' => $items, 'makes' => $makes, 'selected_make' => $makeId]);
    }

    public function create(): void
    {
        require_auth();
        $makes = Make::options();
        $this->view('models/form', ['mode' => 'create', 'item' => null, 'makes' => $makes]);
    }

    public function store(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error', 'Invalid session.'); redirect('/models'); }

        $makeId = (int)($_POST['make_id'] ?? 0);
        $name   = trim((string)($_POST['name'] ?? ''));
        $slug   = trim((string)($_POST['slug'] ?? ''));

        if ($makeId <= 0 || $name === '' || $slug === '') {
            flash_set('error', 'All fields are required.');
            redirect('/models/create');
        }

        try { VehicleModel::create($makeId, $name, $slug); flash_set('success', 'Model created.'); }
        catch (\Throwable $e) { flash_set('error', 'Error: ' . $e->getMessage()); redirect('/models/create'); }

        redirect('/models?make_id=' . $makeId);
    }

    public function edit(): void
    {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $item = VehicleModel::find($id);
        if (!$item) { flash_set('error', 'Model not found.'); redirect('/models'); }
        $makes = Make::options();
        $this->view('models/form', ['mode' => 'edit', 'item' => $item, 'makes' => $makes]);
    }

    public function update(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error', 'Invalid session.'); redirect('/models'); }

        $id     = (int)($_POST['id'] ?? 0);
        $makeId = (int)($_POST['make_id'] ?? 0);
        $name   = trim((string)($_POST['name'] ?? ''));
        $slug   = trim((string)($_POST['slug'] ?? ''));

        if ($id <= 0 || $makeId <= 0 || $name === '' || $slug === '') {
            flash_set('error', 'Invalid form data.');
            redirect('/models');
        }

        try { VehicleModel::update($id, $makeId, $name, $slug); flash_set('success', 'Model updated.'); }
        catch (\Throwable $e) { flash_set('error', 'Error: ' . $e->getMessage()); redirect('/models/edit?id='.(int)$id); }

        redirect('/models?make_id=' . $makeId);
    }

    public function destroy(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error', 'Invalid session.'); redirect('/models'); }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { flash_set('error', 'Invalid id.'); redirect('/models'); }

        // fetch to retain filter context (optional)
        $item = VehicleModel::find($id);
        $ok = VehicleModel::delete($id);
        if ($ok) flash_set('success', 'Model deleted.'); else flash_set('error', 'Delete failed.');
        $redirMake = $item['make_id'] ?? null;
        redirect('/models' . ($redirMake ? '?make_id=' . (int)$redirMake : ''));
    }
}
