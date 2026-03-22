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
			// use App\Models\RoleMenu; // Already imported at the top
			// Use correct user object for staff or default
			$user = Auth::guard('staff')->check() ? Auth::guard('staff')->user() : auth()->user();
			$isAdmin = $user && $user->hasRole('Admin');
			function getAllowedMenuForUser($user) {
				if (!$user) return [];
				// For staff users, get role name from users table, then get role ID
				$roleName = $user->role ?? null;
				if (!$roleName) return [];
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
			if ($isAdmin) {
				$menu = config('sidebar'); // Admin sees all menu items
			} else {
				$allowedMenu = getAllowedMenuForUser($user);
				$menu = filterMenuByAllowed(config('sidebar'), $allowedMenu);
			}
			@endphp


			@foreach ($menu as $item)
				<li class="nav-item" data-item="{{ strtolower($item['title']) }}">
					<a class="nav-item-hold" href="{{ isset($item['route']) ? url($item['route']) : '#' }}">
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
		   // Use the same menu filtering logic as the left sidebar
		   // Functions are already defined above, just reuse the $menu variable
		   if (!isset($menu)) {
			   if ($isAdmin) {
				   $menu = config('sidebar');
			   } else {
				   $allowedMenu = getAllowedMenuForUser($user);
				   $menu = filterMenuByAllowed(config('sidebar'), $allowedMenu);
			   }
		   }
		   @endphp



		   @foreach($menu as $item)
			   @if (isset($item['children']) && count($item['children']))
				   <ul class="childNav" data-parent="{{ strtolower($item['title']) }}">
					   @foreach($item['children'] as $child)
						   <li class="nav-item dropdown-sidemenu">
							   @php
								   $settingMenu = strtolower($item['title']) === 'setting';
								   $directLink = $settingMenu && in_array(strtolower($child['title']), ['users', 'roles', 'permission']);
							   @endphp
							   <a href="{{ $directLink && isset($child['route']) ? url($child['route']) : (isset($child['route']) ? url($child['route']) : '#') }}">
								   <i class="nav-icon {{ $child['icon'] ?? '' }}"></i>
								   <span class="item-name">
									   {{ $child['title'] }}
								   </span>
								   @if(isset($child['children']))
									   <i class="dd-arrow i-Arrow-Down"></i>
								   @endif
							   </a>
							   {{-- SUBMENU --}}
							   @if(isset($child['children']) && count($child['children']))
								   <ul class="submenu">
									   @foreach($child['children'] as $sub)
										   <li>
											   <a href="{{ isset($sub['route']) ? url($sub['route']) : '#' }}">
												   {{ $sub['title'] }}
											   </a>
										   </li>
									   @endforeach
								   </ul>
							   @endif
						   </li>
					   @endforeach
				   </ul>
			   @endif
		   @endforeach

	</div>

	<div class="sidebar-overlay"></div>

</div>