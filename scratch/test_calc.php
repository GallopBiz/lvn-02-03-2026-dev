<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function testEnquiryCalculation($currentDb) {
    Config::set('database.connections.dynamic.database', $currentDb);
    DB::purge('dynamic');
    DB::reconnect('dynamic');

    $prevDb = null;
    $nextDb = null;
    if (preg_match('/^(\d{4})_(\d{4})$/', $currentDb, $matches)) {
        $startYear = (int)$matches[1];
        $endYear = (int)$matches[2];
        $prevDbCand = ($startYear - 1) . '_' . ($endYear - 1);
        $nextDbCand = ($startYear + 1) . '_' . ($endYear + 1);

        try {
            $chk = DB::select("SHOW TABLES FROM `{$prevDbCand}` LIKE 'inquiry_registration'");
            if (!empty($chk)) $prevDb = $prevDbCand;
        } catch (\Exception $e) {}

        try {
            $chk = DB::select("SHOW TABLES FROM `{$nextDbCand}` LIKE 'inquiry_registration'");
            if (!empty($chk)) $nextDb = $nextDbCand;
        } catch (\Exception $e) {}
    }

    // 1. Current Session Sequence (next_year = 0 / unchecked):
    $currMax = DB::connection('dynamic')->table('inquiry_registration')
        ->where(function($q) {
            $q->whereNull('next_year')
              ->orWhere('next_year', 0)
              ->orWhere('next_year', '0')
              ->orWhere('next_year', '');
        })
        ->where(function($q) {
            $q->where('is_delete', 0)->orWhereNull('is_delete');
        })
        ->whereRaw('CAST(form_number AS UNSIGNED) < 1000')
        ->selectRaw('MAX(CAST(form_number AS UNSIGNED)) as max_num')
        ->value('max_num') ?: 0;

    $prevMax = 0;
    if ($prevDb) {
        try {
            $prevMax = DB::table("{$prevDb}.inquiry_registration")
                ->where(function($q) {
                    $q->where('next_year', 1)->orWhere('next_year', '1');
                })
                ->where(function($q) {
                    $q->where('is_delete', 0)->orWhereNull('is_delete');
                })
                ->whereRaw('CAST(form_number AS UNSIGNED) < 1000')
                ->selectRaw('MAX(CAST(form_number AS UNSIGNED)) as max_num')
                ->value('max_num') ?: 0;
        } catch (\Exception $e) {}
    }

    $maxCurrentOverall = max((int)$currMax, (int)$prevMax);
    $currentEnquiryNo = $maxCurrentOverall > 0 ? ($maxCurrentOverall + 1) : 1;

    // 2. Next Year Sequence (next_year = 1 / checked):
    $currNextMax = DB::connection('dynamic')->table('inquiry_registration')
        ->where(function($q) {
            $q->where('next_year', 1)->orWhere('next_year', '1');
        })
        ->where(function($q) {
            $q->where('is_delete', 0)->orWhereNull('is_delete');
        })
        ->whereRaw('CAST(form_number AS UNSIGNED) < 1000')
        ->selectRaw('MAX(CAST(form_number AS UNSIGNED)) as max_num')
        ->value('max_num') ?: 0;

    $nextSessionMax = 0;
    if ($nextDb) {
        try {
            $nextSessionMax = DB::table("{$nextDb}.inquiry_registration")
                ->where(function($q) {
                    $q->where('is_delete', 0)->orWhereNull('is_delete');
                })
                ->whereRaw('CAST(form_number AS UNSIGNED) < 1000')
                ->selectRaw('MAX(CAST(form_number AS UNSIGNED)) as max_num')
                ->value('max_num') ?: 0;
        } catch (\Exception $e) {}
    }

    $maxNextOverall = max((int)$currNextMax, (int)$nextSessionMax);
    $nextYearEnquiryNo = $maxNextOverall > 0 ? ($maxNextOverall + 1) : 1;

    echo "Session: $currentDb (prev: $prevDb, next: $nextDb)\n";
    echo "  Current Enquiry No (next_year=0): $currentEnquiryNo (currMax: $currMax, prevMax: $prevMax)\n";
    echo "  Next Year Enquiry No (next_year=1): $nextYearEnquiryNo (currNextMax: $currNextMax, nextSessionMax: $nextSessionMax)\n";
}

testEnquiryCalculation('2025_2026');
testEnquiryCalculation('2026_2027');
testEnquiryCalculation('2027_2028');
