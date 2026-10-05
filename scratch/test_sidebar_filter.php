<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$configSidebar = config('sidebar');

function filterMenuByRoleNameTest($menu, $roleName) {
    $filtered = [];
    foreach ($menu as $item) {
        $roles = $item['roles'] ?? [];

        // 1. If explicit roles are defined on this item, roleName MUST be in $roles
        if (!empty($roles) && !in_array($roleName, $roles)) {
            continue;
        }

        $filteredItem = $item;
        if (!empty($item['children'])) {
            $children = filterMenuByRoleNameTest($item['children'], $roleName);
            // If item has children, but none of the children are allowed for this role, skip this item
            if (empty($children)) {
                continue;
            }
            $filteredItem['children'] = $children;
        }

        $filtered[] = $filteredItem;
    }
    return $filtered;
}

$studentMenu = filterMenuByRoleNameTest($configSidebar, 'Student');
echo "=== STUDENT MENU OUTPUT ===\n";
echo json_encode($studentMenu, JSON_PRETTY_PRINT);
