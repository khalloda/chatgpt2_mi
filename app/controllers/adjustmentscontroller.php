<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use PDO;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class AdjustmentsController extends Controller
{
    public function index(): void {
        require_auth();
        $pdo = DB::conn();
        $rows = $pdo->query("SELECT id, adj_no, warehouse_id, reason, created_at FROM stock_adjustments ORDER BY id DESC LIMIT 200")
                    ->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $ws = $pdo->query("SELECT id,name FROM warehouses")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        foreach ($rows as &$r) { $r['warehouse_name'] = $ws[(int)$r['warehouse_id']] ?? ('#'.$r['warehouse_id']); }
        unset($r);
        $this->view('adjustments/index', ['items'=>$rows]);
    }

    public function create(): void {
        require_auth();
        $pdo = DB::conn();
        $warehouses = $pdo->query("SELECT id,name FROM warehouses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $products   = $pdo->query("SELECT id, code, name FROM products ORDER BY code, name")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $this->view('adjustments/form', ['warehouses'=>$warehouses, 'products'=>$products]);
    }

    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/adjustments'); }

        $wid   = (int)($_POST['warehouse_id'] ?? 0);
        $reason= $_POST['reason'] ?? 'count';
        $note  = trim((string)($_POST['note'] ?? ''));
        $pids  = $_POST['product_id'] ?? [];
        $qtys  = $_POST['qty_change'] ?? [];

        if ($wid<=0) { flash_set('error','Choose a warehouse.'); redirect('/adjustments/create'); }

        $pdo = DB::conn(); $pdo->beginTransaction();
        try {
            $pdo->prepare("INSERT INTO stock_adjustments (adj_no, warehouse_id, reason, note) VALUES ('',?,?,?)")
                ->execute([$wid,$reason,$note]);
            $adjId = (int)$pdo->lastInsertId();
            $adjNo = 'AD'.date('ymd').'-'.str_pad((string)$adjId, 4, '0', STR_PAD_LEFT);
            $pdo->prepare("UPDATE stock_adjustments SET adj_no=? WHERE id=?")->execute([$adjNo,$adjId]);

            $insItem = $pdo->prepare("INSERT INTO stock_adjustment_items (stock_adjustment_id, product_id, qty_change) VALUES (?,?,?)");

            for ($i=0, $n=max(count($pids),count($qtys)); $i<$n; $i++) {
                $pid = (int)($pids[$i] ?? 0);
                $chg = (int)($qtys[$i] ?? 0);
                if ($pid<=0 || $chg===0) continue;

                // 1) lock stock (composite key) and read avg_cost
                $st = $pdo->prepare("
                    SELECT qty_on_hand, qty_reserved, avg_cost
                      FROM product_stocks
                     WHERE product_id=? AND warehouse_id=?
                     FOR UPDATE
                ");
                $st->execute([$pid,$wid]);
                $row = $st->fetch(PDO::FETCH_ASSOC);
                $avg = (float)($row['avg_cost'] ?? 0.0);

                if ($chg < 0) {
                    // 2a) negative adjustment: ensure free qty then decrease on-hand
                    $on  = (int)($row['qty_on_hand'] ?? 0);
                    $res = (int)($row['qty_reserved'] ?? 0);
                    $free = $on - $res;
                    if (-$chg > $free) {
                        throw new \RuntimeException("Insufficient free stock to decrease product #{$pid}.");
                    }
                    $pdo->prepare("UPDATE product_stocks SET qty_on_hand = qty_on_hand + ? WHERE product_id=? AND warehouse_id=?")
                        ->execute([$chg, $pid, $wid]); // $chg negative
                } else {
                    // 2b) positive adjustment: ensure row exists; DO NOT change avg_cost (count-only adjustment)
                    if ($row) {
                        $pdo->prepare("UPDATE product_stocks SET qty_on_hand = qty_on_hand + ? WHERE product_id=? AND warehouse_id=?")
                            ->execute([$chg, $pid, $wid]);
                    } else {
                        $pdo->prepare("INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, qty_reserved, avg_cost) VALUES (?,?,?,?,?)")
                            ->execute([$pid,$wid,$chg,0,0.0]);
                        $avg = 0.0; // new row has zero avg until next receipt establishes it
                    }
                }

                // 3) ledger: adjustment at current avg (value impact = qty_change * avg_cost)
                $pdo->prepare("
                    INSERT INTO inventory_ledger (product_id, warehouse_id, doc_type, doc_id, qty_delta, unit_cost, value_delta)
                    VALUES (?,?,?,?,?,?,?)
                ")->execute([$pid, $wid, 'adjustment', $adjId, $chg, $avg, $chg * $avg]);

                // 4) item row
                $insItem->execute([$adjId,$pid,$chg]);
            }

            $pdo->commit();
            flash_set('success',"Adjustment {$adjNo} saved.");
            redirect('/adjustments/show?id='.$adjId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Save failed: '.$e->getMessage());
            redirect('/adjustments/create');
        }
    }

    public function show(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id<=0) { redirect('/adjustments'); }

        $pdo = DB::conn();
        $st = $pdo->prepare("SELECT * FROM stock_adjustments WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $a = $st->fetch(PDO::FETCH_ASSOC);
        if (!$a) { $this->view('errors/404',['message'=>'Adjustment not found']); return; }

        $items = $pdo->prepare("SELECT i.*, p.code AS product_code, p.name AS product_name
                                FROM stock_adjustment_items i
                                JOIN products p ON p.id=i.product_id
                                WHERE i.stock_adjustment_id=? ORDER BY i.id");
        $items->execute([$id]);
        $rows = $items->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $ws = $pdo->query("SELECT id,name FROM warehouses")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $a['warehouse_name'] = $ws[(int)$a['warehouse_id']] ?? ('#'.$a['warehouse_id']);

        $this->view('adjustments/view', ['a'=>$a,'items'=>$rows]);
    }

    public function printnote(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id<=0) { redirect('/adjustments'); }
        $pdo = DB::conn();

        $st = $pdo->prepare("SELECT * FROM stock_adjustments WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $a = $st->fetch(PDO::FETCH_ASSOC);
        if (!$a) { $this->view('errors/404',['message'=>'Adjustment not found']); return; }

        $items = $pdo->prepare("SELECT i.*, p.code AS product_code, p.name AS product_name
                                FROM stock_adjustment_items i
                                JOIN products p ON p.id=i.product_id
                                WHERE i.stock_adjustment_id=? ORDER BY i.id");
        $items->execute([$id]);
        $rows = $items->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $ws = $pdo->query("SELECT id,name FROM warehouses")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $a['warehouse_name'] = $ws[(int)$a['warehouse_id']] ?? ('#'.$a['warehouse_id']);

        $this->view('adjustments/print', ['a'=>$a,'items'=>$rows]);
    }
}
