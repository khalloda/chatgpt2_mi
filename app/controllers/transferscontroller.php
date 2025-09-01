<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use PDO;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class TransfersController extends Controller
{
    public function index(): void {
        require_auth();
        $pdo = DB::conn();
        $rows = $pdo->query("SELECT id,tr_no,from_warehouse_id,to_warehouse_id,created_at FROM stock_transfers ORDER BY id DESC LIMIT 200")
                    ->fetchAll(PDO::FETCH_ASSOC) ?: [];
        // map warehouse names
        $ws = $pdo->query("SELECT id,name FROM warehouses")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        foreach ($rows as &$r) {
            $r['from_name'] = $ws[(int)$r['from_warehouse_id']] ?? ('#'.$r['from_warehouse_id']);
            $r['to_name']   = $ws[(int)$r['to_warehouse_id']]   ?? ('#'.$r['to_warehouse_id']);
        }
        unset($r);
        $this->view('transfers/index', ['items'=>$rows]);
    }

    public function create(): void {
        require_auth();
        $pdo = DB::conn();
        $warehouses = $pdo->query("SELECT id,name FROM warehouses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $products   = $pdo->query("SELECT id, code, name FROM products ORDER BY code, name")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $this->view('transfers/form', ['warehouses'=>$warehouses, 'products'=>$products]);
    }

    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/transfers'); }

        $from = (int)($_POST['from_warehouse_id'] ?? 0);
        $to   = (int)($_POST['to_warehouse_id'] ?? 0);
        $note = trim((string)($_POST['note'] ?? ''));
        $pids = $_POST['product_id'] ?? [];
        $qtys = $_POST['qty'] ?? [];

        if ($from<=0 || $to<=0 || $from===$to) {
            flash_set('error','Choose different From/To warehouses.'); redirect('/transfers/create');
        }

        $pdo = DB::conn(); $pdo->beginTransaction();
        try {
            // insert header with placeholder number
            $pdo->prepare("INSERT INTO stock_transfers (tr_no, from_warehouse_id, to_warehouse_id, note) VALUES ('',?,?,?)")
                ->execute([$from,$to,$note]);
            $trId = (int)$pdo->lastInsertId();
            $trNo = 'TR'.date('ymd').'-'.str_pad((string)$trId, 4, '0', STR_PAD_LEFT);
            $pdo->prepare("UPDATE stock_transfers SET tr_no=? WHERE id=?")->execute([$trNo,$trId]);

            $insItem = $pdo->prepare("INSERT INTO stock_transfer_items (stock_transfer_id, product_id, qty) VALUES (?,?,?)");

            for ($i=0, $n=max(count($pids),count($qtys)); $i<$n; $i++) {
                $pid = (int)($pids[$i] ?? 0);
                $qty = (int)($qtys[$i] ?? 0);
                if ($pid<=0 || $qty<=0) continue;

                // lock source stock
                $st = $pdo->prepare("SELECT id, qty_on_hand, qty_reserved FROM product_stocks WHERE product_id=? AND warehouse_id=? FOR UPDATE");
                $st->execute([$pid, $from]);
                $src = $st->fetch(PDO::FETCH_ASSOC);

                $on  = (int)($src['qty_on_hand'] ?? 0);
                $res = (int)($src['qty_reserved'] ?? 0);
                $free = $on - $res;
                if ($qty > $free) {
                    throw new \RuntimeException("Insufficient free stock for product #{$pid} in source warehouse.");
                }

                // decrement source
                if ($src) {
                    $pdo->prepare("UPDATE product_stocks SET qty_on_hand = qty_on_hand - ? WHERE id=?")
                        ->execute([$qty, (int)$src['id']]);
                } else {
                    throw new \RuntimeException("No stock row for product #{$pid} in source warehouse.");
                }

                // increment destination (ensure row)
                $st = $pdo->prepare("SELECT id FROM product_stocks WHERE product_id=? AND warehouse_id=? FOR UPDATE");
                $st->execute([$pid,$to]);
                $dst = $st->fetch(PDO::FETCH_ASSOC);
                if ($dst) {
                    $pdo->prepare("UPDATE product_stocks SET qty_on_hand = qty_on_hand + ? WHERE id=?")
                        ->execute([$qty, (int)$dst['id']]);
                } else {
                    $pdo->prepare("INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, qty_reserved) VALUES (?,?,?,0)")
                        ->execute([$pid,$to,$qty]);
                }

                // item row
                $insItem->execute([$trId,$pid,$qty]);
            }

            $pdo->commit();
            flash_set('success',"Transfer {$trNo} created.");
            redirect('/transfers/show?id='.$trId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Save failed: '.$e->getMessage());
            redirect('/transfers/create');
        }
    }

    public function show(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id<=0) { redirect('/transfers'); }

        $pdo = DB::conn();
        $st = $pdo->prepare("SELECT * FROM stock_transfers WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $t = $st->fetch(PDO::FETCH_ASSOC);
        if (!$t) { $this->view('errors/404',['message'=>'Transfer not found']); return; }

        $items = $pdo->prepare("SELECT i.*, p.code AS product_code, p.name AS product_name
                                FROM stock_transfer_items i
                                JOIN products p ON p.id=i.product_id
                                WHERE i.stock_transfer_id=? ORDER BY i.id");
        $items->execute([$id]);
        $rows = $items->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $ws = $pdo->query("SELECT id,name FROM warehouses")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $t['from_name'] = $ws[(int)$t['from_warehouse_id']] ?? ('#'.$t['from_warehouse_id']);
        $t['to_name']   = $ws[(int)$t['to_warehouse_id']]   ?? ('#'.$t['to_warehouse_id']);

        $this->view('transfers/view', ['t'=>$t,'items'=>$rows]);
    }

    public function printnote(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id<=0) { redirect('/transfers'); }

        $pdo = DB::conn();
        $st = $pdo->prepare("SELECT * FROM stock_transfers WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $t = $st->fetch(PDO::FETCH_ASSOC);
        if (!$t) { $this->view('errors/404',['message'=>'Transfer not found']); return; }

        $items = $pdo->prepare("SELECT i.*, p.code AS product_code, p.name AS product_name
                                FROM stock_transfer_items i
                                JOIN products p ON p.id=i.product_id
                                WHERE i.stock_transfer_id=? ORDER BY i.id");
        $items->execute([$id]);
        $rows = $items->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $ws = $pdo->query("SELECT id,name FROM warehouses")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $t['from_name'] = $ws[(int)$t['from_warehouse_id']] ?? ('#'.$t['from_warehouse_id']);
        $t['to_name']   = $ws[(int)$t['to_warehouse_id']]   ?? ('#'.$t['to_warehouse_id']);

        $this->view('transfers/print', ['t'=>$t,'items'=>$rows]);
    }
}
