<?php
$handle = fopen('2026_2027 (2).sql', 'r');
if (!$handle) die("Could not open file\n");

$tableRows = [];
$currentTable = null;

while (($line = fgets($handle)) !== false) {
    if (preg_match('/-- Table structure for table `([^`]+)`/', $line, $m)) {
        $currentTable = $m[1];
        if (!isset($tableRows[$currentTable])) {
            $tableRows[$currentTable] = 0;
        }
    }
    if (preg_match('/INSERT INTO `([^`]+)`/i', $line, $m)) {
        $tbl = $m[1];
        // count how many rows roughly or count inserts
        $tableRows[$tbl] = ($tableRows[$tbl] ?? 0) + 1;
    }
}
fclose($handle);

echo "Tables and whether they have INSERTs in 2026_2027 (2).sql:\n";
foreach ($tableRows as $tbl => $count) {
    echo sprintf("%-45s : %d insert statements\n", $tbl, $count);
}
