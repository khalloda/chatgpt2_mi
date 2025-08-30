<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\Quote;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\SalesOrder;
use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class QuotesController extends Controller
{
    public function index(): void
    {
        require_auth();
        $this->view('quotes/index', ['items' => Quote::all()]);
    }

    public function create(): void
    {
        require_auth();
        $this->view('quotes/form', [
            'mode' => 'create',
            'quote_no' => Quote::nextNumber(),
            'customers' => Customer::options(),
            'warehouses' => Warehouse::options(),
            'products' => $this->productOptions(),
            'item_rows' => 3, // show 3 blank lines initially
        ]);
    }

    public function store(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error', 'Invalid session.'); redirect('/quotes'); }

        $cust = (int)($_POST['customer_id'] ?? 0);
        $tax  = (float)($_POST['tax_rate'] ?? 0);
        $exp  = (string)($_POST['expires_at'] ?? '');
        $qs   = $this->readItems();
		// Ensure we do not reserve more than available (sum duplicates per product/warehouse)
$want = [];
foreach ($qs as $q) {
    $key = $q['product_id'].':'.$q['warehouse_id'];
    $want[$key] = ($want[$key] ?? 0) + (int)$q['qty'];
}
foreach ($want as $key => $qty) {
    [$pid, $wid] = array_map('intval', explode(':', $key, 2));
    $avail = $this->availableQty($pid, $wid);
    if ($qty > $avail) {
        $label = $this->productLabel($pid);
        flash_set('error', "Insufficient stock to reserve ($label @ selected warehouse). Available: $avail, Requested: $qty.");
        redirect('/quotes/create'); // do NOT save/reserve anything
    }
}
// ensure price defaults from product when not provided or zero
foreach ($qs as &$q) {
    if (empty($q['price']) || (float)$q['price'] <= 0) {
        $q['price'] = $this->productPrice((int)$q['product_id']);
    }
}
unset($q);
        if ($cust<=0 || !$qs) { flash_set('error','Customer and at least one line item are required.'); redirect('/quotes/create'); }

        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            $no = Quote::nextNumber();
            // compute totals
            $subtotal = 0.00;
            foreach ($qs as $q) { $subtotal += $q['qty'] * $q['price']; }
            $tax_amount = round($subtotal * ($tax/100), 2);
            $total = round($subtotal + $tax_amount, 2);

            $ins = $pdo->prepare('INSERT INTO quotes (quote_no, customer_id, status, tax_rate, subtotal, tax_amount, total, expires_at)
                                  VALUES (?,?,?,?,?,?,?,?)');
            $ins->execute([$no, $cust, 'sent', $tax, $subtotal, $tax_amount, $total, $exp ?: null]);
            $quoteId = (int)$pdo->lastInsertId();

            $insIt = $pdo->prepare('INSERT INTO quote_items (quote_id, product_id, warehouse_id, qty, price, line_total)
                                    VALUES (?, ?, ?, ?, ?, ?)');
            foreach ($qs as $q) {
                $lt = round($q['qty'] * $q['price'], 2);
                $insIt->execute([$quoteId, $q['product_id'], $q['warehouse_id'], $q['qty'], $q['price'], $lt]);
                // reserve stock
                Product::adjustReserved($q['product_id'], $q['warehouse_id'], $q['qty']);
            }

            $pdo->commit();
            flash_set('success', 'Quote created and stock reserved.');
            redirect('/quotes/show?id=' . $quoteId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error', 'Error: ' . $e->getMessage());
            redirect('/quotes');
        }
    }

    public function show(): void
    {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $quote = Quote::find($id);
         if (!$quote) {
        flash_set('error', 'Quote not found.');
        $this->view('quotes/index', ['items' => Quote::all()]); // no redirect
        return;
    }
        $items = Quote::items($id);
		// preflight stock check: block if any line cannot be fulfilled
foreach ($items as $it) {
    $pid = (int)$it['product_id'];
    $wid = (int)$it['warehouse_id'];
    $q   = (int)$it['qty'];
    if (!\App\Models\Product::canFulfill($pid, $wid, $q)) {
        flash_set('error', 'Insufficient stock to convert ('
            . htmlspecialchars($it['product_code'].' — '.$it['product_name'])
            . ' @ ' . htmlspecialchars($it['warehouse_name']) . ').');
        redirect('/quotes/show?id=' . $id);
    }
}
        $this->view('quotes/view', ['q'=>$quote, 'items'=>$items]);
    }

    public function cancel(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/quotes'); }
        $id = (int)($_POST['id'] ?? 0);
        $q = Quote::find($id);
        if (!$q || $q['status'] !== 'sent') { flash_set('error','Only sent quotes can be cancelled.'); redirect('/quotes'); }

        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            // release reservations
            $items = Quote::items($id);
            foreach ($items as $it) {
                Product::adjustReserved((int)$it['product_id'], (int)$it['warehouse_id'], -((int)$it['qty']));
            }
            $pdo->prepare('UPDATE quotes SET status="cancelled" WHERE id=?')->execute([$id]);
            $pdo->commit();
            flash_set('success','Quote cancelled and reservation released.');
            redirect('/quotes/show?id='.$id);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Error: '.$e->getMessage());
            redirect('/quotes');
        }
    }

    public function expire(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/quotes'); }
        $id = (int)($_POST['id'] ?? 0);
        $q = Quote::find($id);
        if (!$q || $q['status'] !== 'sent') { flash_set('error','Only sent quotes can be expired.'); redirect('/quotes'); }

        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            $items = Quote::items($id);
            foreach ($items as $it) {
                Product::adjustReserved((int)$it['product_id'], (int)$it['warehouse_id'], -((int)$it['qty']));
            }
            $pdo->prepare('UPDATE quotes SET status="expired" WHERE id=?')->execute([$id]);
            $pdo->commit();
            flash_set('success','Quote marked as expired and reservation released.');
            redirect('/quotes/show?id='.$id);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Error: '.$e->getMessage());
            redirect('/quotes');
        }
    }

    private function productOptions(): array
    {
        // minimal product list (id, display)
        $sql = "SELECT p.id,
                   CONCAT(p.code, ' — ', p.name) AS label,
                   p.price
            FROM products p
            ORDER BY p.name";
    return DB::conn()->query($sql)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

private function productPrice(int $id): float
{
    $st = DB::conn()->prepare('SELECT price FROM products WHERE id = ?');
    $st->execute([$id]);
    return (float)$st->fetchColumn();
}

    private function readItems(): array
    {
        $pids = $_POST['product_id'] ?? [];
        $wids = $_POST['warehouse_id'] ?? [];
        $qtys = $_POST['qty'] ?? [];
        $prices = $_POST['price'] ?? [];

        $rows = [];
        for ($i=0; $i < count($pids); $i++) {
            $pid = (int)($pids[$i] ?? 0);
            $wid = (int)($wids[$i] ?? 0);
            $q   = (int)($qtys[$i] ?? 0);
            $pr  = (float)($prices[$i] ?? 0);
            if ($pid>0 && $wid>0 && $q>0) {
                $rows[] = ['product_id'=>$pid,'warehouse_id'=>$wid,'qty'=>$q,'price'=>$pr];
            }
        }
        return $rows;
    }
	
	public function convert(): void
{
    require_auth();
    if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/quotes'); }
    $id = (int)($_POST['id'] ?? 0);
    $q  = Quote::find($id);
    if (!$q || $q['status'] !== 'sent') { flash_set('error','Only sent quotes can be converted.'); redirect('/quotes'); }

    $pdo = DB::conn(); $pdo->beginTransaction();
    try {
        $items = Quote::items($id);
        $soNo = SalesOrder::nextNumber();
        $insSo = $pdo->prepare("INSERT INTO sales_orders (so_no, quote_id, customer_id, status, tax_rate, subtotal, tax_amount, total)
                                VALUES (?,?,?,?,?,?,?,?)");
        $insSo->execute([$soNo, $id, $q['customer_id'], 'open', $q['tax_rate'], $q['subtotal'], $q['tax_amount'], $q['total']]);
        $soId = (int)$pdo->lastInsertId();

        $insItem = $pdo->prepare("INSERT INTO sales_order_items (sales_order_id, product_id, warehouse_id, qty, price, line_total)
                                  VALUES (?,?,?,?,?,?)");
        foreach ($items as $it) {
            $qty = (int)$it['qty'];
            $insItem->execute([$soId, (int)$it['product_id'], (int)$it['warehouse_id'], $qty, (float)$it['price'], (float)$it['line_total']]);
            // move from reserved to consumed
            Product::consumeFromReservation((int)$it['product_id'], (int)$it['warehouse_id'], $qty);
        }

        $pdo->prepare('UPDATE quotes SET status="ordered" WHERE id=?')->execute([$id]);
        $pdo->commit();
        flash_set('success','Converted to Sales Order ' . $soNo . ' and stock deducted.');
        redirect('/orders/show?id='.$soId);
    } catch (\Throwable $e) {
        $pdo->rollBack();
        flash_set('error','Convert failed: '.$e->getMessage());
        redirect('/quotes/show?id='.$id);
    }
}
private function availableQty(int $productId, int $warehouseId): int
{
    $st = DB::conn()->prepare('SELECT qty_on_hand, qty_reserved
                               FROM product_stocks
                               WHERE product_id=? AND warehouse_id=?');
    $st->execute([$productId, $warehouseId]);
    $row = $st->fetch(\PDO::FETCH_ASSOC) ?: ['qty_on_hand'=>0,'qty_reserved'=>0];
    $on  = (int)$row['qty_on_hand'];
    $res = (int)$row['qty_reserved'];
    return max(0, $on - $res);
}

private function productLabel(int $id): string
{
    $st = DB::conn()->prepare('SELECT CONCAT(code, " — ", name) FROM products WHERE id=?');
    $st->execute([$id]);
    return (string)($st->fetchColumn() ?: ('#'.$id));
}

}
