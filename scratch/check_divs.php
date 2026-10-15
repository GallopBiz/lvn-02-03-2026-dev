<?php
$content = file_get_contents('resources/views/backend/inquiryregistration.blade.php');
$content = preg_replace('/<!--.*?-->/s', '', $content);
$lines = explode("\n", $content);
$stack = [];

foreach ($lines as $lineNum => $line) {
    preg_match_all('/<(\/?)div\b([^>]*)>/i', $line, $matches, PREG_OFFSET_CAPTURE);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $idx => $match) {
            $isClose = ($matches[1][$idx][0] === '/');
            $attrs = $matches[2][$idx][0];
            if ($isClose) {
                if (empty($stack)) {
                    echo "EXTRA </div> at line " . ($lineNum + 1) . "\n";
                } else {
                    array_pop($stack);
                }
            } else {
                $stack[] = [
                    'line' => $lineNum + 1,
                    'attrs' => trim($attrs)
                ];
            }
        }
    }
}

echo "Remaining unclosed divs: " . count($stack) . "\n";
foreach ($stack as $item) {
    echo "Unclosed <div " . $item['attrs'] . "> from line " . $item['line'] . "\n";
}
