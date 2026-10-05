<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING REVISED AUTH & DASHBOARD FLOW ===\n\n";

$student = User::where('type', 's')->first();
if ($student) {
    Auth::guard('web')->setUser($student);
    
    // Test 1: Student opens /admin-dashboard
    $request1 = Request::create('/admin-dashboard', 'GET');
    $response1 = $app->handle($request1);

    echo "1. Student opening /admin-dashboard:\n";
    echo "Status Code: " . $response1->getStatusCode() . "\n";
    if ($response1->getStatusCode() === 200) {
        echo "SUCCESS: Dashboard loaded for student!\n";
    }

    echo "--------------------------------------------------------\n";

    // Test 2: Student tries to open /student-registrations directly
    $request2 = Request::create('/student-registrations', 'GET');
    $response2 = $app->handle($request2);

    echo "2. Student attempting to open Admin URL /student-registrations:\n";
    echo "Status Code: " . $response2->getStatusCode() . "\n";
    echo "Redirect Location: " . $response2->headers->get('Location') . "\n";
    echo "Flash Error Message: " . session('error') . "\n";
}
echo "========================================================\n";
