<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Invoice;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;
use App\Core\DB;

final class PaymentsController extends Controller
{
    public function store(): void {
    require_auth();
    if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/invoices'); }

    $invoiceId = (int)($_POST['invoice_id'] ?? 0);
    $amount    = (float)($_POST['amount'] ?? 0);
    $paidAt    = (string)($_POST['paid_at'] ?? '');
    $method    = trim((string)($_POST['method'] ?? 'cash'));
    $reference = trim((string)($_POST['reference'] ?? ''));
    $note      = trim((string)($_POST['note'] ?? ''));
    $return    = (string)($_POST['_return'] ?? '/invoices');

    $inv = \App\Models\Invoice::find($invoiceId);
    if (!$inv) { flash_set('error','Invoice not found.'); redirect($return); }

    // Block edits once paid
    if (($inv['status'] ?? '') === 'paid') {
        flash_set('error','Invoice is fully paid; payments are locked.');
        redirect($return);
    }

    // Prevent overpayment (cap to remaining)
    $remaining = max(0.0, (float)$inv['total'] - (float)$inv['paid_amount']);
    if ($remaining <= 0.0) {
        flash_set('error','Nothing to pay; invoice is already fully paid.');
        redirect($return);
    }
    $capped = false;
    if ($amount > $remaining) {
        $amount = $remaining;
        $capped = true;
    }
    if ($amount <= 0.0 || $paidAt === '') {
        flash_set('error','Payment requires a date and a positive amount.');
        redirect($return);
    }

    $pdo = \App\Core\DB::conn();
    $st = $pdo->prepare("INSERT INTO invoice_payments (invoice_id, paid_at, method, reference, amount, note)
                         VALUES (?,?,?,?,?,?)");
    $st->execute([$invoiceId, $paidAt, $method, $reference, $amount, $note]);
    $paymentId = (int)$pdo->lastInsertId();

    \App\Models\Invoice::recalcPaidAmount($invoiceId);

    // Optional activity log
    if (function_exists('\App\Core\activity_log')) {
        \App\Core\activity_log('payment.add', 'sales_invoice', $invoiceId, [
            'payment_id' => $paymentId,
            'amount'     => $amount,
            'method'     => $method,
            'reference'  => $reference,
        ]);
    }

    flash_set('success', $capped ? 'Payment recorded (amount capped to remaining).' : 'Payment recorded.');
    redirect($return);
}
	
	public function index(): void
{
    require_auth();
    // Show latest payments with invoice and customer
    $sql = "SELECT p.*, i.inv_no, i.id AS invoice_id, c.name AS customer_name
            FROM invoice_payments p
            JOIN invoices i ON i.id = p.invoice_id
            JOIN customers c ON c.id = i.customer_id
            ORDER BY p.id DESC
            LIMIT 200";
    $rows = \App\Core\DB::conn()->query($sql)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    $this->view('payments/index', ['rows' => $rows]);
}

public function create(): void
{
    require_auth();
    $invoiceId = (int)($_GET['invoice_id'] ?? 0);
    $returnTo  = (string)($_GET['_return'] ?? '/invoices');
    $inv = \App\Models\Invoice::find($invoiceId);
    if (!$inv) {
        flash_set('error', 'Invoice not found.');
        redirect('/invoices');
    }
    $this->view('payments/create', [
        'i'        => $inv,
        'returnTo' => $returnTo ?: '/invoices/show?id='.$invoiceId,
        // default datetime-local value: now
        'now'      => date('Y-m-d\TH:i'),
    ]);
}
	public function destroy(): void {
    require_auth();
    if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/invoices'); }

    $id        = (int)($_POST['id'] ?? 0);
    $invoiceId = (int)($_POST['invoice_id'] ?? 0);
    $return    = (string)($_POST['_return'] ?? '/invoices');

    $inv = \App\Models\Invoice::find($invoiceId);
    if (!$inv) { flash_set('error','Invoice not found.'); redirect($return); }

    // Block edits once paid
    if (($inv['status'] ?? '') === 'paid') {
        flash_set('error','Invoice is fully paid; payments are locked.');
        redirect($return);
    }

    if ($id > 0) {
        \App\Core\DB::conn()->prepare("DELETE FROM invoice_payments WHERE id=?")->execute([$id]);

        \App\Models\Invoice::recalcPaidAmount($invoiceId);

        if (function_exists('\App\Core\activity_log')) {
            \App\Core\activity_log('payment.delete', 'sales_invoice', $invoiceId, ['payment_id' => $id]);
        }

        flash_set('success','Payment deleted.');
    }
    redirect($return);
}
}
