<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Middleware\EnsureAdmin;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING AJAX REQUEST SECURITY ===\n\n";

$middleware = new EnsureAdmin();

$student = User::where('type', 's')->first();
if ($student) {
    Auth::guard('web')->setUser($student);
    
    $request = Request::create('/student-registrations', 'GET', [], [], [], ['HTTP_X-REQUESTED-WITH' => 'XMLHttpRequest']);
    $response = $middleware->handle($request, function ($req) {
        return response("ALLOWED", 200);
    });

    echo "Student AJAX Request to /student-registrations:\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "JSON Response: " . $response->getContent() . "\n";
}
echo "========================================================\n";
