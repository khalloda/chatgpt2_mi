<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\Warehouse;
use App\Models\Note;
use PDO;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class WarehousesController extends Controller
{
    public function index(): void {
        require_auth();

        // Aggregate stock per warehouse for the list
        $sql = "
            SELECT w.id, w.code, w.name, w.location,
                   COALESCE(SUM(ps.qty_on_hand),0)                      AS on_hand,
                   COALESCE(SUM(ps.qty_reserved),0)                     AS reserved,
                   COALESCE(SUM(ps.qty_on_hand * ps.avg_cost),0)        AS value
            FROM warehouses w
            LEFT JOIN product_stocks ps ON ps.warehouse_id = w.id
            GROUP BY w.id
            ORDER BY w.code, w.name
        ";
        $items = DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->view('warehouses/index', ['items' => $items]);
    }

    public function show(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);

        $st = DB::conn()->prepare("SELECT * FROM warehouses WHERE id = ?");
        $st->execute([$id]);
        $warehouse = $st->fetch(PDO::FETCH_ASSOC);

        if (!$warehouse) {
            flash_set('error','Warehouse not found');
            redirect('/warehouses');
            return;
        }

        $sql = "
            SELECT p.id, p.code, p.name,
                   COALESCE(ps.qty_on_hand,0)  AS on_hand,
                   COALESCE(ps.qty_reserved,0) AS reserved,
                   COALESCE(ps.avg_cost,0)     AS avg_cost,
                   (COALESCE(ps.qty_on_hand,0) * COALESCE(ps.avg_cost,0)) AS value
            FROM products p
            LEFT JOIN product_stocks ps
              ON ps.product_id = p.id AND ps.warehouse_id = ?
            WHERE COALESCE(ps.qty_on_hand,0) <> 0 OR COALESCE(ps.qty_reserved,0) <> 0
            ORDER BY p.code
        ";
        $lines = DB::conn()->prepare($sql);
        $lines->execute([$id]);
        $rows = $lines->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->view('warehouses/show', ['warehouse'=>$warehouse, 'lines'=>$rows]);
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
        $item = Warehouse::find((int)$id);
        if (!$item) {
            flash_set('error', 'Warehouse not found');
            redirect('/warehouses');
            return;
        }

        $this->view('warehouses/form', [
            'mode'  => 'edit',
            'item'  => $item,
            'notes' => Note::for('warehouse', (int)$item['id']),
        ]);
    }

    public function update(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/warehouses'); }
        $id=(int)($_POST['id']??0);
        $code=trim((string)($_POST['code']??'')); 
        $name=trim((string)($_POST['name']??'')); 
        $loc=trim((string)($_POST['location']??''));
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
