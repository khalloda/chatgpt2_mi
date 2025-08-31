<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Note;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class PurchaseInvoicesController extends Controller
{
    public function index(): void {
        require_auth();
        $this->view('purchaseinvoices/index', ['items' => PurchaseInvoice::all()]);
    }

    public function show(): void {
    require_auth();
    $id = (int)($_GET['id'] ?? 0);
    $pi = PurchaseInvoice::find($id);
    if (!$pi) { flash_set('error','Invoice not found.'); redirect('/purchaseinvoices'); }

    $items = PurchaseInvoice::poItems($id);
    $receivedMap = PurchaseInvoice::receivedMapByPo((int)$pi['purchase_order_id']);
    $receipts = \App\Models\PurchaseInvoice::receipts($id);

    $this->view('purchaseinvoices/view', [
        'pi'       => $pi,
        'items'    => $items,
        'received' => $receivedMap,
        'receipts' => $receipts,
        'notes'    => \App\Models\Note::for('purchase_invoice', $id),
    ]);
}

    public function createfrompo(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseorders'); }
        $poId = (int)($_POST['purchase_order_id'] ?? 0);
        $po = PurchaseOrder::find($poId);
        if (!$po) { flash_set('error','PO not found.'); redirect('/purchaseorders'); }

        $pdo = DB::conn(); $pdo->beginTransaction();
        try {
            $piNo = PurchaseInvoice::nextNumber();
            $ins = $pdo->prepare("INSERT INTO purchase_invoices
                (pi_no, purchase_order_id, supplier_id, subtotal, tax_rate, tax_amount, total)
                VALUES (?,?,?,?,?,?,?)");
            $ins->execute([
                $piNo, $poId, (int)$po['supplier_id'], (float)$po['subtotal'],
                (float)$po['tax_rate'], (float)$po['tax_amount'], (float)$po['total']
            ]);
            $piId = (int)$pdo->lastInsertId();

            $pdo->commit();
            flash_set('success', 'Purchase invoice '.$piNo.' created.');
            redirect('/purchaseinvoices/show?id='.$piId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Create invoice failed: '.$e->getMessage());
            redirect('/purchaseorders/show?id='.$poId);
        }
    }

    public function printpage(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $includeNotes = isset($_GET['include_notes']) && $_GET['include_notes'] === '1';
        $pi = PurchaseInvoice::find($id);
        if (!$pi) { flash_set('error','Invoice not found.'); redirect('/purchaseinvoices'); }

        $items = PurchaseInvoice::poItems($id);
        $publicNotes = $includeNotes ? Note::publicFor('purchase_invoice', $id) : [];
        $this->view_raw('purchaseinvoices/print', [
            'pi' => $pi,
            'items' => $items,
            'public_notes' => $publicNotes,
            'include_notes' => $includeNotes,
        ]);
    }
}
