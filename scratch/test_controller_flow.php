<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\backend\InquiryEntryController;

echo "=== TEST 1: InquiryEntryController in 2026_2027 ===\n";
Config::set('database.connections.dynamic.database', '2026_2027');
DB::purge('dynamic');
DB::reconnect('dynamic');

$dummyReq = Request::create('/');
$controller = new InquiryEntryController($dummyReq);

// Simulate get-next-enquiry-number next_year=0
$reqCurrent = Request::create('/get-next-enquiry-number', 'GET', ['next_year' => 0]);
$resCurrent = $controller->get_next_enquiry_number($reqCurrent);
echo "get-next-enquiry-number (next_year=0): " . $resCurrent->getContent() . "\n";

// Simulate get-next-enquiry-number next_year=1
$reqNext = Request::create('/get-next-enquiry-number', 'GET', ['next_year' => 1]);
$resNext = $controller->get_next_enquiry_number($reqNext);
echo "get-next-enquiry-number (next_year=1): " . $resNext->getContent() . "\n";

// Test admin_preenquiryform view data
$view = $controller->admin_preenquiryform();
$viewData = $view->getData();
echo "admin_preenquiryform view data: currentEnquiryNo = {$viewData['currentEnquiryNo']}, nextYearEnquiryNo = {$viewData['nextYearEnquiryNo']}\n";

echo "\n=== TEST 2: InquiryEntryController in 2027_2028 ===\n";
Config::set('database.connections.dynamic.database', '2027_2028');
DB::purge('dynamic');
DB::reconnect('dynamic');

$controller27 = new InquiryEntryController($dummyReq);

$reqCurrent27 = Request::create('/get-next-enquiry-number', 'GET', ['next_year' => 0]);
$resCurrent27 = $controller27->get_next_enquiry_number($reqCurrent27);
echo "get-next-enquiry-number in 2027_2028 (next_year=0): " . $resCurrent27->getContent() . "\n";

$reqNext27 = Request::create('/get-next-enquiry-number', 'GET', ['next_year' => 1]);
$resNext27 = $controller27->get_next_enquiry_number($reqNext27);
echo "get-next-enquiry-number in 2027_2028 (next_year=1): " . $resNext27->getContent() . "\n";
