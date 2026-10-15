<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

View::share('errors', new \Illuminate\Support\ViewErrorBag());

// Mock request URL to be admin-enquiryform
$request = Illuminate\Http\Request::create('/admin-enquiryform', 'GET');
$app->instance('request', $request);

try {
    $data = [
        'states' => collect([ (object)['id' => 1, 'name' => 'MP'] ]),
        'caste' => collect([ (object)['caste_name' => 'General'] ]),
        'presentlyschool' => collect([ (object)['school_name' => 'ABC School'] ]),
        'all_inquiry' => collect([]),
        'Userid' => 101,
        'classlist' => collect([ (object)['class_name' => 'I'] ]),
        'Remarks' => collect([]),
    ];
    $rendered = view('backend.inquiryregistration', $data)->render();
    echo "Blade rendered successfully! Length: " . strlen($rendered) . "\n";

    // Extract all <script tags
    preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $rendered, $matches);
    echo "Found " . count($matches[0]) . " script tags.\n";
    foreach ($matches[0] as $idx => $scriptTag) {
        if (preg_match('/src=["\']([^"\']+)["\']/', $scriptTag, $srcMatch)) {
            echo "Script " . ($idx+1) . " [SRC]: " . $srcMatch[1] . "\n";
        } else {
            $snippet = trim(strip_tags($scriptTag));
            $firstLine = strtok($snippet, "\n");
            echo "Script " . ($idx+1) . " [INLINE]: " . substr($firstLine, 0, 70) . "...\n";
        }
    }
    $content = file_get_contents('resources/views/backend/inquiryregistration.blade.php');
    echo "div opens: " . substr_count($content, '<div') . " closes: " . substr_count($content, '</div>') . "\n";
    echo "form opens: " . substr_count($content, '<form') . " closes: " . substr_count($content, '</form>') . "\n";
} catch (\Exception $e) {
    echo "Render Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
