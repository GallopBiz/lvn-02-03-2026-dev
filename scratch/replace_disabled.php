<?php
$f = file_get_contents('resources/views/backend/preinquiryregistration.blade.php');
$target = 'type="text" disabled';
if (strpos($f, $target) !== false) {
    echo "Found target!\n";
    $f = str_replace($target, 'type="text" readonly style="background-color: #e9ecef; cursor: not-allowed;"', $f);
    file_put_contents('resources/views/backend/preinquiryregistration.blade.php', $f);
    echo "Replaced successfully!\n";
} else {
    echo "Target not found.\n";
}
