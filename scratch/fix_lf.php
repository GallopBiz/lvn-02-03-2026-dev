<?php
$f1 = 'app/Http/Controllers/MarksController.php';
file_put_contents($f1, str_replace("\r\n", "\n", file_get_contents($f1)));

$f2 = 'resources/views/backend/AcademicsModules/marks.blade.php';
file_put_contents($f2, str_replace("\r\n", "\n", file_get_contents($f2)));
echo "Normalized line endings.\n";
