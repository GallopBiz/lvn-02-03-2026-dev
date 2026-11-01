@php
			if (!function_exists('isStaffRouteRequest')) {
				function isStaffRouteRequest() {
					return request()->is('staff*') || request()->is('*staff*') || request()->routeIs('staff.*');
				}
			}

			if (!function_exists('filterMenuByRoleName')) {
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
			}

			$user = \App\Services\PermissionService::getCurrentUser();
			$isStudentUser = !Auth::guard('staff')->check()
				&& Auth::guard('web')->check()
				&& strtolower(trim((string) Auth::guard('web')->user()->type)) !== 'a';
			$menu = $isStudentUser
				? filterMenuByRoleName(config('sidebar'), 'Student')
				: \App\Services\PermissionService::filterSidebarForUser(config('sidebar'), $user);

			if (!function_exists('menuItemUrl')) {
				function menuItemUrl($item) {
					if (!empty($item['children'])) {
						return '#';
					}

					$isStaffUser = isStaffRouteRequest()
						|| (Auth::guard('staff')->check() && session('auth_portal') === 'staff');
					$route = $isStaffUser && isset($item['staff_route'])
						? $item['staff_route']
						: ($item['route'] ?? '#');

					if ($route === '#') {
						return '#';
					}

					if (\Illuminate\Support\Facades\Route::has($route)) {
						return route($route);
					}

					return url($route);
				}
			}
			@endphp

	<div class="side-content-wrap">

		{{-- LEFT SIDEBAR --}}
		<div class="sidebar-left open rtl-ps-none"
			data-perfect-scrollbar
			data-suppress-scroll-x="true">

			<ul class="navigation-left">

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
			   $menu = \App\Services\PermissionService::filterSidebarForUser(config('sidebar'), $user);
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
