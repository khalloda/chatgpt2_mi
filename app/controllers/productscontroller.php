<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Make;
use App\Models\VehicleModel;
use App\Models\Warehouse;
use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class ProductsController extends Controller
{
    public function index(): void
    {
        require_auth();
        $q    = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
        $cat  = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $make = isset($_GET['make_id']) ? (int)$_GET['make_id'] : null;
        $model= isset($_GET['model_id']) ? (int)$_GET['model_id'] : null;

        $items = Product::all($q ?: null, $cat ?: null, $make ?: null, $model ?: null);
        $this->view('products/index', [
            'items' => $items,
            'q' => $q, 'category_id'=>$cat, 'make_id'=>$make, 'model_id'=>$model,
            'categories' => Category::all(),
            'makes' => Make::options(),
            'models' => VehicleModel::all($make ?: null)
        ]);
    }

    public function create(): void
    {
        require_auth();
        $this->view('products/form', [
            'mode'=>'create',
            'item'=>['code'=>Product::nextCode()],
            'categories'=>Category::all(),
            'makes'=>Make::options(),
            'models'=>VehicleModel::all()
        ]);
    }

    public function store(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/products'); }

        $data = $this->readForm();
        if ($data['name']==='' || $data['code']==='') { flash_set('error','Code and name are required.'); redirect('/products/create'); }

        try {
            $id = Product::create($data);
            flash_set('success','Product created.');
            redirect('/products/stock?id='.$id);
        } catch (\Throwable $e) {
            flash_set('error','Error: '.$e->getMessage());
            redirect('/products/create');
        }
    }

    public function edit(): void
    {
        require_auth();
        $id=(int)($_GET['id']??0);
        $item = Product::find($id);
        if(!$item){ flash_set('error','Product not found.'); redirect('/products'); }
        $this->view('products/form', [
            'mode'=>'edit','item'=>$item,
            'categories'=>Category::all(),
            'makes'=>Make::options(),
            'models'=>VehicleModel::all($item['make_id'] ? (int)$item['make_id'] : null)
        ]);
    }

    public function update(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/products'); }
        $id=(int)($_POST['id']??0);
        $data = $this->readForm();
        if($id<=0){ flash_set('error','Invalid id.'); redirect('/products'); }

        try { Product::update($id,$data); flash_set('success','Product updated.'); }
        catch(\Throwable $e){ flash_set('error','Error: '.$e->getMessage()); redirect('/products/edit?id='.$id); }

        redirect('/products');
    }

    public function destroy(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/products'); }
        $id=(int)($_POST['id']??0);
        if($id<=0){ flash_set('error','Invalid id.'); redirect('/products'); }
        try { Product::delete($id); flash_set('success','Product deleted.'); }
        catch(\Throwable $e){ flash_set('error','Error: '.$e->getMessage()); }
        redirect('/products');
    }

    public function stock(): void
    {
        require_auth();
        $id=(int)($_GET['id']??0);
        $item = Product::find($id);
        if(!$item){ flash_set('error','Product not found.'); redirect('/products'); }
        $rows = Product::stocks($id);
        $this->view('products/stock', ['item'=>$item,'rows'=>$rows]);
    }

    public function savestock(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/products'); }
        $id=(int)($_POST['id']??0);
        if($id<=0){ flash_set('error','Invalid product id.'); redirect('/products'); }

        // posted arrays: qty_on[wid], qty_res[wid]
        $rows = [];
        foreach (($_POST['qty_on'] ?? []) as $wid => $on) {
            $rows[(int)$wid]['on'] = (int)$on;
        }
        foreach (($_POST['qty_res'] ?? []) as $wid => $res) {
            $rows[(int)$wid]['res'] = (int)$res;
        }
        Product::saveStocks($id, $rows);
        flash_set('success','Stock updated.');
        redirect('/products/stock?id='.$id);
    }

    private function readForm(): array
    {
        return [
            'code' => trim((string)($_POST['code'] ?? '')),
            'name' => trim((string)($_POST['name'] ?? '')),
            'category_id' => $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null,
            'make_id' => $_POST['make_id'] !== '' ? (int)$_POST['make_id'] : null,
            'model_id' => $_POST['model_id'] !== '' ? (int)$_POST['model_id'] : null,
            'cost' => (float)($_POST['cost'] ?? 0),
            'price' => (float)($_POST['price'] ?? 0),
        ];
    }
}
