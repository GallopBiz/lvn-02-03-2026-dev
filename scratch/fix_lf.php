<?php
$f3 = 'resources/views/backend/AcademicsModules/showmarks.blade.php';
file_put_contents($f3, str_replace("\r\n", "\n", file_get_contents($f3)));
echo "Normalized showmarks.blade.php line endings.\n";
