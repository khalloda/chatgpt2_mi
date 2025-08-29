<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Customer;
use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class CustomersController extends Controller
{
    public function index(): void {
        require_auth();
        $this->view('customers/index', ['items' => Customer::all()]);
    }
    public function create(): void {
        require_auth();
        $this->view('customers/form', ['mode'=>'create','item'=>null]);
    }
    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/customers'); }
        $d = $this->r();
        if ($d['name']===''){ flash_set('error','Name is required.'); redirect('/customers/create'); }
        Customer::create($d);
        flash_set('success','Customer created.');
        redirect('/customers');
    }
    public function edit(): void {
        require_auth();
        $id=(int)($_GET['id']??0);
        $it=Customer::find($id);
        if(!$it){ flash_set('error','Not found.'); redirect('/customers'); }
        $this->view('customers/form',['mode'=>'edit','item'=>$it]);
    }
    public function update(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/customers'); }
        $id=(int)($_POST['id']??0);
        $d = $this->r();
        if($id<=0){ flash_set('error','Bad id.'); redirect('/customers'); }
        Customer::update($id,$d);
        flash_set('success','Customer updated.');
        redirect('/customers');
    }
    public function destroy(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/customers'); }
        $id=(int)($_POST['id']??0);
        if($id<=0){ flash_set('error','Bad id.'); redirect('/customers'); }
        if(!Customer::delete($id)){ flash_set('error','Cannot delete: has quotes.'); }
        else { flash_set('success','Customer deleted.'); }
        redirect('/customers');
    }

    private function r(): array {
        return [
            'name'=>trim((string)($_POST['name']??'')),
            'phone'=>trim((string)($_POST['phone']??'')),
            'email'=>trim((string)($_POST['email']??'')),
            'address'=>trim((string)($_POST['address']??'')),
        ];
    }
}
