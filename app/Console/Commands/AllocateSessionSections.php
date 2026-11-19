<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\backend\StudentTransferController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AllocateSessionSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:allocate-sections {session? : The target session database e.g. 2026_2027} {--class= : Optional specific class name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically assigns and balances student sections in the target session using Round Robin (Nursery-10) and stream mapping (Classes 11-12)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $targetSession = $this->argument('session');

        if (empty($targetSession)) {
            $currentDb = Config::get('database.connections.dynamic.database') ?: env('DB_DATABASE');
            if (preg_match('/^(\d{4})_(\d{4})$/', $currentDb, $matches)) {
                $startYear = (int)$matches[1];
                $targetSession = ($startYear + 1) . '_' . ($startYear + 2);
            } else {
                $targetSession = '2026_2027';
            }
        }

        $this->info("Starting automatic section allocation for target session: {$targetSession}");

        $classOption = $this->option('class');
        $targetClasses = !empty($classOption) ? [$classOption] : null;

        $controller = new StudentTransferController();
        $summary = $controller->allocateSectionsForSession($targetSession, $targetClasses);

        if (isset($summary['error'])) {
            $this->error($summary['error']);
            return Command::FAILURE;
        }

        $this->info("Section allocation complete. Summary:");
        foreach ($summary as $class => $info) {
            $this->line("- Class {$class} [{$info['type']}]: " . json_encode($info));
        }

        return Command::SUCCESS;
    }
}
