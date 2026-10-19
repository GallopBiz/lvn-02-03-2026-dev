<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

Config::set('database.connections.dynamic.database', '2027_2028');
DB::purge('dynamic');

try {
    $controller = new \App\Http\Controllers\backend\InquiryEntryController(request());
    $view = $controller->admin_preenquiryform();
    $data = $view->getData();
    echo "Current Enquiry: " . $data['currentEnquiryNo'] . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
