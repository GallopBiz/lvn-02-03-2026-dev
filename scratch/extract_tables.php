<?php
$handle = fopen('2026_2027 (2).sql', 'r');
if (!$handle) {
    die("Could not open file\n");
}

$tables = [];
while (($line = fgets($handle)) !== false) {
    if (preg_match('/CREATE TABLE `([^`]+)`/', $line, $matches)) {
        $tables[] = $matches[1];
    }
}
fclose($handle);

echo "Total tables found: " . count($tables) . "\n";
sort($tables);
foreach ($tables as $t) {
    echo $t . "\n";
}
