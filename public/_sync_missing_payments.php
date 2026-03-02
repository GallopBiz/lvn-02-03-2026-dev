<?php

/* ===========================
   BASIC SECURITY (IMPORTANT)
   =========================== */

// simple token protection
$token = $_GET['token'] ?? null;
if ($token !== 'RUN_SYNC_2026') {
    http_response_code(403);
    die('Forbidden');
}

/* ===========================
   BOOTSTRAP LARAVEL
   =========================== */

$basePath = realpath(__DIR__ . '/../../'); // Laravel root

if (!$basePath) {
    die('Laravel base path not found');
}

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/* ===========================
   FETCH MISSING RECORDS
   =========================== */

$records = DB::select("
    SELECT DISTINCT
        sop.transaction_id,
        sop.payment_gateway_transaction_id
    FROM student_online_fee_payments sop
    LEFT JOIN feesreceiptchallan frc
        ON frc.old_fee_rec_no COLLATE utf8mb4_unicode_ci =
           CONCAT(
               CAST(sop.transaction_id AS CHAR),
               '',
               CAST(sop.payment_gateway_transaction_id AS CHAR)
           ) COLLATE utf8mb4_unicode_ci
    WHERE sop.status = 'paid'
      AND frc.old_fee_rec_no IS NULL
");

/* ===========================
   PROCESS RECORDS
   =========================== */

$controller = app()->make(\App\Http\Controllers\PaymentController::class);

$successCount = 0;
$failCount = 0;

foreach ($records as $row) {
    try {
        $ok = $controller->paymentSuccessInternal(
            $row->transaction_id,
            $row->payment_gateway_transaction_id
        );

        if ($ok) {
            echo "✅ Processed: {$row->transaction_id}<br>";
            $successCount++;
        } else {
            echo "❌ Failed: {$row->transaction_id}<br>";
            $failCount++;
        }

    } catch (\Throwable $e) {
        Log::error('Manual sync failed', [
            'transaction_id' => $row->transaction_id,
            'error' => $e->getMessage()
        ]);

        echo "❌ Error: {$row->transaction_id}<br>";
        $failCount++;
    }
}

echo "<hr>";
echo "<strong>Completed</strong><br>";
echo "Success: {$successCount}<br>";
echo "Failed: {$failCount}<br>";
