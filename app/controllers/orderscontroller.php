<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SalesOrder;
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
    $this->view('orders/view', ['o'=>$order,'items'=>$items]);
  }
}
