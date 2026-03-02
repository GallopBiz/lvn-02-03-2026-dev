<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncMissingFeeReceipts extends Command
{
    protected $signature = 'fees:sync-missing-receipts';
    protected $description = 'Trigger payment-success endpoint for missing fee receipts';

    public function handle()
    {
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

        foreach ($records as $row) {
            try {
                Http::post(config('services.payment.success_url'), [
                    'transaction_id' => $row->transaction_id,
                    'payment_gateway_transaction_id' => $row->payment_gateway_transaction_id,
                ]);

                $this->info("Triggered payment-success for TXN: {$row->transaction_id}");
            } catch (\Exception $e) {
                \Log::error('Payment success cron failed', [
                    'transaction_id' => $row->transaction_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
