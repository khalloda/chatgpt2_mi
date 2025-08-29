<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\Quote;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
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
            redirect('/quotes/view?id=' . $quoteId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error', 'Error: ' . $e->getMessage());
            redirect('/quotes');
        }
    }

    public function view(): void
    {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $quote = Quote::find($id);
        if (!$quote) { flash_set('error','Quote not found.'); redirect('/quotes'); }
        $items = Quote::items($id);
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
            redirect('/quotes/view?id='.$id);
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
            redirect('/quotes/view?id='.$id);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Error: '.$e->getMessage());
            redirect('/quotes');
        }
    }

    private function productOptions(): array
    {
        // minimal product list (id, display)
        $sql = "SELECT p.id, CONCAT(p.code, ' — ', p.name) AS label FROM products p ORDER BY p.name";
        return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
}
