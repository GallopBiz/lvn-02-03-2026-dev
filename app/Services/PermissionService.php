<?php

namespace App\Services;

use App\Models\RoleMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionService
{
    /**
     * Cache for role permissions during the current request lifecycle.
     *
     * @var array
     */
    protected static $rolePermissionsCache = [];

    /**
     * Map of common module aliases to sidebar menu titles / paths or route keywords.
     *
     * @var array
     */
    protected static $moduleRouteMap = [
        'student-registrations' => [
            'menus' => [
                'Scholars > Admission & Registration > Registration List',
                'Scholars > Admission & Registration > Student Registration',
            ],
            'view' => ['student-registrations', 'curren-year-student-registrations', 'registrationviewlist', 'getDataByFormNumber', 'getDataByFormNumberstudent_registration', 'export_registrations.csv', 'export_selection_process.csv', 'export.csv', 'exportdefaulterslist', 'downloadcertificate', 'student-registration-filter'],
            'add' => ['add-student-registrations', 'save-student-registration'],
            'edit' => ['registrationeditlist', 'update-student-registration', 'edit-student-registrations'],
            'delete' => ['archive-student', 'restore-student', 'student-delete-row', 'registrationdelete', 'archived-students'],
        ],
        'pre-enquiry' => [
            'menus' => [
                'Scholars > Pre-Admission > Pre-Enquiry Entry',
                'Scholars > Pre-Admission > Pre-Enquiry List',
            ],
            'view' => ['admin-pre-enquiryform', 'admin-preenquiryform-view'],
            'add' => ['admin-preenquiryform', 'save-pre-enquiry'],
            'edit' => ['admin-preenquiryform-edit', 'update-pre-enquiry'],
            'delete' => ['delete-pre-enquiry'],
        ],
        'enquiry' => [
            'menus' => [
                'Scholars > Pre-Admission > Enquiry Entry',
                'Scholars > Pre-Admission > Enquiry List',
            ],
            'view' => ['adminenquirylist', 'inquiry-data-show', 'inquiry-report'],
            'add' => ['admin-enquiryform', 'inquiry-data'],
            'edit' => ['inquiry-edit', 'inquiry-update'],
            'delete' => ['inquiry-delete'],
        ],
        'character-certificate' => [
            'menus' => [
                'Scholars > Certificates > Character Certificate',
            ],
            'view' => ['character-certificate', 'character-certificate.showForm', 'character-certificate-student-details', 'character-certificate.studentDetails'],
            'add' => ['generate-character-certificate', 'character-certificate.generateCertificate'],
            'edit' => ['generate-character-certificate', 'character-certificate.generateCertificate'],
            'delete' => [],
        ],
        'bonafide-certificate' => [
            'menus' => [
                'Scholars > Certificates > Bonafide Certificate',
            ],
            'view' => ['bonafide-certificate', 'bonafide.showForm', 'bonafide-students', 'bonafide.students'],
            'add' => ['generate-bonafide', 'bonafide.generateCertificate'],
            'edit' => ['generate-bonafide', 'bonafide.generateCertificate'],
            'delete' => [],
        ],
        'term1-marksheet' => [
            'menus' => [
                'Academic > Examination & Grading > Term 1 Report Card',
            ],
            'view' => ['academic/term1-marksheet', 'academic.term1-marksheet.index', 'academic/term1-marksheet/print', 'academic.term1-marksheet.print', 'academic/term1-marksheet/students-data', 'academic.term1-marksheet.students-data', 'academic/term1-marksheet/print/bulk', 'academic/term1-marksheet/print/'],
            'add' => ['academic/term1-marksheet/generate', 'academic.term1-marksheet.generate'],
            'edit' => ['academic/term1-marksheet/generate', 'academic.term1-marksheet.generate'],
            'delete' => ['academic/term1-marksheet/generated/bulk-delete', 'academic.term1-marksheet.generated.bulk-delete', 'academic/term1-marksheet/generated/delete', 'academic.term1-marksheet.generated.delete'],
        ],
        'senior-marksheets' => [
            'menus' => [
                'Academic > Examination & Grading > Class 11-12 Marksheet',
            ],
            'view' => ['academic/senior-marksheets', 'academic.senior-marksheets.index', 'academic/senior-marksheets/print'],
            'add' => ['academic/senior-marksheets/generate'],
            'edit' => ['academic/senior-marksheets/generate'],
            'delete' => ['academic/senior-marksheets/delete'],
        ],
        'marks' => [
            'menus' => [
                'Academic > Examination & Grading > Enter Marks',
            ],
            'view' => ['marks', 'show_report_marks', 'view-marks'],
            'add' => ['save-marks', 'store-marks'],
            'edit' => ['store-marks', 'unlock-marks-entry'],
            'delete' => ['delete-marks'],
        ],
    ];

    /**
     * Get the active authenticated user across web and staff guards.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function getCurrentUser()
    {
        if (Auth::guard('staff')->check()) {
            return Auth::guard('staff')->user();
        }
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }
        return Auth::user();
    }

    /**
     * Check if the user has administrative privileges (full bypass).
     *
     * @param  mixed  $user
     * @return bool
     */
    public static function isAdmin($user = null): bool
    {
        $user = $user ?: self::getCurrentUser();
        if (!$user) {
            return false;
        }

        // Web user type 'a' is Admin
        if (isset($user->type) && strtolower(trim((string)$user->type)) === 'a') {
            return true;
        }

        // Explicit role column 'Admin' or 'admin'
        $roleName = strtolower(trim((string)($user->role ?? '')));
        if ($roleName === 'admin' || $roleName === 'super-admin') {
            return true;
        }

        // Spatie HasRoles check
        if (method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('admin'))) {
            return true;
        }

        return false;
    }

    /**
     * Get the role name for a given user.
     *
     * @param  mixed  $user
     * @return string|null
     */
    public static function getRoleName($user = null): ?string
    {
        $user = $user ?: self::getCurrentUser();
        if (!$user) {
            return null;
        }

        if (self::isAdmin($user)) {
            return 'Admin';
        }

        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames();
            if ($roles && $roles->isNotEmpty()) {
                return $roles->first();
            }
        }

        return $user->role ?? null;
    }

    /**
     * Get the normalized permissions map for a role.
     *
     * @param  int|string|null  $roleIdentifier  Role ID or Role Name
     * @return array<string, array{view: bool, add: bool, edit: bool, delete: bool}>
     */
    public static function getPermissionsForRole($roleIdentifier = null): array
    {
        if ($roleIdentifier === null) {
            $roleName = self::getRoleName();
            if ($roleName === 'Admin') {
                return ['*']; // Full access
            }
            $roleIdentifier = $roleName;
        }

        if (!$roleIdentifier) {
            return [];
        }

        if (isset(self::$rolePermissionsCache[$roleIdentifier])) {
            return self::$rolePermissionsCache[$roleIdentifier];
        }

        // Resolve role ID
        $roleId = is_numeric($roleIdentifier)
            ? (int)$roleIdentifier
            : DB::table('roles')->where('name', $roleIdentifier)->value('id');

        if (!$roleId) {
            self::$rolePermissionsCache[$roleIdentifier] = [];
            return [];
        }

        $roleMenu = RoleMenu::where('role_id', $roleId)->first();
        if (!$roleMenu || empty($roleMenu->menu)) {
            self::$rolePermissionsCache[$roleIdentifier] = [];
            return [];
        }

        $raw = is_array($roleMenu->menu) ? $roleMenu->menu : json_decode($roleMenu->menu, true);
        if (!is_array($raw)) {
            self::$rolePermissionsCache[$roleIdentifier] = [];
            return [];
        }

        $normalized = [];

        // Check if legacy indexed array of strings e.g. ["Scholars > Student Registration", ...]
        if (isset($raw[0]) && is_string($raw[0])) {
            foreach ($raw as $menuTitle) {
                $normalized[trim($menuTitle)] = [
                    'view'   => true,
                    'add'    => true,
                    'edit'   => true,
                    'delete' => true,
                ];
            }
        } else {
            // New structured format: [menuTitle => [view => 1, add => 0, edit => 1, delete => 0]]
            foreach ($raw as $menuTitle => $actions) {
                if (is_array($actions)) {
                    $normalized[trim($menuTitle)] = [
                        'view'   => !empty($actions['view']),
                        'add'    => !empty($actions['add']),
                        'edit'   => !empty($actions['edit']),
                        'delete' => !empty($actions['delete']),
                    ];
                } elseif ($actions === true || $actions == 1) {
                    $normalized[trim($menuTitle)] = [
                        'view'   => true,
                        'add'    => true,
                        'edit'   => true,
                        'delete' => true,
                    ];
                }
            }
        }

        self::$rolePermissionsCache[$roleIdentifier] = $normalized;
        return $normalized;
    }

    /**
     * Check if the current user has permission for an action in a module / menu.
     *
     * @param  string  $action  'view' | 'add' | 'edit' | 'delete'
     * @param  string|null  $moduleOrRoute  Menu title, route name, or URL path
     * @param  mixed  $user
     * @return bool
     */
    public static function can(string $action, ?string $moduleOrRoute = null, $user = null): bool
    {
        $user = $user ?: self::getCurrentUser();
        if (!$user) {
            return false;
        }

        if (self::isAdmin($user)) {
            return true;
        }

        $action = strtolower(trim($action));
        if (!in_array($action, ['view', 'add', 'edit', 'delete'])) {
            $action = 'view';
        }

        $rolePermissions = self::getPermissionsForRole(self::getRoleName($user));
        if ($rolePermissions === ['*']) {
            return true;
        }

        // If no module is passed, auto-detect from the current request
        if ($moduleOrRoute === null) {
            $request = request();
            $detected = self::detectModuleAndActionFromRequest($request);
            $moduleOrRoute = $detected['module'];
            if (!$action) {
                $action = $detected['action'];
            }
        }

        if (!$moduleOrRoute) {
            return false;
        }

        $moduleOrRoute = trim($moduleOrRoute);

        // 1. Direct match on menu title in role permissions
        if (isset($rolePermissions[$moduleOrRoute])) {
            return !empty($rolePermissions[$moduleOrRoute][$action]);
        }

        // 2. Check predefined moduleRouteMap
        foreach (self::$moduleRouteMap as $moduleKey => $cfg) {
            $matchesModule = ($moduleOrRoute === $moduleKey)
                || in_array($moduleOrRoute, $cfg['menus'], true)
                || in_array($moduleOrRoute, $cfg['view'] ?? [], true)
                || in_array($moduleOrRoute, $cfg['add'] ?? [], true)
                || in_array($moduleOrRoute, $cfg['edit'] ?? [], true)
                || in_array($moduleOrRoute, $cfg['delete'] ?? [], true);

            if ($matchesModule) {
                foreach ($cfg['menus'] as $menuTitle) {
                    if (isset($rolePermissions[$menuTitle])) {
                        return !empty($rolePermissions[$menuTitle][$action]);
                    }
                }
            }
        }

        // 3. Match against sidebar config by route or title
        $matchingMenuKeys = self::findMenuKeysForRouteOrTitle($moduleOrRoute);
        foreach ($matchingMenuKeys as $key) {
            if (isset($rolePermissions[$key])) {
                return !empty($rolePermissions[$key][$action]);
            }
        }

        // 4. Fallback: match by partial title / path suffix
        foreach ($rolePermissions as $menuTitle => $actions) {
            if (Str::is('*' . $moduleOrRoute, $menuTitle) || Str::is($moduleOrRoute . '*', $menuTitle)) {
                return !empty($actions[$action]);
            }
        }

        return false;
    }

    /**
     * Find matching sidebar menu keys for a given route name or title.
     *
     * @param  string  $routeOrTitle
     * @return array<string>
     */
    protected static function findMenuKeysForRouteOrTitle(string $routeOrTitle): array
    {
        $sidebar = config('sidebar', []);
        $results = [];

        $walker = function ($items, $prefix = '') use (&$walker, &$results, $routeOrTitle) {
            foreach ($items as $item) {
                $currentKey = $prefix ? $prefix . ' > ' . $item['title'] : $item['title'];
                $itemRoute = $item['route'] ?? null;

                if (
                    strcasecmp($item['title'], $routeOrTitle) === 0 ||
                    strcasecmp($currentKey, $routeOrTitle) === 0 ||
                    ($itemRoute && (
                        strcasecmp($itemRoute, $routeOrTitle) === 0 ||
                        Str::is(trim($itemRoute, '/') . '*', trim($routeOrTitle, '/'))
                    ))
                ) {
                    $results[] = $currentKey;
                }

                if (!empty($item['children'])) {
                    $walker($item['children'], $currentKey);
                }
            }
        };

        $walker($sidebar);
        return $results;
    }

    /**
     * Detect the module and action from an incoming HTTP Request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array{module: string, action: string}
     */
    public static function detectModuleAndActionFromRequest(Request $request): array
    {
        $routeName = $request->route() ? $request->route()->getName() : '';
        $path = trim($request->path(), '/');
        $method = strtoupper($request->method());

        // 1. Detect Action
        $action = 'view';
        if ($method === 'DELETE') {
            $action = 'delete';
        } elseif (
            Str::contains($path, ['delete', 'destroy', 'archive', 'remove', 'soft_delete']) ||
            ($routeName && Str::contains($routeName, ['delete', 'destroy', 'archive', 'remove']))
        ) {
            $action = 'delete';
        } elseif (
            Str::contains($path, ['edit', 'update', 'modify']) ||
            ($routeName && Str::contains($routeName, ['edit', 'update', 'modify']))
        ) {
            $action = 'edit';
        } elseif (
            ($method === 'POST' || $method === 'PUT') &&
            (
                Str::contains($path, ['save', 'store', 'create', 'add', 'generate']) ||
                ($routeName && Str::contains($routeName, ['save', 'store', 'create', 'add', 'generate']))
            )
        ) {
            $action = 'add';
        } elseif (
            Str::contains($path, ['create', 'add']) ||
            ($routeName && Str::contains($routeName, ['create', 'add']))
        ) {
            $action = 'add';
        }

        // 2. Detect Module from predefined moduleRouteMap
        foreach (self::$moduleRouteMap as $moduleKey => $cfg) {
            $allRoutes = array_merge(
                $cfg['view'] ?? [],
                $cfg['add'] ?? [],
                $cfg['edit'] ?? [],
                $cfg['delete'] ?? []
            );

            foreach ($allRoutes as $pattern) {
                if ($routeName === $pattern || $path === $pattern || Str::is(trim($pattern, '/') . '*', $path)) {
                    return ['module' => $cfg['menus'][0] ?? $moduleKey, 'action' => $action];
                }
            }
        }

        // 3. Fallback: match directly against sidebar routes
        $matching = self::findMenuKeysForRouteOrTitle($routeName ?: $path);
        if (!empty($matching)) {
            return ['module' => $matching[0], 'action' => $action];
        }

        // 4. Return the path itself as the module candidate
        return ['module' => $routeName ?: $path, 'action' => $action];
    }

    /**
     * Filter sidebar items so that staff members only see menus where they have 'view' permission.
     *
     * @param  array  $sidebar
     * @param  mixed  $user
     * @return array
     */
    public static function filterSidebarForUser(array $sidebar, $user = null): array
    {
        $user = $user ?: self::getCurrentUser();
        if (!$user) {
            return [];
        }

        if (self::isAdmin($user)) {
            return $sidebar; // Admin sees all
        }

        $permissions = self::getPermissionsForRole(self::getRoleName($user));

        $filterNode = function ($items, $prefix = '') use (&$filterNode, $permissions) {
            $filtered = [];

            foreach ($items as $item) {
                $key = $prefix ? $prefix . ' > ' . $item['title'] : $item['title'];
                $itemFiltered = $item;

                // Process children recursively
                if (!empty($item['children'])) {
                    $visibleChildren = $filterNode($item['children'], $key);
                    if (!empty($visibleChildren)) {
                        $itemFiltered['children'] = $visibleChildren;
                        $filtered[] = $itemFiltered;
                        continue;
                    }
                }

                // Leaf item: check if view permission is granted
                $hasView = false;
                if (isset($permissions[$key])) {
                    $hasView = !empty($permissions[$key]['view']);
                }

                // If menu item has child but no children were visible, don't show parent
                if (empty($item['children']) && $hasView) {
                    $filtered[] = $itemFiltered;
                }
            }

            return $filtered;
        };

        return $filterNode($sidebar);
    }
}

