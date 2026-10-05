<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING FULL REDIRECT FLOW ===\n\n";

$student = User::where('type', 's')->first();
if ($student) {
    Auth::guard('web')->setUser($student);
    
    $request = Request::create('/student-registrations', 'GET');
    $response = $app->handle($request);

    echo "Student Request to Admin URL /student-registrations:\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Redirect Location: " . $response->headers->get('Location') . "\n";
    echo "Flash Error Message: " . session('error') . "\n";
}
echo "========================================================\n";
