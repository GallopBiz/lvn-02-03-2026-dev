<?php

use App\Services\PermissionService;

if (!function_exists('can_action')) {
    /**
     * Check if the authenticated user has permission for a specific action on a module.
     *
     * @param  string  $action  'view' | 'add' | 'edit' | 'delete'
     * @param  string|null  $moduleOrRoute
     * @return bool
     */
    function can_action(string $action, ?string $moduleOrRoute = null): bool
    {
        return PermissionService::can($action, $moduleOrRoute);
    }
}

if (!function_exists('can_view')) {
    function can_view(?string $moduleOrRoute = null): bool
    {
        return PermissionService::can('view', $moduleOrRoute);
    }
}

if (!function_exists('can_add')) {
    function can_add(?string $moduleOrRoute = null): bool
    {
        return PermissionService::can('add', $moduleOrRoute);
    }
}

if (!function_exists('can_edit')) {
    function can_edit(?string $moduleOrRoute = null): bool
    {
        return PermissionService::can('edit', $moduleOrRoute);
    }
}

if (!function_exists('can_delete')) {
    function can_delete(?string $moduleOrRoute = null): bool
    {
        return PermissionService::can('delete', $moduleOrRoute);
    }
}

if (!function_exists('is_admin_user')) {
    function is_admin_user($user = null): bool
    {
        return PermissionService::isAdmin($user);
    }
}

if (!function_exists('correct_caution_money_display')) {
    /**
     * Correct misspelled 'Caution Money' strings for display purposes only.
     * Does NOT alter underlying database values or calculation keys.
     *
     * @param string|null $text
     * @return string
     */
    function correct_caution_money_display(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // Replace common misspellings such as CUATION MONEY, Cuation, CUATION, etc.
        $patterns = [
            '/\bcuat?ion\s*money\b/i' => 'Caution Money',
            '/\bcuat?ion\b/i'        => 'Caution',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $text = preg_replace_callback($pattern, function ($matches) use ($replacement) {
                // If the matched string was all uppercase, return replacement in uppercase
                if (ctype_upper(preg_replace('/[^a-zA-Z]/', '', $matches[0]))) {
                    return strtoupper($replacement);
                }
                return $replacement;
            }, $text);
        }

        return $text;
    }
}

