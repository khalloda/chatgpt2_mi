<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Warehouse;
use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class WarehousesController extends Controller
{
    public function index(): void {
        require_auth();
        $this->view('warehouses/index', ['items' => Warehouse::all()]);
    }
    public function create(): void {
        require_auth();
        $this->view('warehouses/form', ['mode'=>'create','item'=>null]);
    }
    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/warehouses'); }
        $code = trim((string)($_POST['code'] ?? ''));
        $name = trim((string)($_POST['name'] ?? ''));
        $loc  = trim((string)($_POST['location'] ?? ''));
        if ($code===''||$name===''){ flash_set('error','Code and name are required.'); redirect('/warehouses/create'); }
        try { Warehouse::create($code,$name,$loc ?: null); flash_set('success','Warehouse created.'); }
        catch(\Throwable $e){ flash_set('error','Error: '.$e->getMessage()); }
        redirect('/warehouses');
    }
    public function edit(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $item = Warehouse::find($id);
        if(!$item){ flash_set('error','Not found.'); redirect('/warehouses'); }
        $this->view('warehouses/form',['mode'=>'edit','item'=>$item]);
    }
    public function update(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/warehouses'); }
        $id=(int)($_POST['id']??0);
        $code=trim((string)($_POST['code']??'')); $name=trim((string)($_POST['name']??'')); $loc=trim((string)($_POST['location']??''));
        if($id<=0||$code===''||$name===''){ flash_set('error','Invalid form data.'); redirect('/warehouses'); }
        try { Warehouse::update($id,$code,$name,$loc?:null); flash_set('success','Warehouse updated.'); }
        catch(\Throwable $e){ flash_set('error','Error: '.$e->getMessage()); redirect('/warehouses/edit?id='.$id); }
        redirect('/warehouses');
    }
    public function destroy(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/warehouses'); }
        $id=(int)($_POST['id']??0);
        if($id<=0){ flash_set('error','Invalid id.'); redirect('/warehouses'); }
        if(!Warehouse::delete($id)){ flash_set('error','Cannot delete: stock exists.'); }
        else { flash_set('success','Warehouse deleted.'); }
        redirect('/warehouses');
    }
}
