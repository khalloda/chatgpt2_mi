<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Customer;
use App\Models\Note;
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
        $this->view('customers/form',['mode'=>'edit','item'=>$it,'notes'=> Note::for('customer', (int)$it['id'])]);
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
	
	private function tableExists(string $name): bool {
        $pdo = \App\Core\DB::conn();
        $st = $pdo->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1");
        $st->execute([$name]);
        return (bool)$st->fetchColumn();
    }
    private function columnExists(string $table, string $col): bool {
        $pdo = \App\Core\DB::conn();
        $st = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
        $st->execute([$table,$col]);
        return (bool)$st->fetchColumn();
    }

    public function show(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id<=0) { \App\Core\redirect('/customers'); }

        $pdo = \App\Core\DB::conn();

        // customer row
        $st = $pdo->prepare("SELECT * FROM customers WHERE id=?"); $st->execute([$id]);
        $customer = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$customer) { $this->view('errors/404',['message'=>'Customer not found']); return; }

        // invoices (sales)
        $invNoCol = $this->columnExists('invoices','inv_no') ? 'inv_no' : 'id';
        $sqlInv = "SELECT id, {$invNoCol} AS inv_no, total, paid_amount, status, created_at
                   FROM invoices WHERE customer_id=? ORDER BY id DESC LIMIT 200";
        $st = $pdo->prepare($sqlInv); $st->execute([$id]);
        $invoices = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // payments (join to invoices for this customer)
        $sqlPay = "SELECT p.id, p.paid_at, p.method, p.reference, p.amount,
                          i.id AS invoice_id, i.{$invNoCol} AS inv_no
                   FROM invoice_payments p
                   JOIN invoices i ON i.id = p.invoice_id
                   WHERE i.customer_id = ?
                   ORDER BY p.paid_at DESC, p.id DESC LIMIT 200";
        $st = $pdo->prepare($sqlPay); $st->execute([$id]);
        $payments = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // quotes
        $quotes = [];
        if ($this->tableExists('quotes')) {
            $qNoCol = $this->columnExists('quotes','q_no') ? 'q_no' : ($this->columnExists('quotes','quote_no')?'quote_no':'id');
            $totalExpr = $this->columnExists('quotes','total') ? 'q.total' : "(SELECT COALESCE(SUM(line_total),0) FROM quote_items qi WHERE qi.quote_id=q.id)";
            $sqlQ = "SELECT q.id, q.created_at, {$qNoCol} AS q_no, {$totalExpr} AS total, COALESCE(q.status,'') AS status
                     FROM quotes q WHERE q.customer_id=? ORDER BY q.id DESC LIMIT 200";
            $st = $pdo->prepare($sqlQ); $st->execute([$id]);
            $quotes = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        // orders (support 'orders' or 'sales_orders')
        $orders = [];
        $ordersTable = $this->tableExists('orders') ? 'orders' : ($this->tableExists('sales_orders') ? 'sales_orders' : null);
        if ($ordersTable) {
            $noCol = $this->columnExists($ordersTable,'so_no') ? 'so_no' :
                     ($this->columnExists($ordersTable,'order_no') ? 'order_no' : 'id');
            $totalExpr = $this->columnExists($ordersTable,'total') ? "o.total" :
                         "(SELECT COALESCE(SUM(line_total),0) FROM ".($ordersTable==='orders'?'order_items':'sales_order_items')." oi WHERE oi.".($ordersTable==='orders'?'order_id':'sales_order_id')."=o.id)";
            $custCol = $this->columnExists($ordersTable,'customer_id') ? 'customer_id' : 'client_id';
            $sqlO = "SELECT o.id, {$noCol} AS so_no, {$totalExpr} AS total,
                            COALESCE(o.status,'') AS status, o.created_at
                     FROM {$ordersTable} o
                     WHERE o.{$custCol} = ?
                     ORDER BY o.id DESC LIMIT 200";
            $st = $pdo->prepare($sqlO); $st->execute([$id]);
            $orders = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        // quick AR totals
$inv_total = (float)$pdo->query("SELECT COALESCE(SUM(i.total),0) FROM invoices i WHERE i.customer_id=".$id)->fetchColumn();
$pay_total = (float)$pdo->query("SELECT COALESCE(SUM(p.amount),0) FROM invoice_payments p JOIN invoices i ON i.id=p.invoice_id WHERE i.customer_id=".$id)->fetchColumn();
$ret_total = (float)$pdo->query("SELECT COALESCE(SUM(sr.total),0) FROM sales_returns sr JOIN invoices i ON i.id=sr.sales_invoice_id WHERE i.customer_id=".$id)->fetchColumn();
$ar_balance = max(0.0, $inv_total - $pay_total - $ret_total);

        $this->view('customers/view', [
            'customer'  => $customer,
            'quotes'    => $quotes,
            'orders'    => $orders,
            'invoices'  => $invoices,
            'payments'  => $payments,
            'ar_balance'=> $ar_balance,
            'inv_total' => $inv_total,
            'pay_total' => $pay_total,
            'ret_total' => $ret_total,
        ]);
    }

    public function statement(): void {
        require_auth();
        $id   = (int)($_GET['id'] ?? 0);
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        $pdo = \App\Core\DB::conn();
        $st = $pdo->prepare("SELECT * FROM customers WHERE id=?");
        $st->execute([$id]);
        $customer = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$customer) { $this->view('errors/404',['message'=>'Customer not found']); return; }

        // Opening AR before $from: invoices - payments - returns
        $sqlInv0 = "SELECT COALESCE(SUM(total),0) FROM invoices WHERE customer_id=? AND created_at < ?";
        $sqlPay0 = "SELECT COALESCE(SUM(p.amount),0) FROM invoice_payments p JOIN invoices i ON i.id=p.invoice_id WHERE i.customer_id=? AND p.paid_at < ?";
        $sqlRet0 = "SELECT COALESCE(SUM(sr.total),0) FROM sales_returns sr JOIN invoices i ON i.id=sr.sales_invoice_id WHERE i.customer_id=? AND sr.created_at < ?";

        $opening = (float)DB::conn()->prepare($sqlInv0)->execute([$id,$from]) ?: 0;
        $stX = DB::conn()->prepare($sqlInv0); $stX->execute([$id,$from]); $inv0 = (float)$stX->fetchColumn();
        $stX = DB::conn()->prepare($sqlPay0); $stX->execute([$id,$from]); $pay0 = (float)$stX->fetchColumn();
        $stX = DB::conn()->prepare($sqlRet0); $stX->execute([$id,$from]); $ret0 = (float)$stX->fetchColumn();
        $opening = $inv0 - $pay0 - $ret0;

        // Movements within range
        $invNoCol = $this->columnExists('invoices','inv_no') ? 'inv_no' : 'id';
        $q1 = $pdo->prepare("SELECT created_at AS txn_date, 'invoice' AS kind, {$invNoCol} AS ref_no, total AS debit, 0 AS credit, id AS ref_id
                             FROM invoices WHERE customer_id=? AND DATE(created_at) BETWEEN ? AND ?");
        $q1->execute([$id,$from,$to]); $invoices = $q1->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $q2 = $pdo->prepare("SELECT p.paid_at AS txn_date, 'payment' AS kind, p.reference AS ref_no, 0 AS debit, p.amount AS credit, p.id AS ref_id
                             FROM payments p JOIN invoices i ON i.id=p.invoice_id
                             WHERE i.customer_id=? AND DATE(p.paid_at) BETWEEN ? AND ?");
        $q2->execute([$id,$from,$to]); $payments = $q2->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $q3 = $pdo->prepare("SELECT sr.created_at AS txn_date, 'return' AS kind, sr.sr_no AS ref_no, 0 AS debit, sr.total AS credit, sr.id AS ref_id
                             FROM sales_returns sr JOIN invoices i ON i.id=sr.sales_invoice_id
                             WHERE i.customer_id=? AND DATE(sr.created_at) BETWEEN ? AND ?");
        $q3->execute([$id,$from,$to]); $returns = $q3->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $rows = array_merge($invoices,$payments,$returns);
        usort($rows, function($a,$b){
            if ($a['txn_date'] === $b['txn_date']) return $a['kind'] <=> $b['kind'];
            return strcmp($a['txn_date'],$b['txn_date']);
        });

        $running = $opening;
        foreach ($rows as &$r) { $running += (float)$r['debit'] - (float)$r['credit']; $r['running'] = $running; }
        unset($r);

        $this->view('customers/statement', [
            'customer'=>$customer, 'from'=>$from, 'to'=>$to,
            'opening'=>$opening, 'rows'=>$rows, 'closing'=>$running
        ]);
    }
}
