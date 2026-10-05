@php
$adminUser = Auth::user();
$staffUser = Auth::guard('staff')->user();
@endphp

<div class="side-content-wrap">

	{{-- LEFT SIDEBAR --}}
	<div class="sidebar-left open rtl-ps-none"
		data-perfect-scrollbar
		data-suppress-scroll-x="true">

		<ul class="navigation-left">

			@php
			if (!function_exists('isStaffRouteRequest')) {
				function isStaffRouteRequest() {
					return request()->is('staff*') || request()->is('*staff*') || request()->routeIs('staff.*');
				}
			}

			if (!function_exists('getSidebarUser')) {
				function getSidebarUser() {
					if (isStaffRouteRequest() && Auth::guard('staff')->check()) {
						return Auth::guard('staff')->user();
					}
					if (Auth::guard('web')->check()) {
						return Auth::guard('web')->user();
					}
					if (Auth::guard('staff')->check()) {
						return Auth::guard('staff')->user();
					}
					return auth()->user();
				}
			}

			$user = getSidebarUser();

			// Check if current logged-in user is a Student (web guard user where type != 'a')
			$isStudentUser = Auth::guard('web')->check() && strtolower(trim(Auth::guard('web')->user()->type ?? '')) !== 'a';

			if ($isStudentUser) {
				$isAdmin = false;
				$roleName = 'Student';
			} else {
				$roleName = null;
				if ($user && method_exists($user, 'getRoleNames')) {
					$roleName = $user->getRoleNames()->first();
				}
				if (empty($roleName) && $user) {
					$roleName = $user->role ?? null;
				}
				$isAdmin = $user && (
					($roleName === 'Admin') ||
					(method_exists($user, 'hasRole') && $user->hasRole('Admin')) ||
					(isset($user->type) && strtolower(trim($user->type)) === 'a')
				);
			}

			function getAllowedMenuForUser($user, $roleName = null, $isStudentUser = false) {
				if (!$user) return [];
				if (!$roleName) {
					$roleName = getRoleNameForUser($user);
				}
				if ($isStudentUser || !$roleName || $roleName === 'Student') return []; // Students do not use DB role_menus
				$roleId = \DB::table('roles')->where('name', $roleName)->value('id');
				if (!$roleId) return [];
				$roleMenu = \App\Models\RoleMenu::where('role_id', $roleId)->first();
				return $roleMenu ? json_decode($roleMenu->menu, true) : [];
			}

			function filterMenuByAllowed($menu, $allowed, $prefix = '') {
				$filtered = [];
				foreach ($menu as $item) {
					$key = $prefix . $item['title'];
					if (in_array($key, $allowed)) {
						$filteredItem = $item;
						if (!empty($item['children'])) {
							$filteredItem['children'] = filterMenuByAllowed($item['children'], $allowed, $key . ' > ');
						}
						$filtered[] = $filteredItem;
					} elseif (!empty($item['children'])) {
						$children = filterMenuByAllowed($item['children'], $allowed, $key . ' > ');
						if ($children) {
							$filteredItem = $item;
							$filteredItem['children'] = $children;
							$filtered[] = $filteredItem;
						}
					}
				}
				return $filtered;
			}

			function filterMenuByRoleName($menu, $roleName) {
				$filtered = [];
				foreach ($menu as $item) {
					$roles = $item['roles'] ?? [];

					// Explicit roles check: roleName MUST be in $roles if $roles is non-empty
					if (!empty($roles) && !in_array($roleName, $roles)) {
						continue;
					}

					$filteredItem = $item;
					if (!empty($item['children'])) {
						$children = filterMenuByRoleName($item['children'], $roleName);
						if (empty($children)) {
							continue;
						}
						$filteredItem['children'] = $children;
					}

					$filtered[] = $filteredItem;
				}
				return $filtered;
			}

			function menuItemUrl($item) {
				if (!empty($item['children'])) {
					return '#';
				}

				$isStaffUser = isStaffRouteRequest() || (Auth::guard('staff')->check() && !Auth::guard('web')->check());
				$route = $isStaffUser && isset($item['staff_route'])
					? $item['staff_route']
					: ($item['route'] ?? '#');

				return $route === '#' ? '#' : url($route);
			}

			if ($isAdmin) {
				$menu = config('sidebar'); // Admin sees all menu items
			} else {
				$allowedMenu = getAllowedMenuForUser($user, $roleName, $isStudentUser);
				$menu = !empty($allowedMenu)
					? filterMenuByAllowed(config('sidebar'), $allowedMenu)
					: filterMenuByRoleName(config('sidebar'), $roleName);
				
				$hasDashboard = false;
				foreach ($menu as $mItem) {
					if (strtolower($mItem['title'] ?? '') === 'dashboard') {
						$hasDashboard = true;
						break;
					}
				}
				if (!$hasDashboard) {
					array_unshift($menu, [
						'title' => 'Dashboard',
						'route' => 'admin-dashboard',
						'icon' => 'i-Bar-Chart',
						'permission' => 'dashboard',
						'roles' => ['Admin', 'Student', 'Academic Staff (Teacher)'],
					]);
				}
			}
			@endphp


			@foreach ($menu as $item)
				<li class="nav-item" @if(!empty($item['children'])) data-item="{{ strtolower($item['title']) }}" @endif>
					<a class="nav-item-hold" href="{{ menuItemUrl($item) }}">
						<i class="nav-icon {{ $item['icon'] ?? '' }}"></i>
						<span class="nav-text">
							{{ $item['title'] }}
						</span>
					</a>
					<div class="triangle"></div>
				</li>
			@endforeach

		</ul>

	</div>


	{{-- RIGHT SIDEBAR --}}
	<div class="sidebar-left-secondary rtl-ps-none"
		data-perfect-scrollbar
		data-suppress-scroll-x="true">

		   @php
		   if (!isset($menu)) {
			   if ($isAdmin) {
				   $menu = config('sidebar');
			   } else {
				   $allowedMenu = getAllowedMenuForUser($user, $roleName, $isStudentUser);
				   $menu = filterMenuByAllowed(config('sidebar'), $allowedMenu);
			   }
		   }
		   @endphp

		   @foreach($menu as $item)
			   @if (isset($item['children']) && count($item['children']))
				   <ul class="childNav" data-parent="{{ strtolower($item['title']) }}">
					   @foreach($item['children'] as $child)
						   @if (isset($child['children']) && count($child['children']))
							   <li class="nav-item dropdown-sidemenu">
								   <a href="#">
									   <i class="nav-icon {{ $child['icon'] ?? '' }}"></i>
									   <span class="item-name">{{ $child['title'] }}</span>
									   <i class="dd-arrow i-Arrow-Down"></i>
								   </a>
								   <ul class="submenu">
									   @foreach($child['children'] as $sub)
										   <li>
											   <a href="{{ menuItemUrl($sub) }}">
												   {{ $sub['title'] }}
											   </a>
										   </li>
									   @endforeach
								   </ul>
							   </li>
						   @else
							   <li class="nav-item">
								   <a href="{{ menuItemUrl($child) }}">
									   <i class="nav-icon {{ $child['icon'] ?? '' }}"></i>
									   <span class="item-name">{{ $child['title'] }}</span>
								   </a>
							   </li>
						   @endif
					   @endforeach
				   </ul>
			   @endif
		   @endforeach

	</div>

	<div class="sidebar-overlay"></div>

</div>
