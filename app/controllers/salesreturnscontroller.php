<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\SalesReturn;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class SalesReturnsController extends Controller
{
    /** Create a credit note (sales return) from an invoice */
    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/invoices'); }

        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        if ($invoiceId <= 0) { flash_set('error','Missing invoice.'); redirect('/invoices'); }

        $pdo = DB::conn(); $pdo->beginTransaction();
        try {
            // 1) Issued per (product, warehouse) from invoice items
            $sqlIssued = "SELECT product_id, warehouse_id, SUM(qty) AS qty, MAX(price) AS price
                          FROM invoice_items WHERE invoice_id=? GROUP BY product_id, warehouse_id";
            $st = $pdo->prepare($sqlIssued); $st->execute([$invoiceId]);
            $issuedMap = []; $priceMap = [];
            foreach ($st->fetchAll(\PDO::FETCH_ASSOC) ?: [] as $r) {
                $k = ((int)$r['product_id']).':'.((int)$r['warehouse_id']);
                $issuedMap[$k] = (int)$r['qty'];
                $priceMap[$k]  = (float)$r['price'];
            }

            // 2) Returned so far for that invoice
            $returnedMap = SalesReturn::returnedMapByInvoice($invoiceId);

            // 3) Read posted arrays (compatible with your invoices/view.php)
            $pids = $_POST['ret_product_id'] ?? ($_POST['product_id'] ?? []);
            $wids = $_POST['ret_warehouse_id'] ?? ($_POST['warehouse_id'] ?? []);
            $qtys = $_POST['ret_qty'] ?? ($_POST['qty'] ?? []);
            $prs  = $_POST['ret_price'] ?? ($_POST['price'] ?? []);

            $lines = [];
            for ($i=0,$n=max(count($pids),count($wids),count($qtys)); $i<$n; $i++) {
                $pid = (int)($pids[$i] ?? 0);
                $wid = (int)($wids[$i] ?? 0);
                $qty = (int)($qtys[$i] ?? 0);
                if ($pid<=0 || $wid<=0 || $qty<=0) continue;

                $k   = $pid.':'.$wid;
                $iss = (int)($issuedMap[$k] ?? 0);
                $ret = (int)($returnedMap[$k] ?? 0);
                $rem = max(0, $iss - $ret);
                if ($rem <= 0) continue;
                if ($qty > $rem) $qty = $rem;

                $price = isset($prs[$i]) && $prs[$i] !== '' ? (float)$prs[$i] : (float)($priceMap[$k] ?? 0);
                $lines[] = ['product_id'=>$pid,'warehouse_id'=>$wid,'qty'=>$qty,'price'=>$price];
            }

            if (!$lines) {
                $pdo->rollBack();
                flash_set('info','Nothing to return.');
                redirect('/invoices/show?id='.$invoiceId);
            }

            // 4) Create header number
            $srNo = SalesReturn::nextNumber();
            $pdo->prepare("INSERT INTO sales_returns (sr_no, sales_invoice_id, total) VALUES (?,?,0.00)")
                ->execute([$srNo, $invoiceId]);
            $srId = (int)$pdo->lastInsertId();

            // 5) Insert items, increase on-hand, ledger, simple COGS reversal
            $insItem = $pdo->prepare("INSERT INTO sales_return_items (sales_return_id, product_id, warehouse_id, qty, price, line_total)
                                      VALUES (?,?,?,?,?,?)");
            $stSel = $pdo->prepare("SELECT qty_on_hand, avg_cost FROM product_stocks WHERE product_id=? AND warehouse_id=? FOR UPDATE");
            $stUpd = $pdo->prepare("UPDATE product_stocks SET qty_on_hand = qty_on_hand + ? WHERE product_id=? AND warehouse_id=?");
            $stIns = $pdo->prepare("INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, qty_reserved, avg_cost) VALUES (?,?,?,?,?)");
            $insLed= $pdo->prepare("INSERT INTO inventory_ledger (product_id, warehouse_id, doc_type, doc_id, qty_delta, unit_cost, value_delta)
                                    VALUES (?,?,?,?,?,?,?)");
            $insCogs = $pdo->prepare("INSERT INTO cogs_entries (invoice_id, product_id, warehouse_id, qty, unit_cost, line_cost)
                                      VALUES (?,?,?,?,?,?)");

            $subtotal = 0.0; $cogsReversal = 0.0;

            foreach ($lines as $ln) {
                $pid   = (int)$ln['product_id'];
                $wid   = (int)$ln['warehouse_id'];
                $qty   = (int)$ln['qty'];
                $price = (float)$ln['price'];
                $line  = $qty * $price;

                // stock row
                $stSel->execute([$pid,$wid]);
                $row = $stSel->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    $avg = (float)$row['avg_cost'];
                    $stUpd->execute([$qty,$pid,$wid]);
                } else {
                    $avg = 0.0;
                    $stIns->execute([$pid,$wid,$qty,0,0.0]);
                }

                // ledger at current avg
                $insLed->execute([$pid,$wid,'sales_return',$srId, +$qty, $avg, +$qty*$avg]);

                // cogs reversal at current avg (negative qty/value in cogs_entries)
                $insCogs->execute([$invoiceId,$pid,$wid, -$qty, $avg, -$qty*$avg]);
                $cogsReversal += (-$qty * $avg); // negative value to add to cogs_total

                // item
                $insItem->execute([$srId,$pid,$wid,$qty,$price,$line]);
                $subtotal += $line;
            }

            // 6) Update SR total (no-tax in table) and adjust invoice cogs_total down
            $pdo->prepare("UPDATE sales_returns SET total=? WHERE id=?")->execute([$subtotal, $srId]);
            if ($cogsReversal !== 0.0) {
                $pdo->prepare("UPDATE invoices SET cogs_total = GREATEST(cogs_total + ?, 0.00) WHERE id=?")
                    ->execute([$cogsReversal, $invoiceId]);
            }

            $pdo->commit();
            flash_set('success', "Credit Note {$srNo} created.");
            redirect('/invoices/show?id='.$invoiceId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Create credit note failed: '.$e->getMessage());
            redirect('/invoices/show?id='.$invoiceId);
        }
    }

    /** Minimal SR view (list lines + link to print) */
    public function show(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id<=0) { redirect('/invoices'); }
        $pdo = DB::conn();

        $st = $pdo->prepare("SELECT sr.*, i.inv_no
                               FROM sales_returns sr
                               JOIN invoices i ON i.id = sr.sales_invoice_id
                              WHERE sr.id=?");
        $st->execute([$id]);
        $sr = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$sr) { $this->view('errors/404',['message'=>'Credit note not found']); return; }

        $this->view('salesreturns/view', [
            'sr'    => $sr,
            'items' => SalesReturn::items((int)$sr['id']),
        ]);
    }

    /** Print credit note (compute subtotal/tax for your print.php) */
    public function printpage(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id<=0) { redirect('/invoices'); }
        $pdo = DB::conn();

        $st = $pdo->prepare("SELECT sr.*, i.inv_no, i.id AS invoice_id, i.tax_rate
                               FROM sales_returns sr
                               JOIN invoices i ON i.id = sr.sales_invoice_id
                              WHERE sr.id=?");
        $st->execute([$id]);
        $sr = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$sr) { $this->view('errors/404',['message'=>'Credit note not found']); return; }

        // invoice + client name (alias as client_name to match your print.php)
        $st = $pdo->prepare("SELECT i.*, c.name AS client_name
                               FROM invoices i
                               JOIN customers c ON c.id = i.customer_id
                              WHERE i.id=?");
        $st->execute([(int)$sr['invoice_id']]);
        $inv = $st->fetch(\PDO::FETCH_ASSOC) ?: [];

        $items = SalesReturn::items((int)$sr['id']);

        // compute subtotal/tax/total for printing (your template expects these keys)
        $subtotal = 0.0;
        foreach ($items as $it) { $subtotal += (float)($it['line_total'] ?? 0); }
        $taxRate   = isset($sr['tax_rate']) ? (float)$sr['tax_rate'] : (float)($inv['tax_rate'] ?? 0.0);
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $total     = $subtotal + $taxAmount;

        $sr['subtotal']   = $subtotal;
        $sr['tax_rate']   = $taxRate;
        $sr['tax_amount'] = $taxAmount;
        $sr['total']      = $total;

        $this->view_raw('salesreturns/print', [
            'sr'    => $sr,
            'items' => $items,
            'inv'   => $inv,
        ]);
    }
}
