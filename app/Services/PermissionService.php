<?php

namespace App\Services;

use App\Models\RoleMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class PermissionService
{
    /**
     * Unambiguous controller-to-menu mappings inferred from sidebar landing routes.
     *
     * @var array<string, string>|null
     */
    protected static $controllerModuleMap;

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
            'view' => [
                'senior-marksheet',
                'senior-marksheet.index',
                'academic/senior-marksheets',
                'academic.senior-marksheets.index',
                'academic.senior-marksheets.students-data',
                'academic.senior-marksheets.print',
                'academic.senior-marksheets.generated.bulk-print-page',
                'academic.senior-marksheets.generated.bulk-print',
                'academic.senior-marksheets.generated.print',
            ],
            'add' => [
                'academic/senior-marksheets/generate',
                'academic.senior-marksheets.generate',
            ],
            'edit' => [],
            'delete' => [
                'academic/senior-marksheets/generated/bulk-delete',
                'academic.senior-marksheets.generated.bulk-delete',
                'academic/senior-marksheets/generated',
                'academic.senior-marksheets.generated.delete',
            ],
        ],
        'marks' => [
            'menus' => [
                'Academic > Examination & Grading > Enter Marks',
            ],
            'view' => ['marks', 'show_report_marks', 'view-marks'],
            'add' => ['save-marks', 'store-marks'],
            'edit' => ['store-marks', 'lock-marks-entry', 'lock-current-marks-entry', 'bulk-lock-marks-entries', 'unlock-marks-entry', 'bulk-unlock-marks-entries'],
            'delete' => ['delete-marks'],
        ],
        'monthly-attendance' => [
            'menus' => [
                'Academic > Attendance > Monthly Attendance Summary',
                'Academic > Attendance > Lock Monthly Attendance',
            ],
            'view' => [
                'staff.attendance.monthly-report',
                'staff.attendance.monthly-lock',
                'academic.attendance.monthly-report',
                'academic/attendance-monthly-report',
                'academic.attendance.monthly-lock',
            ],
            'edit' => [
                'staff.attendance.monthly-lock.store',
                'academic.attendance.monthly-lock.unlock',
            ],
            'delete' => [],
        ],
    ];

    /**
     * Get the active authenticated user across web and staff guards.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function getCurrentUser()
    {
        $staffPortal = request()->cookie('auth_portal') === 'staff'
            || (request()->hasSession() && request()->session()->get('auth_portal') === 'staff');

        if ($staffPortal && Auth::guard('staff')->check()) {
            return Auth::guard('staff')->user();
        }
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }
        if (Auth::guard('staff')->check()) {
            return Auth::guard('staff')->user();
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

        if (self::isStaffPrincipal($user)) {
            return false;
        }

        if (isset($user->type) && strtolower(trim((string) $user->type)) !== 'a') {
            return false;
        }

        // An explicitly assigned administrator role grants the existing admin bypass.
        $roleName = strtolower(trim((string)($user->role ?? '')));
        if ($roleName === 'admin' || $roleName === 'super-admin') {
            return true;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('admin'))) {
            return true;
        }

        return isset($user->type)
            && strtolower(trim((string) $user->type)) === 'a';
    }

    /**
     * Determine whether a user instance is authenticated as a staff principal.
     *
     * @param  mixed  $user
     */
    protected static function isStaffPrincipal($user): bool
    {
        $staffUser = Auth::guard('staff')->user();
        if (!$staffUser || !method_exists($user, 'getAuthIdentifier')) {
            return false;
        }

        $sameUser = $staffUser->getAuthIdentifier() == $user->getAuthIdentifier();
        return $sameUser && get_class($staffUser) === get_class($user);
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
            $user = self::getCurrentUser();
            if (self::isAdmin($user)) {
                return ['*'];
            }
            $roleIdentifier = self::getRoleName($user);
        }

        if (!$roleIdentifier) {
            return [];
        }

        // Resolve role ID
        if (is_numeric($roleIdentifier)) {
            $roleId = (int) $roleIdentifier;
        } else {
            $roleIdCacheKey = 'role_menu_role_id.' . md5((string) $roleIdentifier);
            if (request()->attributes->has($roleIdCacheKey)) {
                $roleId = request()->attributes->get($roleIdCacheKey);
            } else {
                $roleId = DB::table('roles')->where('name', $roleIdentifier)->value('id');
                request()->attributes->set($roleIdCacheKey, $roleId ? (int) $roleId : null);
            }
        }

        if (!$roleId) {
            return [];
        }

        $cacheKey = 'role_menu_permissions.' . $roleId;
        if (request()->attributes->has($cacheKey)) {
            return request()->attributes->get($cacheKey);
        }

        $roleMenu = RoleMenu::where('role_id', $roleId)->first();
        if (!$roleMenu || empty($roleMenu->menu)) {
            request()->attributes->set($cacheKey, []);
            return [];
        }

        $raw = is_array($roleMenu->menu) ? $roleMenu->menu : json_decode($roleMenu->menu, true);
        if (!is_array($raw)) {
            request()->attributes->set($cacheKey, []);
            return [];
        }

        $normalized = [];

        // Legacy menu lists only represented visibility, not CRUD grants.
        if (isset($raw[0]) && is_string($raw[0])) {
            foreach ($raw as $menuTitle) {
                $normalized[trim($menuTitle)] = [
                    'view'   => true,
                    'add'    => false,
                    'edit'   => false,
                    'delete' => false,
                ];
            }
        } else {
            // New structured format: [menuTitle => [view => 1, add => 0, edit => 1, delete => 0]]
            foreach ($raw as $menuTitle => $actions) {
                if (is_array($actions)) {
                    $normalized[trim($menuTitle)] = [
                        'view'   => filter_var($actions['view'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'add'    => filter_var($actions['add'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'edit'   => filter_var($actions['edit'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'delete' => filter_var($actions['delete'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ];
                } elseif ($actions === true || $actions == 1) {
                    $normalized[trim($menuTitle)] = [
                        'view'   => true,
                        'add'    => false,
                        'edit'   => false,
                        'delete' => false,
                    ];
                }
            }
        }

        request()->attributes->set($cacheKey, $normalized);
        return $normalized;
    }

    /**
     * Clear the current request's role-menu cache after a role is updated.
     */
    public static function forgetRolePermissions(int $roleId): void
    {
        request()->attributes->remove('role_menu_permissions.' . $roleId);
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

        if (self::canViewOwnStaffRequests($action, $user)) {
            return true;
        }

        if (self::canSubmitOwnStaffLeaveRequest($action, $user)) {
            return true;
        }

        if (self::isAdmin($user)) {
            return true;
        }

        $action = self::normalizeAction($action);
        if ($action === null) {
            return false;
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
                if ($moduleKey === 'monthly-attendance') {
                    return collect($cfg['menus'])->contains(
                        fn ($menuTitle) => !empty($rolePermissions[$menuTitle][$action])
                    );
                }

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

        return false;
    }

    private static function canViewOwnStaffRequests(string $action, $user): bool
    {
        if (
            self::normalizeAction($action) !== 'view'
            || !Auth::guard('staff')->check()
            || Auth::guard('staff')->user() !== $user
            || (int) (Auth::guard('staff')->user()->employee_id ?? 0) <= 0
        ) {
            return false;
        }

        $request = request();
        $routeName = (string) optional($request->route())->getName();
        $path = trim($request->path(), '/');

        $isOwnRequestsPage = in_array($request->method(), ['GET', 'HEAD'], true)
            && (
                in_array($routeName, ['leaverequests.staff', 'compoffrequests.staff'], true)
                || in_array($path, ['leaverequests-staff', 'compoffrequests-staff'], true)
            );
        $isLeaveTypeLookup = $request->isMethod('POST')
            && $routeName === 'getLeaveTypes.staff';

        return $isOwnRequestsPage || $isLeaveTypeLookup;
    }

    private static function canSubmitOwnStaffLeaveRequest(string $action, $user): bool
    {
        if (
            self::normalizeAction($action) !== 'add'
            || !Auth::guard('staff')->check()
            || Auth::guard('staff')->user() !== $user
            || (int) (Auth::guard('staff')->user()->employee_id ?? 0) <= 0
        ) {
            return false;
        }

        $request = request();

        return $request->isMethod('POST')
            && (
                optional($request->route())->getName() === 'save-leaverequests.staff'
                || trim($request->path(), '/') === 'save-leaverequests-staff'
            );
    }

    private static function canViewOwnLeaveRequests(string $action, $user): bool
    {
        return self::canViewOwnStaffRequests($action, $user);
    }

    /**
     * Normalize common CRUD terminology to the action names stored in role_menus.
     */
    protected static function normalizeAction(string $action): ?string
    {
        $action = strtolower(trim($action));
        $aliases = [
            'create' => 'add',
            'store' => 'add',
            'update' => 'edit',
            'modify' => 'edit',
            'destroy' => 'delete',
        ];
        $action = $aliases[$action] ?? $action;

        return in_array($action, ['view', 'add', 'edit', 'delete'], true)
            ? $action
            : null;
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
                $itemRoutes = array_filter([
                    $item['route'] ?? null,
                    $item['staff_route'] ?? null,
                ]);

                if (
                    strcasecmp($item['title'], $routeOrTitle) === 0 ||
                    strcasecmp($currentKey, $routeOrTitle) === 0 ||
                    collect($itemRoutes)->contains(function ($itemRoute) use ($routeOrTitle) {
                        return strcasecmp($itemRoute, $routeOrTitle) === 0
                            || Str::is(trim($itemRoute, '/') . '*', trim($routeOrTitle, '/'));
                    })
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
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        $controllerMethod = $route ? strtolower((string) $route->getActionMethod()) : '';
        $path = trim($request->path(), '/');
        $method = strtoupper($request->method());
        $module = $route ? ($route->getAction('permission_module') ?: null) : null;
        $explicitAction = $route ? ($route->getAction('permission_action') ?: null) : null;

        if ($routeName === 'getLeaveTypes.staff') {
            $module = $module ?: 'HRMS > Leave Management > My Leave Requests';
            $explicitAction = $explicitAction ?: 'view';
        }

        if ($route) {
            foreach ($route->gatherMiddleware() as $middleware) {
                if (!Str::startsWith($middleware, 'check.permission:')) {
                    continue;
                }

                $parameters = explode(',', substr($middleware, strlen('check.permission:')), 2);
                if (count($parameters) === 2) {
                    $explicitAction = $explicitAction ?: trim($parameters[0]);
                    $module = $module ?: trim($parameters[1]);
                }
                break;
            }
        }

        $routeCandidates = array_unique(array_filter([
            $routeName,
            preg_replace('/^staff\./i', '', $routeName),
        ]));
        $pathCandidates = array_unique(array_filter([
            $path,
            preg_replace('/^staff\//i', '', $path),
        ]));
        foreach (array_merge($routeCandidates, $pathCandidates) as $candidate) {
            $withoutActionPrefix = preg_replace('/^(save|store|delete|destroy|view|get|fetch|update|create|add|edit)-/i', '', $candidate);
            $withoutActionSuffix = preg_replace('/-(delete|destroy|view|update|create|edit)$/i', '', $candidate);
            if ($withoutActionPrefix !== $candidate) {
                $routeCandidates[] = $withoutActionPrefix;
                $pathCandidates[] = $withoutActionPrefix;
            }
            if ($withoutActionSuffix !== $candidate) {
                $routeCandidates[] = $withoutActionSuffix;
                $pathCandidates[] = $withoutActionSuffix;
            }
        }
        $routeCandidates = array_unique(array_filter($routeCandidates));
        $pathCandidates = array_unique(array_filter($pathCandidates));

        $actionTokens = preg_split(
            '/[.\/_\-\s]+/',
            strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $routeName . ' ' . $path . ' ' . $controllerMethod)),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        // Resolve mutating controller actions before the HTTP-method fallback.
        $action = 'view';
        if ($method === 'DELETE' || array_intersect($actionTokens, ['delete', 'destroy', 'archive', 'remove', 'soft'])) {
            $action = 'delete';
        } elseif (
            $method === 'PUT'
            || $method === 'PATCH'
            || array_intersect($actionTokens, ['edit', 'update', 'modify'])
            || (!in_array($method, ['GET', 'HEAD'], true) && array_intersect($actionTokens, ['lock', 'unlock']))
        ) {
            $action = 'edit';
        } elseif (array_intersect($actionTokens, ['create', 'store', 'save', 'add', 'generate'])) {
            $action = 'add';
        } elseif ($method === 'POST') {
            $readOnlyActions = ['fetch', 'filter', 'search', 'report', 'export', 'print', 'check', 'validate', 'lookup', 'data', 'status'];
            $action = array_intersect($actionTokens, $readOnlyActions) ? 'view' : 'add';
        }
        $action = self::normalizeAction((string) ($explicitAction ?: $action)) ?: 'view';

        if ($module) {
            return ['module' => $module, 'action' => $action];
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
                foreach ($routeCandidates as $candidateRoute) {
                    if ($candidateRoute === $pattern) {
                        return ['module' => $cfg['menus'][0] ?? $moduleKey, 'action' => $action];
                    }
                }
                foreach ($pathCandidates as $candidatePath) {
                    if ($candidatePath === $pattern || Str::is(trim($pattern, '/') . '*', $candidatePath)) {
                        return ['module' => $cfg['menus'][0] ?? $moduleKey, 'action' => $action];
                    }
                }
            }
        }

        // 3. Fallback: match directly against sidebar routes
        foreach (array_merge($routeCandidates, $pathCandidates) as $candidate) {
            $matching = self::findMenuKeysForRouteOrTitle($candidate);
            if (!empty($matching)) {
                return ['module' => $matching[0], 'action' => $action];
            }
        }

        $controller = $route ? $route->getControllerClass() : null;
        $controllerModule = $controller ? self::getControllerModuleMap()[$controller] ?? null : null;
        if ($controllerModule) {
            return ['module' => $controllerModule, 'action' => $action];
        }

        // 4. Return the path itself as the module candidate
        return ['module' => $routeName ?: $path, 'action' => $action];
    }

    /**
     * Infer modules for legacy CRUD routes whose controller has one sidebar module.
     *
     * Controllers shared by multiple menu items are intentionally omitted.
     *
     * @return array<string, string>
     */
    protected static function getControllerModuleMap(): array
    {
        if (self::$controllerModuleMap !== null) {
            return self::$controllerModuleMap;
        }

        $routesByName = [];
        $routesByUri = [];
        foreach (Route::getRoutes() as $route) {
            if ($route->getName()) {
                $routesByName[$route->getName()] = $route;
            }
            if (in_array('GET', $route->methods(), true) || in_array('HEAD', $route->methods(), true)) {
                $routesByUri[$route->uri()] = $route;
            }
        }

        $controllerMenus = [];
        $walk = function (array $items, string $prefix = '') use (&$walk, &$controllerMenus, $routesByName, $routesByUri): void {
            foreach ($items as $item) {
                $key = $prefix ? $prefix . ' > ' . $item['title'] : $item['title'];
                if (!empty($item['children'])) {
                    $walk($item['children'], $key);
                    continue;
                }

                $target = $item['route'] ?? null;
                if (!$target) {
                    continue;
                }

                $route = $routesByName[$target] ?? $routesByUri[trim($target, '/')] ?? null;
                $controller = $route ? $route->getControllerClass() : null;
                if ($controller) {
                    $controllerMenus[$controller][$key] = true;
                }
            }
        };
        $walk(config('sidebar', []));

        self::$controllerModuleMap = [];
        foreach ($controllerMenus as $controller => $menus) {
            if (count($menus) === 1) {
                self::$controllerModuleMap[$controller] = array_key_first($menus);
            }
        }

        return self::$controllerModuleMap;
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

        $isStaffSelfLeaveRequestsUser = Auth::guard('staff')->check()
            && Auth::guard('staff')->user() === $user
            && (int) (Auth::guard('staff')->user()->employee_id ?? 0) > 0;

        $filterNode = function ($items, $prefix = '') use (&$filterNode, $permissions, $isStaffSelfLeaveRequestsUser) {
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
                if (
                    $isStaffSelfLeaveRequestsUser
                    && in_array($key, [
                        'HRMS > Leave Management > My Leave Requests',
                        'HRMS > Comp Off > My Comp Off Requests',
                    ], true)
                ) {
                    $hasView = true;
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
