<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Note;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class PurchaseOrdersController extends Controller
{
    public function index(): void {
        require_auth();
        $this->view('purchaseorders/index', ['items' => PurchaseOrder::all()]);
    }

    public function create(): void {
        require_auth();
        $suppliers  = Supplier::all();
        $products   = DB::conn()->query("SELECT id, code, name, price FROM products ORDER BY code")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $warehouses = DB::conn()->query("SELECT id, name FROM warehouses ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $this->view('purchaseorders/form', [
            'mode' => 'create',
            'po'   => ['po_no'=>PurchaseOrder::nextNumber(), 'supplier_id'=>'', 'tax_rate'=>0, 'subtotal'=>0, 'tax_amount'=>0, 'total'=>0, 'status'=>'draft'],
            'items'=> [],
            'suppliers'=>$suppliers, 'products'=>$products, 'warehouses'=>$warehouses,
        ]);
    }

    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseorders'); }
        $pdo = DB::conn(); $pdo->beginTransaction();
        try {
            $poNo = PurchaseOrder::nextNumber();
            $supplierId = (int)($_POST['supplier_id'] ?? 0);
            $taxRate = (float)($_POST['tax_rate'] ?? 0);

            $items = $this->readItems();
            if (!$items) { throw new \RuntimeException('At least one item is required.'); }

            $subtotal = 0.0;
            foreach ($items as &$it) {
                $it['qty'] = max(1, (int)$it['qty']);
                $it['price'] = (float)$it['price'];
                $it['line_total'] = $it['qty'] * $it['price'];
                $subtotal += $it['line_total'];
            }
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $total = $subtotal + $taxAmount;

            $ins = $pdo->prepare("INSERT INTO purchase_orders (po_no, supplier_id, status, tax_rate, subtotal, tax_amount, total)
                                  VALUES (?,?,?,?,?,?,?)");
            $ins->execute([$poNo, $supplierId, 'draft', $taxRate, $subtotal, $taxAmount, $total]);
            $poId = (int)$pdo->lastInsertId();

            $insItem = $pdo->prepare("INSERT INTO purchase_order_items (purchase_order_id, product_id, warehouse_id, qty, price, line_total)
                                      VALUES (?,?,?,?,?,?)");
            foreach ($items as $it) {
                $insItem->execute([$poId, (int)$it['product_id'], (int)$it['warehouse_id'], (int)$it['qty'], (float)$it['price'], (float)$it['line_total']]);
            }

            $pdo->commit();
            flash_set('success','PO created: '.$poNo);
            redirect('/purchaseorders/show?id='.$poId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Save failed: '.$e->getMessage());
            redirect('/purchaseorders/create');
        }
    }

    public function edit(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $po = PurchaseOrder::find($id);
        if (!$po) { flash_set('error','Not found.'); redirect('/purchaseorders'); }
        if (($po['status'] ?? 'draft') !== 'draft') {
            flash_set('error','Only draft POs can be edited.');
            redirect('/purchaseorders/show?id='.$id);
        }
        $suppliers  = Supplier::all();
        $products   = DB::conn()->query("SELECT id, code, name, price FROM products ORDER BY code")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $warehouses = DB::conn()->query("SELECT id, name FROM warehouses ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $this->view('purchaseorders/form', [
            'mode'=>'edit', 'po'=>$po, 'items'=>PurchaseOrder::items($id),
            'suppliers'=>$suppliers, 'products'=>$products, 'warehouses'=>$warehouses,
        ]);
    }

    public function update(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseorders'); }
        $id = (int)($_POST['id'] ?? 0);
        $po = PurchaseOrder::find($id);
        if (!$po) { flash_set('error','Not found.'); redirect('/purchaseorders'); }
        if (($po['status'] ?? 'draft') !== 'draft') {
            flash_set('error','Only draft POs can be edited.');
            redirect('/purchaseorders/show?id='.$id);
        }

        $pdo = DB::conn(); $pdo->beginTransaction();
        try {
            $supplierId = (int)($_POST['supplier_id'] ?? 0);
            $taxRate = (float)($_POST['tax_rate'] ?? 0);
            $items = $this->readItems();
            if (!$items) { throw new \RuntimeException('At least one item is required.'); }

            $subtotal = 0.0;
            foreach ($items as &$it) {
                $it['qty'] = max(1, (int)$it['qty']);
                $it['price'] = (float)$it['price'];
                $it['line_total'] = $it['qty'] * $it['price'];
                $subtotal += $it['line_total'];
            }
            $taxAmount = round($subtotal * ($taxRate/100), 2);
            $total = $subtotal + $taxAmount;

            $pdo->prepare("UPDATE purchase_orders SET supplier_id=?, tax_rate=?, subtotal=?, tax_amount=?, total=? WHERE id=?")
                ->execute([$supplierId, $taxRate, $subtotal, $taxAmount, $total, $id]);

            $pdo->prepare("DELETE FROM purchase_order_items WHERE purchase_order_id=?")->execute([$id]);
            $insItem = $pdo->prepare("INSERT INTO purchase_order_items (purchase_order_id, product_id, warehouse_id, qty, price, line_total)
                                      VALUES (?,?,?,?,?,?)");
            foreach ($items as $it) {
                $insItem->execute([$id, (int)$it['product_id'], (int)$it['warehouse_id'], (int)$it['qty'], (float)$it['price'], (float)$it['line_total']]);
            }

            $pdo->commit();
            flash_set('success','PO updated.');
            redirect('/purchaseorders/show?id='.$id);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Update failed: '.$e->getMessage());
            redirect('/purchaseorders/edit?id='.$id);
        }
    }

    public function show(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $po = PurchaseOrder::find($id);
        if (!$po) { flash_set('error','Not found.'); redirect('/purchaseorders'); }
        $items = PurchaseOrder::items($id);
        $this->view('purchaseorders/view', [
            'po' => $po, 'items' => $items, 'notes' => Note::for('purchase_order', $id),
        ]);
    }

    public function markordered(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseorders'); }
        $id = (int)($_POST['id'] ?? 0);
        $po = PurchaseOrder::find($id);
        if (!$po) { flash_set('error','Not found.'); redirect('/purchaseorders'); }
        if (($po['status'] ?? 'draft') !== 'draft') {
            flash_set('error','Only draft POs can be marked as ordered.');
            redirect('/purchaseorders/show?id='.$id);
        }
        DB::conn()->prepare("UPDATE purchase_orders SET status='ordered' WHERE id=?")->execute([$id]);
        flash_set('success','PO marked as ordered.');
        redirect('/purchaseorders/show?id='.$id);
    }

    public function printpage(): void
    {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $includeNotes = isset($_GET['include_notes']) && $_GET['include_notes'] === '1';
        $po = PurchaseOrder::find($id);
        if (!$po) { flash_set('error','Not found.'); redirect('/purchaseorders'); }
        $items = PurchaseOrder::items($id);
        $publicNotes = $includeNotes ? Note::publicFor('purchase_order', $id) : [];
        $this->view_raw('purchaseorders/print', [
            'po'=>$po, 'items'=>$items, 'public_notes'=>$publicNotes, 'include_notes'=>$includeNotes
        ]);
    }

    private function readItems(): array {
        $rows = [];
        $pids = $_POST['item_product_id'] ?? [];
        $wids = $_POST['item_warehouse_id'] ?? [];
        $qtys = $_POST['item_qty'] ?? [];
        $prices = $_POST['item_price'] ?? [];
        $n = max(count($pids), count($wids), count($qtys), count($prices));
        for ($i=0; $i<$n; $i++) {
            $pid = (int)($pids[$i] ?? 0);
            $wid = (int)($wids[$i] ?? 0);
            $q   = (int)($qtys[$i] ?? 0);
            $pr  = (float)($prices[$i] ?? 0);
            if ($pid>0 && $wid>0 && $q>0 && $pr>=0) {
                $rows[] = ['product_id'=>$pid, 'warehouse_id'=>$wid, 'qty'=>$q, 'price'=>$pr];
            }
        }
        return $rows;
    }
}
