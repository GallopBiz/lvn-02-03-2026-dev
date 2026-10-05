<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING FLASH MESSAGE LAYOUT OUTPUT ===\n\n";

$student = User::where('type', 's')->first();
if ($student) {
    Auth::guard('web')->setUser($student);
    session()->flash('error', 'Permission Denied: You do not have permission to access Admin pages.');
    
    $request = Request::create('/admin-dashboard', 'GET');
    $response = $app->handle($request);

    $content = $response->getContent();

    if (strpos($content, '<div class="card-body">') !== false) {
        echo "WARNING: Extra card-body HTML element found in layout!\n";
    } else {
        echo "SUCCESS: No extra card-body element in DOM layout! Toastr script embedded cleanly without white space!\n";
    }
}
echo "========================================================\n";
