<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\SalesOrder;
use App\Models\Quote;
use App\Models\Note;
use App\Services\DocNumbers;
use PDO;

use function App\Core\require_auth;
use function App\Core\redirect;
use function App\Core\flash_set;
use function App\Core\verify_csrf_post;

final class OrdersController extends Controller
{
  public function index(): void {
    require_auth();
    $this->view('orders/index', ['items'=>SalesOrder::all()]);
  }

  public function show(): void {
    require_auth();
    $id = (int)($_GET['id'] ?? 0);
    $list = SalesOrder::all();
    $order = null; foreach ($list as $o) if ((int)$o['id'] === $id) { $order = $o; break; }
    if (!$order) { flash_set('error','Order not found.'); redirect('/orders'); }
    $items = SalesOrder::items($id);
    $this->view('orders/view', ['o'=>$order,'items'=>$items,'notes'=>Note::for('sales_order',$id)]);
  }

  /** Optional: direct SO creation (allocate number on save) */
  public function store(): void {
    require_auth();
    if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/orders'); }

    $customerId = (int)($_POST['customer_id'] ?? 0);
    $taxRate    = (float)($_POST['tax_rate'] ?? 0);
    $items      = $this->readItems();
    if (!$items) { flash_set('error','At least one item is required.'); redirect('/orders'); }

    $subtotal=0.0;
    foreach ($items as &$it) { $it['qty']=max(1,(int)$it['qty']); $it['price']=(float)$it['price']; $it['line_total']=$it['qty']*$it['price']; $subtotal+=$it['line_total']; }
    unset($it);
    $taxAmount = round($subtotal*($taxRate/100),2);
    $total = $subtotal+$taxAmount;

    $pdo = DB::conn();
    $pdo->beginTransaction();
    try {
      $soNo = DocNumbers::next('so');
      $pdo->prepare("INSERT INTO sales_orders (so_no, customer_id, status, tax_rate, subtotal, tax_amount, total, created_at)
                     VALUES (?,?,?,?,?,?,?, NOW())")->execute([$soNo,$customerId,'draft',$taxRate,$subtotal,$taxAmount,$total]);
      $soId = (int)$pdo->lastInsertId();

      $ins = $pdo->prepare("INSERT INTO sales_order_items (sales_order_id, product_id, warehouse_id, qty, price, line_total) VALUES (?,?,?,?,?,?)");
      foreach ($items as $row) { $ins->execute([$soId,(int)$row['product_id'],(int)$row['warehouse_id'],(int)$row['qty'],(float)$row['price'],(float)$row['line_total']]); }

      $pdo->commit();
      flash_set('success','Sales Order created: '.$soNo);
      redirect('/orders/show?id='.$soId);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      flash_set('error','Save failed: '.$e->getMessage());
      redirect('/orders');
    }
  }

  /** POST /orders/createfromquote — mirror Q→SO with suffix fallback */
  public function createfromquote(): void {
    require_auth();
    if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/quotes'); }

    $qid = (int)($_POST['quote_id'] ?? 0);
    $q = Quote::find($qid);
    if (!$q) { flash_set('error','Quote not found.'); redirect('/quotes'); }

    $pdo = DB::conn();
    $pdo->beginTransaction();
    try {
      $base = $this->swapPrefix((string)$q['quote_no'], 'Q', 'SO');
      $soNo = $this->uniqueNo($base, 'sales_orders', 'so_no', $pdo);

      $pdo->prepare("INSERT INTO sales_orders (so_no, customer_id, status, tax_rate, subtotal, tax_amount, total, created_at)
                     VALUES (?,?,?,?,?,?,?, NOW())")->execute([
                       $soNo,(int)$q['customer_id'],'draft',(float)$q['tax_rate'],(float)$q['subtotal'],(float)$q['tax_amount'],(float)$q['total']
                     ]);
      $soId = (int)$pdo->lastInsertId();

      $copy = $pdo->prepare("SELECT product_id, warehouse_id, qty, price, line_total FROM quote_items WHERE quote_id=?");
      $ins  = $pdo->prepare("INSERT INTO sales_order_items (sales_order_id, product_id, warehouse_id, qty, price, line_total) VALUES (?,?,?,?,?,?)");
      $copy->execute([$qid]);
      while ($row = $copy->fetch(PDO::FETCH_ASSOC)) {
        $ins->execute([$soId,(int)$row['product_id'],(int)$row['warehouse_id'],(int)$row['qty'],(float)$row['price'],(float)$row['line_total']]);
      }

      $pdo->commit();
      flash_set('success','Sales Order created: '.$soNo);
      redirect('/orders/show?id='.$soId);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      flash_set('error','Create order failed: '.$e->getMessage());
      redirect('/quotes/show?id='.$qid);
    }
  }

  public function printpage(): void {
    require_auth();
    $id = (int)($_GET['id'] ?? 0);
    $order = null; foreach (SalesOrder::all() as $o) if ((int)$o['id']===$id) { $order=$o; break; }
    if (!$order) { flash_set('error','Order not found.'); redirect('/orders'); }
    $items = SalesOrder::items($id);
    $includeNotes = isset($_GET['include_notes']) && $_GET['include_notes']==='1';
    $publicNotes = $includeNotes ? Note::publicFor('sales_order',$id) : [];
    $this->view_raw('orders/print', ['o'=>$order,'items'=>$items,'public_notes'=>$publicNotes,'include_notes'=>$includeNotes]);
  }

  private function readItems(): array {
    $rows=[]; $pids=$_POST['product_id']??[]; $wids=$_POST['warehouse_id']??[]; $qtys=$_POST['qty']??[]; $prices=$_POST['price']??[]; 
    $n=max(count($pids),count($wids),count($qtys),count($prices));
    for($i=0;$i<$n;$i++){ $pid=(int)($pids[$i]??0); $wid=(int)($wids[$i]??0); $q=(int)($qtys[$i]??0); $pr=(float)($prices[$i]??0);
      if($pid>0&&$wid>0&&$q>0&&$pr>=0){ $rows[]=['product_id'=>$pid,'warehouse_id'=>$wid,'qty'=>$q,'price'=>$pr]; } }
    return $rows;
  }

  private function swapPrefix(string $src, string $from, string $to): string {
    if (preg_match('/^'.preg_quote($from,'/').'(?P<yr>\d{4})-(?P<seq>\d{4})$/i',$src,$m)) return sprintf('%s%s-%s',$to,$m['yr'],$m['seq']);
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
