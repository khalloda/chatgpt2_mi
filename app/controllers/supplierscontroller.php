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
        $items = \App\Models\Supplier::allWithBalance();
    $this->view('suppliers/index', ['items' => $items]);
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
	
	public function statement(): void {
        require_auth();
        $id   = (int)($_GET['id'] ?? 0);
        $from = $_GET['from'] ?? date('Y-m-01');     // default: this month start
        $to   = $_GET['to']   ?? date('Y-m-d');      // default: today

        $supplier = Supplier::find($id);
        if (!$supplier) {
            $this->view('errors/404', ['message' => 'Supplier not found']); return;
        }

        $opening   = Supplier::apOpeningBalance($id, $from);
        $rows      = Supplier::apMovements($id, $from, $to);

        // running balance
        $running = $opening;
        foreach ($rows as &$r) {
            $running += (float)$r['debit'] - (float)$r['credit'];
            $r['running'] = $running;
        }
        unset($r);

        $this->view('suppliers/statement', [
            'supplier' => $supplier,
            'from'     => $from,
            'to'       => $to,
            'opening'  => $opening,
            'rows'     => $rows,
            'closing'  => $running,
        ]);
    }
	public function show(): void {
    \App\Core\require_auth();

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) { \App\Core\redirect('/suppliers'); }

    $pdo = \App\Core\DB::conn();

    // supplier row
    $supplier = \App\Models\Supplier::find($id);
    if (!$supplier) {
        $this->view('errors/404', ['message' => 'Supplier not found']); return;
    }

    // purchase orders
    $st = $pdo->prepare("SELECT id, po_no, status, total, created_at
                         FROM purchase_orders
                         WHERE supplier_id=? ORDER BY id DESC LIMIT 200");
    $st->execute([$id]);
    $po_list = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    // receipts (delivered items) for this supplier (via PI)
    $sqlReceipts = "SELECT r.id, r.created_at, p.code AS product_code, p.name AS product_name,
                           w.name AS warehouse_name, r.qty, r.price, (r.qty*r.price) AS line_total,
                           pi.id AS purchase_invoice_id, pi.pi_no
                    FROM receipts r
                    JOIN purchase_invoices pi ON pi.id = r.purchase_invoice_id
                    JOIN products p ON p.id = r.product_id
                    JOIN warehouses w ON w.id = r.warehouse_id
                    WHERE pi.supplier_id = ?
                    ORDER BY r.id DESC LIMIT 200";
    $st = $pdo->prepare($sqlReceipts); $st->execute([$id]);
    $receipt_items = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    // purchase invoices
    $st = $pdo->prepare("SELECT id, pi_no, total, paid_amount, status, created_at
                         FROM purchase_invoices
                         WHERE supplier_id=? ORDER BY id DESC LIMIT 200");
    $st->execute([$id]);
    $pi_list = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    // supplier payments (AP)
    $st = $pdo->prepare("SELECT id, paid_at, method, reference, amount
                         FROM supplier_payments
                         WHERE supplier_id=? ORDER BY paid_at DESC, id DESC LIMIT 200");
    $st->execute([$id]);
    $spayments = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    // quick AP totals
    $inv_total = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM purchase_invoices WHERE supplier_id=".$id)->fetchColumn();
    $pay_total = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM supplier_payments WHERE supplier_id=".$id)->fetchColumn();
    $ret_total = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM purchase_returns WHERE supplier_id=".$id)->fetchColumn();
    $ap_balance = max(0.0, $inv_total - $pay_total - $ret_total);

    $this->view('suppliers/view', [
        'supplier'      => $supplier,
        'po_list'       => $po_list,
        'receipt_items' => $receipt_items,
        'pi_list'       => $pi_list,
        'spayments'     => $spayments,
        'ap_balance'    => $ap_balance,
        'inv_total'     => $inv_total,
        'pay_total'     => $pay_total,
        'ret_total'     => $ret_total,
    ]);
}
}
