<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                $this->createTableOnConnection($conn);
            } catch (\Throwable $e) {
                // Ignore connection errors if connection not configured
            }
        }

        // Migrate across active session databases (e.g., 2025_2026, 2026_2027)
        try {
            $databases = DB::select("SHOW DATABASES LIKE '20%'");
            foreach ($databases as $dbObj) {
                $dbName = array_values((array) $dbObj)[0];
                try {
                    $this->createTableOnDatabase($dbName);
                } catch (\Throwable $e) {
                    // Ignore database errors
                }
            }
        } catch (\Throwable $e) {
            // Ignore if SHOW DATABASES not permitted
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                Schema::connection($conn)->dropIfExists('refund_vouchers');
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        try {
            $databases = DB::select("SHOW DATABASES LIKE '20%'");
            foreach ($databases as $dbObj) {
                $dbName = array_values((array) $dbObj)[0];
                try {
                    DB::statement("DROP TABLE IF EXISTS `{$dbName}`.`refund_vouchers`");
                } catch (\Throwable $e) {
                    // Ignore
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    private function createTableOnConnection(string $conn): void
    {
        if (!Schema::connection($conn)->hasTable('refund_vouchers')) {
            Schema::connection($conn)->create('refund_vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('voucher_no', 60)->unique();
                $table->date('voucher_date');
                $table->unsignedBigInteger('student_id')->index();
                $table->string('scholar_no', 50)->index();
                $table->string('student_name', 150);
                $table->string('class_name', 50)->nullable();
                $table->string('section_name', 50)->nullable();
                $table->string('father_name', 150)->nullable();
                $table->unsignedBigInteger('tc_id')->nullable()->index();
                $table->boolean('is_discontinued')->default(false);
                $table->string('refund_head', 100)->default('CAUTION MONEY');
                $table->decimal('refundable_amount', 10, 2)->default(0.00);
                $table->decimal('refund_amount', 10, 2)->default(0.00);
                $table->string('payment_mode', 50)->default('Cheque');
                $table->string('cheque_no', 50)->nullable();
                $table->date('cheque_date')->nullable();
                $table->string('bank_name', 150)->nullable();
                $table->string('cash_account', 150)->nullable();
                $table->text('remarks')->nullable();
                $table->string('status', 30)->default('completed'); // completed, cancelled
                $table->text('cancel_reason')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->unsignedBigInteger('cancelled_by')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('created_by_name', 150)->nullable();
                $table->string('session_name', 50)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function createTableOnDatabase(string $dbName): void
    {
        $tableExists = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'refund_vouchers'", [$dbName]);
        if (empty($tableExists)) {
            DB::statement("
                CREATE TABLE IF NOT EXISTS `{$dbName}`.`refund_vouchers` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `voucher_no` VARCHAR(60) NOT NULL UNIQUE,
                    `voucher_date` DATE NOT NULL,
                    `student_id` BIGINT UNSIGNED NOT NULL,
                    `scholar_no` VARCHAR(50) NOT NULL,
                    `student_name` VARCHAR(150) NOT NULL,
                    `class_name` VARCHAR(50) NULL,
                    `section_name` VARCHAR(50) NULL,
                    `father_name` VARCHAR(150) NULL,
                    `tc_id` BIGINT UNSIGNED NULL,
                    `is_discontinued` TINYINT(1) NOT NULL DEFAULT 0,
                    `refund_head` VARCHAR(100) NOT NULL DEFAULT 'CAUTION MONEY',
                    `refundable_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                    `refund_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                    `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'Cheque',
                    `cheque_no` VARCHAR(50) NULL,
                    `cheque_date` DATE NULL,
                    `bank_name` VARCHAR(150) NULL,
                    `cash_account` VARCHAR(150) NULL,
                    `remarks` TEXT NULL,
                    `status` VARCHAR(30) NOT NULL DEFAULT 'completed',
                    `cancel_reason` TEXT NULL,
                    `cancelled_at` TIMESTAMP NULL,
                    `cancelled_by` BIGINT UNSIGNED NULL,
                    `created_by` BIGINT UNSIGNED NULL,
                    `created_by_name` VARCHAR(150) NULL,
                    `session_name` VARCHAR(50) NULL,
                    `created_at` TIMESTAMP NULL,
                    `updated_at` TIMESTAMP NULL,
                    `deleted_at` TIMESTAMP NULL,
                    INDEX `idx_student_id` (`student_id`),
                    INDEX `idx_scholar_no` (`scholar_no`),
                    INDEX `idx_tc_id` (`tc_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }
};
