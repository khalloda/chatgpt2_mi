<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SalesOrder;
use App\Models\Note;
use function App\Core\require_auth;
use function App\Core\redirect;

final class OrdersController extends Controller
{
  public function index(): void {
    require_auth();
    $this->view('orders/index', ['items'=>SalesOrder::all()]);
  }

  public function show(): void {
    require_auth();
    $id=(int)($_GET['id']??0);
    $items = SalesOrder::items($id);
    $list  = SalesOrder::all();
    $order = null; foreach ($list as $o) if((int)$o['id']===$id){ $order=$o; break; }
    if(!$order){ $this->redirect('/orders'); return; }
    $this->view('orders/view', ['o'=>$order,'items'=>$items,'notes' => Note::for('sales_order', $id)]);
  }
  public function printpage(): void
{
    require_auth();
    $id = (int)($_GET['id'] ?? 0);
    $includeNotes = isset($_GET['include_notes']) && $_GET['include_notes'] === '1';

    // fetch one order; we used SalesOrder::all() previously, but here we’ll reuse that pattern:
    $list  = \App\Models\SalesOrder::all();
    $order = null; foreach ($list as $o) if ((int)$o['id'] === $id) { $order = $o; break; }
    if (!$order) { flash_set('error','Order not found.'); redirect('/orders'); }

    $items = \App\Models\SalesOrder::items($id);
    $publicNotes = $includeNotes ? Note::publicFor('sales_order', $id) : [];

    $this->view_raw('orders/print', [
        'o' => $order,
        'items' => $items,
        'public_notes' => $publicNotes,
        'include_notes' => $includeNotes,
    ]);
}
}
