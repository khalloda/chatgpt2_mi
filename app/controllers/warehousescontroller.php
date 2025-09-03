<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\Warehouse;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class WarehousesController extends Controller
{
    public function index(): void {
        require_auth();
        $pdo = DB::conn();
        // Include aggregated stock totals so the list can show numbers.
        $q = $pdo->query("
            SELECT
              w.id, w.code, w.name, w.location,
              COALESCE(SUM(s.qty_on_hand),0)                  AS on_hand,
              COALESCE(SUM(s.qty_reserved),0)                 AS reserved,
              COALESCE(SUM(s.qty_on_hand * s.avg_cost),0.00)  AS value
            FROM warehouses w
            LEFT JOIN product_stocks s ON s.warehouse_id = w.id
            GROUP BY w.id, w.code, w.name, w.location
            ORDER BY w.code
        ");
        $items = $q->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $this->view('warehouses/index', ['items' => $items]);
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
        $item = \App\Models\Warehouse::find((int)$id);
        if (!$item) { flash_set('error', 'Warehouse not found'); redirect('/warehouses'); return; }

        $this->view('warehouses/form', [
            'mode'  => 'edit',
            'item'  => $item,
            'notes' => \App\Models\Note::for('warehouse', (int)$item['id']),
        ]);
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

    /** Detailed stock view (avoid name 'view' to not collide with Controller::view()) */
    public function detail(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $pdo = DB::conn();

        $st = $pdo->prepare("SELECT * FROM warehouses WHERE id=?");
        $st->execute([$id]);
        $wh = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$wh) { flash_set('error','Warehouse not found.'); redirect('/warehouses'); }

        $rows = $pdo->prepare("
            SELECT
                p.id            AS product_id,
                p.code          AS code,
                p.name          AS name,
                s.qty_on_hand   AS on_hand,
                s.qty_reserved  AS reserved,
                GREATEST(s.qty_on_hand - s.qty_reserved, 0) AS available,
                s.avg_cost      AS avg_cost,
                ROUND(s.qty_on_hand * s.avg_cost, 2) AS value
            FROM product_stocks s
            JOIN products p ON p.id = s.product_id
            WHERE s.warehouse_id = ?
            ORDER BY p.code
        ");
        $rows->execute([$id]);
        $items = $rows->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $totals = ['on_hand'=>0.0,'reserved'=>0.0,'available'=>0.0,'value'=>0.0];
        foreach ($items as $r) {
            $totals['on_hand']   += (float)$r['on_hand'];
            $totals['reserved']  += (float)$r['reserved'];
            $totals['available'] += (float)$r['available'];
            $totals['value']     += (float)$r['value'];
        }

        $this->view('warehouses/view', ['wh'=>$wh,'items'=>$items,'totals'=>$totals]);
    }

    /** CSV export of the same breakdown */
    public function exportcsv(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $pdo = DB::conn();

        $st = $pdo->prepare("SELECT code, name FROM warehouses WHERE id=?");
        $st->execute([$id]);
        $wh = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$wh) { flash_set('error','Warehouse not found.'); redirect('/warehouses'); }

        $rows = $pdo->prepare("
            SELECT p.code, p.name, s.qty_on_hand, s.qty_reserved,
                   GREATEST(s.qty_on_hand - s.qty_reserved, 0) AS available,
                   s.avg_cost, ROUND(s.qty_on_hand * s.avg_cost, 2) AS value
            FROM product_stocks s
            JOIN products p ON p.id = s.product_id
            WHERE s.warehouse_id = ?
            ORDER BY p.code
        ");
        $rows->execute([$id]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=warehouse-'.$wh['code'].'-stock.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Product Code','Product Name','On hand','Reserved','Available','Avg cost','Value']);
        while ($r = $rows->fetch(\PDO::FETCH_ASSOC)) {
            fputcsv($out, [
                $r['code'], $r['name'],
                $r['qty_on_hand'], $r['qty_reserved'], $r['available'],
                $r['avg_cost'], $r['value']
            ]);
        }
        fclose($out);
        exit;
    }
}
