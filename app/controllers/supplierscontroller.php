<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Supplier;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class SuppliersController extends Controller
{
    public function index(): void {
        require_auth();
        $this->view('suppliers/index', ['items' => Supplier::all()]);
    }

    public function create(): void {
        require_auth();
        $this->view('suppliers/form', ['mode'=>'create', 'item'=>['name'=>'','phone'=>'','email'=>'','address'=>'']]);
    }

    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/suppliers'); }
        $d = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'address' => trim((string)($_POST['address'] ?? '')),
        ];
        if ($d['name'] === '') { flash_set('error','Name is required.'); redirect('/suppliers/create'); }
        $id = Supplier::create($d);
        flash_set('success','Supplier created.');
        redirect('/suppliers/edit?id='.$id);
    }

    public function edit(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $it = Supplier::find($id);
        if (!$it) { flash_set('error','Not found.'); redirect('/suppliers'); }
        $this->view('suppliers/form', ['mode'=>'edit', 'item'=>$it]);
    }

    public function update(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/suppliers'); }
        $id = (int)($_POST['id'] ?? 0);
        $it = Supplier::find($id);
        if (!$it) { flash_set('error','Not found.'); redirect('/suppliers'); }
        $d = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'address' => trim((string)($_POST['address'] ?? '')),
        ];
        if ($d['name'] === '') { flash_set('error','Name is required.'); redirect('/suppliers/edit?id='.$id); }
        Supplier::update($id, $d);
        flash_set('success','Supplier saved.');
        redirect('/suppliers/edit?id='.$id);
    }

    public function destroy(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/suppliers'); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { Supplier::delete($id); flash_set('success','Supplier deleted.'); }
        redirect('/suppliers');
    }
}
