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
        $amount = (float)($_POST['amount'] ?? 0);
        $paidAt = (string)($_POST['paid_at'] ?? '');
        $method = trim((string)($_POST['method'] ?? 'cash'));
        $reference = trim((string)($_POST['reference'] ?? ''));
        $note = trim((string)($_POST['note'] ?? ''));
        $return = (string)($_POST['_return'] ?? '/invoices');

        if ($invoiceId <= 0 || $amount <= 0 || $paidAt === '') {
            flash_set('error','Payment requires date and positive amount.');
            redirect($return);
        }

        $st = DB::conn()->prepare("INSERT INTO invoice_payments (invoice_id, paid_at, method, reference, amount, note)
                                   VALUES (?,?,?,?,?,?)");
        $st->execute([$invoiceId, $paidAt, $method, $reference, $amount, $note]);

        Invoice::recalcPaidAmount($invoiceId);

        flash_set('success','Payment recorded.');
        redirect($return);
    }

    public function destroy(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/invoices'); }
        $id = (int)($_POST['id'] ?? 0);
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $return = (string)($_POST['_return'] ?? '/invoices');
        if ($id > 0) {
            DB::conn()->prepare("DELETE FROM invoice_payments WHERE id=?")->execute([$id]);
            if ($invoiceId > 0) Invoice::recalcPaidAmount($invoiceId);
            flash_set('success','Payment deleted.');
        }
        redirect($return);
    }
}
