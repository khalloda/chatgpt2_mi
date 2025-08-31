<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\PurchaseInvoice;
use App\Models\SupplierPayment;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;
use function App\Core\activity_log;

final class SupplierPaymentsController extends Controller
{
    public function index(): void {
        require_auth();
        $this->view('supplierpayments/index', ['items' => SupplierPayment::all(200)]);
    }

    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseinvoices'); }

        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $amount    = (float)($_POST['amount'] ?? 0);
        $paidAt    = (string)($_POST['paid_at'] ?? '');
        $method    = trim((string)($_POST['method'] ?? 'cash'));
        $reference = trim((string)($_POST['reference'] ?? ''));
        $note      = trim((string)($_POST['note'] ?? ''));
        $return    = (string)($_POST['_return'] ?? '/purchaseinvoices');

        $pi = PurchaseInvoice::find($invoiceId);
        if (!$pi) { flash_set('error','Invoice not found.'); redirect($return); }

        if (($pi['status'] ?? 'unpaid') === 'paid') {
            flash_set('error','Invoice is fully paid; payments are locked.');
            redirect($return);
        }

        $remaining = max(0.0, (float)$pi['total'] - (float)$pi['paid_amount']);
        if ($remaining <= 0.0) {
            flash_set('error','Nothing to pay; invoice is already fully paid.');
            redirect($return);
        }
        $capped = false;
        if ($amount > $remaining) { $amount = $remaining; $capped = true; }
        if ($amount <= 0.0 || $paidAt === '') {
            flash_set('error','Payment requires a date and a positive amount.');
            redirect($return);
        }

        $id = SupplierPayment::create([
            'supplier_id' => (int)$pi['supplier_id'],
            'purchase_invoice_id' => $invoiceId,
            'paid_at' => $paidAt,
            'method' => $method,
            'reference' => $reference,
            'amount' => $amount,
            'note' => $note,
        ]);

        PurchaseInvoice::recalcPaidAmount($invoiceId);

        if (function_exists('App\Core\activity_log')) {
            activity_log('ap.add', 'purchase_invoice', $invoiceId, [
                'payment_id'=>$id, 'amount'=>$amount, 'method'=>$method, 'reference'=>$reference
            ]);
        }

        flash_set('success', $capped ? 'Supplier payment recorded (amount capped to remaining).' : 'Supplier payment recorded.');
        redirect($return);
    }

    public function destroy(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseinvoices'); }

        $id        = (int)($_POST['id'] ?? 0);
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $return    = (string)($_POST['_return'] ?? '/purchaseinvoices');

        $pi = PurchaseInvoice::find($invoiceId);
        if (!$pi) { flash_set('error','Invoice not found.'); redirect($return); }

        if (($pi['status'] ?? 'unpaid') === 'paid') {
            flash_set('error','Invoice is fully paid; payments are locked.');
            redirect($return);
        }

        if ($id > 0) {
            SupplierPayment::delete($id);
            PurchaseInvoice::recalcPaidAmount($invoiceId);

            if (function_exists('App\Core\activity_log')) {
                activity_log('ap.delete', 'purchase_invoice', $invoiceId, ['payment_id'=>$id]);
            }
            flash_set('success','Supplier payment deleted.');
        }
        redirect($return);
    }
}
