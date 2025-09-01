<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Supplier;

use function App\Core\require_auth;

final class ReportsController extends Controller
{
    public function apaging(): void {
        require_auth();
        $asof = $_GET['asof'] ?? date('Y-m-d');
        $rows = Supplier::apAgingSnapshot($asof);

        // totals
        $totals = ['b0'=>0,'b31'=>0,'b61'=>0,'b90'=>0,'total'=>0];
        foreach ($rows as $r) {
            $totals['b0']   += (float)$r['bucket_0_30'];
            $totals['b31']  += (float)$r['bucket_31_60'];
            $totals['b61']  += (float)$r['bucket_61_90'];
            $totals['b90']  += (float)$r['bucket_90_plus'];
            $totals['total']+= (float)$r['total'];
        }

        $this->view('reports/ap_aging', [
            'asof'   => $asof,
            'rows'   => $rows,
            'totals' => $totals,
        ]);
    }
}
