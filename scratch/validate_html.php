<?php
$content = file_get_contents('resources/views/backend/inquiryregistration.blade.php');
// strip PHP blocks
$content = preg_replace('/<\?php.*?\?>/s', '', $content);
$content = preg_replace('/\{\{.*?\}\}/s', '', $content);
$content = preg_replace('/\{!!.*?!!\}/s', '', $content);
$content = preg_replace('/@\w+.*?\n/', "\n", $content);
$content = preg_replace('/<!--.*?-->/s', '', $content);

$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML('<!DOCTYPE html><html><body>' . $content . '</body></html>');
$errors = libxml_get_errors();
libxml_clear_errors();

echo "Libxml errors: " . count($errors) . "\n";
foreach (array_slice($errors, 0, 15) as $err) {
    echo "Line {$err->line}: {$err->message}";
}
