<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\Note;
use PDO;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class InvoicesController extends Controller
{
    public function index(): void {
        require_auth();
        $this->view('invoices/index', ['items'=>Invoice::all()]);
    }

    public function show(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $inv = Invoice::find($id);
        if (!$inv) { flash_set('error','Invoice not found.'); redirect('/invoices'); }

        // Items (with adaptive table detection)
        try { $items = Invoice::items($id); }
        catch (\Throwable $e) { flash_set('error','Items table not found: '.$e->getMessage()); $items = []; }

        // --- Payments (RESTORED) ---
        $pdo = DB::conn();
        $pay = $pdo->prepare("
            SELECT id, amount, method, reference, paid_at, created_at
              FROM invoice_payments
             WHERE invoice_id = ?
             ORDER BY COALESCE(paid_at, created_at) DESC, id DESC
        ");
        try { $pay->execute([$id]); $payments = $pay->fetchAll(PDO::FETCH_ASSOC) ?: []; }
        catch (\Throwable $e) { $payments = []; } // table may not exist in some dumps

        $paid = 0.0; foreach ($payments as $p) { $paid += (float)$p['amount']; }
        $due  = max(0, (float)$inv['total'] - $paid);

        $includeNotes = isset($_GET['include_notes']) && $_GET['include_notes'] === '1';
        $publicNotes  = $includeNotes ? Note::publicFor('invoice',$id) : [];

        $this->view('invoices/view', [
            'i'            => $inv,
            'items'        => $items,
            'notes'        => Note::for('invoice',$id),
            'payments'     => $payments,   // <- pass to view
            'paid'         => $paid,
            'due'          => $due,
            'public_notes' => $publicNotes,
            'include_notes'=> $includeNotes,
        ]);
    }

    /** Manual create (optional) */
    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/invoices'); }

        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            $items = $this->readItems();
            if (!$items) { throw new \RuntimeException('At least one item is required.'); }

            $customerId = (int)($_POST['customer_id'] ?? 0);
            $taxRate    = (float)($_POST['tax_rate'] ?? 0);

            $subtotal=0.0;
            foreach ($items as &$it){
                $it['qty']   = max(1,(int)$it['qty']);
                $it['price'] = (float)$it['price'];
                $it['line_total'] = $it['qty'] * $it['price'];
                $subtotal += $it['line_total'];
            } unset($it);
            $taxAmount = round($subtotal*($taxRate/100),2);
            $total     = $subtotal + $taxAmount;

            $invNo = \App\Services\DocNumbers::next('inv');

            $pdo->prepare("INSERT INTO invoices (inv_no, customer_id, status, tax_rate, subtotal, tax_amount, total, created_at)
                           VALUES (?,?,?,?,?,?,?, NOW())")
                ->execute([$invNo,$customerId,'draft',$taxRate,$subtotal,$taxAmount,$total]);
            $invId = (int)$pdo->lastInsertId();

            // Insert lines adaptively
            [$tbl, $hasWh, $hasLineTotal] = $this->detectInvoiceItemsMeta($pdo);
            $sql = $this->buildItemsInsertSQL($tbl, $hasWh, $hasLineTotal);
            $ins = $pdo->prepare($sql);

            foreach ($items as $row){
                $params = [$invId, (int)$row['product_id']];
                if ($hasWh)       { $params[] = (int)$row['warehouse_id']; }
                $params[] = (int)$row['qty'];
                $params[] = (float)$row['price'];
                if ($hasLineTotal){ $params[] = (float)$row['line_total']; }
                $ins->execute($params);
            }

            $pdo->commit();
            flash_set('success','Invoice created: '.$invNo);
            redirect('/invoices/show?id='.$invId);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            flash_set('error','Save failed: '.$e->getMessage());
            redirect('/invoices');
        }
    }

    /** SO → Invoice */
    public function createfromso(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/orders'); }

        $soId = 0;
        foreach (['sales_order_id','order_id','so_id','id'] as $k) {
            if (isset($_POST[$k])) { $soId = (int)$_POST[$k]; break; }
        }
        if ($soId <= 0) { $soId = (int)($_GET['id'] ?? $_GET['so_id'] ?? $_GET['order_id'] ?? 0); }
        if ($soId <= 0) { flash_set('error','Missing sales order id.'); redirect('/orders'); }

        $so = SalesOrder::find($soId);
        if (!$so) { flash_set('error','Sales order not found.'); redirect('/orders'); }

        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            $base = $this->swapPrefix((string)$so['so_no'],'SO','INV');
            $invNo = $this->uniqueNo($base, 'invoices', 'inv_no', $pdo);

            $pdo->prepare("INSERT INTO invoices (sales_order_id, inv_no, customer_id, status, tax_rate, subtotal, tax_amount, total, created_at)
                           VALUES (?,?,?,?,?,?,?, ?, NOW())")
                ->execute([
                    (int)$soId, $invNo, (int)$so['customer_id'], 'draft',
                    (float)$so['tax_rate'], (float)$so['subtotal'], (float)$so['tax_amount'], (float)$so['total']
                ]);
            $invId = (int)$pdo->lastInsertId();

            [$tbl, $hasWh, $hasLineTotal] = $this->detectInvoiceItemsMeta($pdo);
            $sql = $this->buildItemsInsertSQL($tbl, $hasWh, $hasLineTotal);
            $ins = $pdo->prepare($sql);

            $copy = $pdo->prepare("SELECT product_id, warehouse_id, qty, price, (qty*price) AS lt FROM sales_order_items WHERE sales_order_id=?");
            $copy->execute([$soId]);
            while ($r = $copy->fetch(PDO::FETCH_ASSOC)) {
                $params = [$invId, (int)$r['product_id']];
                if ($hasWh)       { $params[] = (int)$r['warehouse_id']; }
                $params[] = (int)$r['qty'];
                $params[] = (float)$r['price'];
                if ($hasLineTotal){ $params[] = (float)($r['lt'] ?? ((float)$r['qty'] * (float)$r['price'])); }
                $ins->execute($params);
            }

            $pdo->commit();
            flash_set('success','Invoice created: '.$invNo);
            redirect('/invoices/show?id='.$invId);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            flash_set('error','Create invoice failed: '.$e->getMessage());
            redirect('/orders/show?id='.$soId);
        }
    }

    /** Router alias used by your app */
    public function createfromorder(): void { $this->createfromso(); }

    /** --- NEW: add a payment to an invoice --- */
    public function addpayment(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/invoices'); }

        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $amount    = (float)($_POST['amount'] ?? 0);
        $method    = trim((string)($_POST['method'] ?? ''));
        $reference = trim((string)($_POST['reference'] ?? ''));
        $paidAt    = trim((string)($_POST['paid_at'] ?? '')) ?: null;

        if ($invoiceId <= 0 || $amount <= 0) {
            flash_set('error','Amount must be greater than zero.');
            redirect('/invoices/show?id='.$invoiceId);
        }

        $pdo = DB::conn();
        try {
            $pdo->prepare("INSERT INTO invoice_payments (invoice_id, amount, method, reference, paid_at, created_at)
                           VALUES (?,?,?,?,?, NOW())")
                ->execute([$invoiceId, $amount, ($method ?: null), ($reference ?: null), $paidAt]);

            // Recompute paid/partial/paid status
            Invoice::recomputePaidAndStatus($invoiceId);

            flash_set('success','Payment recorded.');
        } catch (\Throwable $e) {
            flash_set('error','Add payment failed: '.$e->getMessage());
        }
        redirect('/invoices/show?id='.$invoiceId);
    }

    /** --- NEW: delete a payment --- */
    public function deletepayment(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/invoices'); }
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $paymentId = (int)($_POST['payment_id'] ?? 0);
        if ($invoiceId<=0 || $paymentId<=0) { redirect('/invoices'); }

        $pdo = DB::conn();
        try {
            $pdo->prepare("DELETE FROM invoice_payments WHERE id=? AND invoice_id=?")->execute([$paymentId,$invoiceId]);
            Invoice::recomputePaidAndStatus($invoiceId);
            flash_set('success','Payment deleted.');
        } catch (\Throwable $e) {
            flash_set('error','Delete payment failed: '.$e->getMessage());
        }
        redirect('/invoices/show?id='.$invoiceId);
    }

    public function printpage(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $includeNotes = isset($_GET['include_notes']) && $_GET['include_notes'] === '1';
        $inv = Invoice::find($id);
        if (!$inv) { flash_set('error','Invoice not found.'); redirect('/invoices'); }
        $items = [];
        try { $items = Invoice::items($id); } catch (\Throwable $e) {}
        $publicNotes = $includeNotes ? Note::publicFor('invoice',$id) : [];
        $this->view_raw('invoices/print', ['i'=>$inv,'items'=>$items,'public_notes'=>$publicNotes,'include_notes'=>$includeNotes]);
    }

    /* ---------- helpers (same as before) ---------- */

    private function readItems(): array {
        $rows=[]; $pids=$_POST['product_id']??[]; $wids=$_POST['warehouse_id']??[]; $qtys=$_POST['qty']??[]; $prices=$_POST['price']??[];
        $n=max(count($pids),count($wids),count($qtys),count($prices));
        for($i=0;$i<$n;$i++){
            $pid=(int)($pids[$i]??0); $wid=(int)($wids[$i]??0); $q=(int)($qtys[$i]??0); $pr=(float)($prices[$i]??0);
            if($pid>0 && $q>0 && $pr>=0){
                $row=['product_id'=>$pid,'qty'=>$q,'price'=>$pr];
                if ($wid>0) { $row['warehouse_id']=$wid; }
                $rows[]=$row;
            }
        }
        return $rows;
    }

    private function detectInvoiceItemsMeta(PDO $pdo): array {
        try {
            $ref = new \ReflectionClass(Invoice::class);
            $m = $ref->getMethod('detectItemsMeta');
            $m->setAccessible(true);
            return $m->invoke(null, $pdo);
        } catch (\Throwable $e) {
            $candidates = ['invoice_lines','invoice_items','sales_invoice_items','invoices_items'];
            $in = implode(',', array_fill(0, count($candidates), '?'));
            $st = $pdo->prepare("SELECT table_name FROM information_schema.tables
                                  WHERE table_schema = DATABASE() AND table_name IN ($in) LIMIT 1");
            $st->execute($candidates);
            $tbl = (string)$st->fetchColumn();
            if ($tbl === '') { throw new \RuntimeException('No invoice items table found.'); }
            $hasWh  = $this->tableHasColumn($pdo,$tbl,'warehouse_id');
            $hasLt  = $this->tableHasColumn($pdo,$tbl,'line_total');
            return [$tbl,$hasWh,$hasLt];
        }
    }
    private function buildItemsInsertSQL(string $tbl, bool $hasWh, bool $hasLineTotal): string {
        if ($hasWh && $hasLineTotal)
            return "INSERT INTO {$tbl} (invoice_id, product_id, warehouse_id, qty, price, line_total) VALUES (?,?,?,?,?,?)";
        if ($hasWh && !$hasLineTotal)
            return "INSERT INTO {$tbl} (invoice_id, product_id, warehouse_id, qty, price) VALUES (?,?,?,?,?)";
        if (!$hasWh && $hasLineTotal)
            return "INSERT INTO {$tbl} (invoice_id, product_id, qty, price, line_total) VALUES (?,?,?,?,?)";
        return "INSERT INTO {$tbl} (invoice_id, product_id, qty, price) VALUES (?,?,?,?)";
    }
    private function tableHasColumn(PDO $pdo, string $table, string $col): bool {
        $st = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
        $st->execute([$table, $col]);
        return (bool)$st->fetchColumn();
    }
    private function swapPrefix(string $src, string $from, string $to): string {
        if (preg_match('/^'.preg_quote($from,'/').'(?P<yr>\d{4})-(?P<seq>\d{4})$/i',$src,$m))
            return sprintf('%s%s-%s',$to,$m['yr'],$m['seq']);
        if (stripos($src,$from)===0) return $to.substr($src, strlen($from));
        return $to.$src;
    }
    private function uniqueNo(string $base, string $table, string $col, PDO $pdo): string {
        $chk=$pdo->prepare("SELECT 1 FROM {$table} WHERE {$col}=? LIMIT 1");
        $chk->execute([$base]); if(!$chk->fetchColumn()) return $base;
        foreach(range('A','Z') as $ch){ $try=$base.'-'.$ch; $chk->execute([$try]); if(!$chk->fetchColumn()) return $try; }
        for($i=2;$i<100;$i++){ $try=$base.'-'.$i; $chk->execute([$try]); if(!$chk->fetchColumn()) return $try; }
        return $base.'-'.date('His');
    }
}
