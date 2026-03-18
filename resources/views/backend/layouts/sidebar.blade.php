@php
$adminUser = Auth::user();
$staffUser = Auth::guard('staff')->user();
@endphp
<div style="background: #ffeeba; color: #856404; padding: 10px; margin-bottom: 10px; border: 1px solid #ffeeba; border-radius: 4px;">
	<strong>Debug Info:</strong><br>
	@if($adminUser)
		<strong>Admin User:</strong> {{ $adminUser->name ?? $adminUser->email ?? $adminUser->id }}<br>
		<strong>Roles:</strong> {{ method_exists($adminUser, 'getRoleNames') ? implode(', ', $adminUser->getRoleNames()->toArray()) : 'N/A' }}<br>
		<strong>Permissions:</strong> {{ method_exists($adminUser, 'getAllPermissions') ? implode(', ', $adminUser->getAllPermissions()->pluck('name')->toArray()) : 'N/A' }}<br>
	@else
		<strong>Admin User:</strong> Not authenticated<br>
	@endif
	<hr>
	@if($staffUser)
		<strong>Staff User:</strong> {{ $staffUser->name ?? $staffUser->username ?? $staffUser->email ?? $staffUser->id }}<br>
		<strong>Roles:</strong> {{ method_exists($staffUser, 'getRoleNames') ? implode(', ', $staffUser->getRoleNames()->toArray()) : 'N/A' }}<br>
		<strong>Permissions:</strong> {{ method_exists($staffUser, 'getAllPermissions') ? implode(', ', $staffUser->getAllPermissions()->pluck('name')->toArray()) : 'N/A' }}<br>
	@else
		<strong>Staff User:</strong> Not authenticated<br>
	@endif
</div>

<div class="side-content-wrap">

	{{-- LEFT SIDEBAR --}}
	<div class="sidebar-left open rtl-ps-none"
		data-perfect-scrollbar
		data-suppress-scroll-x="true">

		<ul class="navigation-left">

			@php
			$menu = config('sidebar');
			$user = auth()->user();
			$isAdmin = $user && $user->hasRole('Admin');
			@endphp


			@foreach ($menu as $item)
				@php
					$show = $isAdmin;
					if (!$isAdmin) {
						// Show if user has required role
						if (isset($item['roles'])) {
							foreach ($item['roles'] as $role) {
								if ($user && $user->hasRole($role)) {
									$show = true;
									break;
								}
							}
						}
						// Show if user has required permission
						if (!$show && isset($item['permission']) && $user && $user->can($item['permission'])) {
							$show = true;
						}
					}
				@endphp
				@if ($show)
					<li class="nav-item" data-item="{{ strtolower($item['title']) }}">
						<a class="nav-item-hold" href="{{ isset($item['route']) ? url($item['route']) : '#' }}">
							<i class="nav-icon {{ $item['icon'] ?? '' }}"></i>
							<span class="nav-text">
								{{ $item['title'] }}
							</span>
						</a>
						<div class="triangle"></div>
					</li>
				@endif
			@endforeach

		</ul>

	</div>


	{{-- RIGHT SIDEBAR --}}
	<div class="sidebar-left-secondary rtl-ps-none"
		data-perfect-scrollbar
		data-suppress-scroll-x="true">

		@php
		$menu = config('sidebar');
		$user = auth()->user();
		$isAdmin = $user && $user->hasRole('Admin');
		@endphp



		@foreach($menu as $item)
			@php
				$show = $isAdmin;
				if (!$isAdmin) {
					if (isset($item['roles'])) {
						foreach ($item['roles'] as $role) {
							if ($user && $user->hasRole($role)) {
								$show = true;
								break;
							}
						}
					}
					if (!$show && isset($item['permission']) && $user && $user->can($item['permission'])) {
						$show = true;
					}
				}
			@endphp
			@if ($show && isset($item['children']))
				<ul class="childNav" data-parent="{{ strtolower($item['title']) }}">
					@foreach($item['children'] as $child)
						@php
							$childShow = $isAdmin;
							if (!$isAdmin) {
								if (isset($child['roles'])) {
									foreach ($child['roles'] as $role) {
										if ($user && $user->hasRole($role)) {
											$childShow = true;
											break;
										}
									}
								}
								if (!$childShow && isset($child['permission']) && $user && $user->can($child['permission'])) {
									$childShow = true;
								}
							}
						@endphp
						@if ($childShow)
							<li class="nav-item dropdown-sidemenu">
								@php
									$settingMenu = strtolower($item['title']) === 'setting';
									$directLink = $settingMenu && in_array(strtolower($child['title']), ['users', 'roles', 'permission']);
								@endphp
								<a href="{{ $directLink && isset($child['route']) ? url($child['route']) : '#' }}">
									<i class="nav-icon {{ $child['icon'] ?? '' }}"></i>
									<span class="item-name">
										{{ $child['title'] }}
									</span>
									@if(isset($child['children']))
										<i class="dd-arrow i-Arrow-Down"></i>
									@endif
								</a>
								{{-- SUBMENU --}}
								@if(isset($child['children']))
									<ul class="submenu">
										@foreach($child['children'] as $sub)
											@php
												$showSub = $isAdmin;
												if (!$isAdmin) {
													if (isset($sub['roles'])) {
														foreach ($sub['roles'] as $role) {
															if ($user && $user->hasRole($role)) {
																$showSub = true;
																break;
															}
														}
													}
													if (!$showSub && isset($sub['permission']) && $user && $user->can($sub['permission'])) {
														$showSub = true;
													}
												}
											@endphp
											@if($showSub)
												<li>
													<a href="{{ url($sub['route']) }}">
														{{ $sub['title'] }}
													</a>
												</li>
											@endif
										@endforeach
									</ul>
								@endif
							</li>
						@endif
					@endforeach
				</ul>
			@endif
		@endforeach

	</div>

	<div class="sidebar-overlay"></div>

</div>