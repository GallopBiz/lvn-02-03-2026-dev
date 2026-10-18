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
        $tableRows[$tbl] = ($tableRows[$tbl] ?? 0) + 1;
    }
}
fclose($handle);

$zeroTables = [];
$dataTables = [];

foreach ($tableRows as $tbl => $count) {
    if ($count === 0) {
        $zeroTables[] = $tbl;
    } else {
        $dataTables[$tbl] = $count;
    }
}

file_put_contents('scratch/tables_summary.txt', 
    "=== TABLES WITH DATA (Count of INSERT statements) ===\n" .
    print_r($dataTables, true) . 
    "\n=== TABLES WITH ZERO DATA (Empty in 2026_2027) ===\n" .
    print_r($zeroTables, true)
);

echo "Summary written to scratch/tables_summary.txt\n";
echo "With data: " . count($dataTables) . ", Empty: " . count($zeroTables) . "\n";
