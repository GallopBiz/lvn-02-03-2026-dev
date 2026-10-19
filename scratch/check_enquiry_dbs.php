<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dbs = DB::select('SHOW DATABASES');
foreach ($dbs as $db) {
    $name = array_values((array)$db)[0];
    if (strpos($name, '20') === 0) {
        echo "Database: $name\n";
        try {
            $tables = DB::select("SHOW TABLES FROM `$name` LIKE 'inquiry_registration'");
            if (!empty($tables)) {
                $count = DB::table("$name.inquiry_registration")->count();
                $maxForm = DB::table("$name.inquiry_registration")->selectRaw("MAX(CAST(form_number AS UNSIGNED)) as m")->value('m');
                $nextYearCount = DB::table("$name.inquiry_registration")->where('next_year', 1)->count();
                $recent = DB::table("$name.inquiry_registration")
                    ->orderBy('id', 'desc')
                    ->limit(5)
                    ->get(['id', 'form_number', 'student_name', 'session_name', 'next_year', 'created_at']);
                echo "  inquiry_registration count: $count, max form_number: $maxForm, next_year=1 count: $nextYearCount\n";
                foreach ($recent as $r) {
                    echo "    ID: {$r->id}, form: {$r->form_number}, name: {$r->student_name}, session: {$r->session_name}, next_year: {$r->next_year}, created: {$r->created_at}\n";
                }
            } else {
                echo "  no inquiry_registration table\n";
            }
        } catch (\Exception $e) {
            echo "  error: " . $e->getMessage() . "\n";
        }
    }
}
