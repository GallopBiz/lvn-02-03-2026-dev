@php
$sidebar = config('sidebar');
$permissionsMap = [];

if (isset($selectedMenus) && is_array($selectedMenus)) {
    if (isset($selectedMenus[0]) && is_string($selectedMenus[0])) {
        foreach ($selectedMenus as $m) {
            $permissionsMap[trim($m)] = ['view' => true, 'add' => true, 'edit' => true, 'delete' => true];
        }
    } else {
        $permissionsMap = $selectedMenus;
    }
}
@endphp

<style>
.permission-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    background: #ffffff;
    transition: all 0.2s ease;
}
.permission-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.permission-header {
    background: linear-gradient(135deg, #1f2937, #374151);
    color: #ffffff;
    border-top-left-radius: 7px;
    border-top-right-radius: 7px;
    padding: 10px 14px;
}
.permission-item-box {
    padding: 8px 10px;
    margin-bottom: 6px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 6px;
    transition: background 0.15s ease;
}
.permission-item-box:hover {
    background: #f1f5f9;
}
.action-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 7px;
    border-radius: 4px;
    cursor: pointer;
    user-select: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: 1px solid transparent;
}
.action-view { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
.action-add { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.action-edit { background: #fef3c7; color: #b45309; border-color: #fde68a; }
.action-delete { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.action-badge input[type="checkbox"] {
    cursor: pointer;
    margin: 0;
}
.submenu-heading {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #4b5563;
    padding: 6px 0 2px 2px;
    border-bottom: 1px dashed #cbd5e1;
    margin-bottom: 8px;
    margin-top: 8px;
}
</style>

<div class="container-fluid px-0">
    <!-- Global Quick Controls Bar -->
    <div class="card mb-3 shadow-sm" style="background: #ffffff; border: 1px solid #cbd5e1;">
        <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="permissionSearchBox" class="form-control form-control-sm" placeholder="Search menu or module..." style="max-width: 250px;">
                <span class="text-muted small">Filter modules quickly</span>
            </div>
            <div class="d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnCheckAllGlobal">Check All</button>
                <button type="button" class="btn btn-sm btn-outline-info" id="btnCheckAllViewGlobal">All View</button>
                <button type="button" class="btn btn-sm btn-outline-success" id="btnCheckAllAddGlobal">All Add</button>
                <button type="button" class="btn btn-sm btn-outline-warning" id="btnCheckAllEditGlobal">All Edit</button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnCheckAllDeleteGlobal">All Delete</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUncheckAllGlobal">Clear All</button>
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="row">
        @foreach($sidebar as $parent)
            @if(strtolower(trim($parent['title'])) === 'dashboard')
                @continue
            @endif

            @php
                $parentSlug = Str::slug($parent['title']);
            @endphp

            <div class="col-lg-4 col-md-6 mb-4 menu-module-card" data-title="{{ strtolower($parent['title']) }}">
                <div class="permission-card h-100 d-flex flex-column">
                    <!-- Card Header -->
                    <div class="permission-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="{{ $parent['icon'] ?? 'i-Folder' }} fs-5"></i>
                            <span class="fw-bold">{{ $parent['title'] }}</span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-sm btn-light py-0 px-2 toggle-card-all" data-target="{{ $parentSlug }}" style="font-size: 11px;">Select All</button>
                            <button type="button" class="btn btn-sm btn-dark py-0 px-2 clear-card-all" data-target="{{ $parentSlug }}" style="font-size: 11px;">Clear</button>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-3 flex-grow-1" style="max-height: 480px; overflow-y: auto;">
                        @if(!empty($parent['children']))
                            @foreach($parent['children'] as $child)
                                @php
                                    $childKey = $parent['title'] . ' > ' . $child['title'];
                                @endphp

                                @if(!empty($child['children']))
                                    <!-- Child has sub-items -->
                                    <div class="submenu-heading d-flex justify-content-between align-items-center">
                                        <span>{{ $child['title'] }}</span>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none toggle-subgroup" data-target="{{ Str::slug($childKey) }}" style="font-size: 10px;">Toggle</button>
                                    </div>

                                    @foreach($child['children'] as $sub)
                                        @php
                                            $subKey = $childKey . ' > ' . $sub['title'];
                                            $itemPerms = $permissionsMap[$subKey] ?? ($permissionsMap[$sub['title']] ?? []);
                                            $hasView = !empty($itemPerms['view']);
                                            $hasAdd = !empty($itemPerms['add']);
                                            $hasEdit = !empty($itemPerms['edit']);
                                            $hasDelete = !empty($itemPerms['delete']);
                                        @endphp

                                        <div class="permission-item-box item-group-{{ $parentSlug }} item-subgroup-{{ Str::slug($childKey) }}" data-keywords="{{ strtolower($parent['title'] . ' ' . $child['title'] . ' ' . $sub['title']) }}">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-semibold text-dark" style="font-size: 12px;">{{ $sub['title'] }}</span>
                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none toggle-row-all" style="font-size: 10px; color: #64748b;">All</button>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1">
                                                <label class="action-badge action-view">
                                                    <input type="checkbox" name="menu_permissions[{{ $subKey }}][view]" value="1" class="perm-action perm-view action-for-{{ $parentSlug }}" {{ $hasView ? 'checked' : '' }}> View
                                                </label>
                                                <label class="action-badge action-add">
                                                    <input type="checkbox" name="menu_permissions[{{ $subKey }}][add]" value="1" class="perm-action perm-add action-for-{{ $parentSlug }}" {{ $hasAdd ? 'checked' : '' }}> Add
                                                </label>
                                                <label class="action-badge action-edit">
                                                    <input type="checkbox" name="menu_permissions[{{ $subKey }}][edit]" value="1" class="perm-action perm-edit action-for-{{ $parentSlug }}" {{ $hasEdit ? 'checked' : '' }}> Edit
                                                </label>
                                                <label class="action-badge action-delete">
                                                    <input type="checkbox" name="menu_permissions[{{ $subKey }}][delete]" value="1" class="perm-action perm-delete action-for-{{ $parentSlug }}" {{ $hasDelete ? 'checked' : '' }}> Del
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Child is direct leaf -->
                                    @php
                                        $itemPerms = $permissionsMap[$childKey] ?? ($permissionsMap[$child['title']] ?? []);
                                        $hasView = !empty($itemPerms['view']);
                                        $hasAdd = !empty($itemPerms['add']);
                                        $hasEdit = !empty($itemPerms['edit']);
                                        $hasDelete = !empty($itemPerms['delete']);
                                    @endphp

                                    <div class="permission-item-box item-group-{{ $parentSlug }}" data-keywords="{{ strtolower($parent['title'] . ' ' . $child['title']) }}">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold text-dark" style="font-size: 12px;">{{ $child['title'] }}</span>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none toggle-row-all" style="font-size: 10px; color: #64748b;">All</button>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1">
                                            <label class="action-badge action-view">
                                                <input type="checkbox" name="menu_permissions[{{ $childKey }}][view]" value="1" class="perm-action perm-view action-for-{{ $parentSlug }}" {{ $hasView ? 'checked' : '' }}> View
                                            </label>
                                            <label class="action-badge action-add">
                                                <input type="checkbox" name="menu_permissions[{{ $childKey }}][add]" value="1" class="perm-action perm-add action-for-{{ $parentSlug }}" {{ $hasAdd ? 'checked' : '' }}> Add
                                            </label>
                                            <label class="action-badge action-edit">
                                                <input type="checkbox" name="menu_permissions[{{ $childKey }}][edit]" value="1" class="perm-action perm-edit action-for-{{ $parentSlug }}" {{ $hasEdit ? 'checked' : '' }}> Edit
                                            </label>
                                            <label class="action-badge action-delete">
                                                <input type="checkbox" name="menu_permissions[{{ $childKey }}][delete]" value="1" class="perm-action perm-delete action-for-{{ $parentSlug }}" {{ $hasDelete ? 'checked' : '' }}> Del
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="text-muted small p-2">No configurable submenus</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Dependency Rule: If Add, Edit, or Delete is checked, auto-check View
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('perm-action')) {
            var row = e.target.closest('.permission-item-box');
            if (row) {
                var viewCheckbox = row.querySelector('.perm-view');
                if (e.target.checked && (e.target.classList.contains('perm-add') || e.target.classList.contains('perm-edit') || e.target.classList.contains('perm-delete'))) {
                    if (viewCheckbox && !viewCheckbox.checked) {
                        viewCheckbox.checked = true;
                    }
                }
            }
        }
    });

    // 2. Toggle row (all 4 actions for a specific row)
    document.querySelectorAll('.toggle-row-all').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var row = this.closest('.permission-item-box');
            var boxes = row.querySelectorAll('.perm-action');
            var anyUnchecked = Array.from(boxes).some(function(b) { return !b.checked; });
            boxes.forEach(function(b) { b.checked = anyUnchecked; });
        });
    });

    // 3. Card-level Select All
    document.querySelectorAll('.toggle-card-all').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var target = this.getAttribute('data-target');
            document.querySelectorAll('.action-for-' + target).forEach(function(b) {
                b.checked = true;
            });
        });
    });

    // 4. Card-level Clear All
    document.querySelectorAll('.clear-card-all').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var target = this.getAttribute('data-target');
            document.querySelectorAll('.action-for-' + target).forEach(function(b) {
                b.checked = false;
            });
        });
    });

    // 5. Subgroup toggle
    document.querySelectorAll('.toggle-subgroup').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var target = this.getAttribute('data-target');
            var boxes = document.querySelectorAll('.item-subgroup-' + target + ' .perm-action');
            var anyUnchecked = Array.from(boxes).some(function(b) { return !b.checked; });
            boxes.forEach(function(b) { b.checked = anyUnchecked; });
        });
    });

    // 6. Global Buttons
    document.getElementById('btnCheckAllGlobal')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-action').forEach(function(b) { b.checked = true; });
    });
    document.getElementById('btnUncheckAllGlobal')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-action').forEach(function(b) { b.checked = false; });
    });
    document.getElementById('btnCheckAllViewGlobal')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-view').forEach(function(b) { b.checked = true; });
    });
    document.getElementById('btnCheckAllAddGlobal')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-add').forEach(function(b) { b.checked = true; });
        document.querySelectorAll('.perm-view').forEach(function(b) { b.checked = true; });
    });
    document.getElementById('btnCheckAllEditGlobal')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-edit').forEach(function(b) { b.checked = true; });
        document.querySelectorAll('.perm-view').forEach(function(b) { b.checked = true; });
    });
    document.getElementById('btnCheckAllDeleteGlobal')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-delete').forEach(function(b) { b.checked = true; });
        document.querySelectorAll('.perm-view').forEach(function(b) { b.checked = true; });
    });

    // 7. Search filter
    document.getElementById('permissionSearchBox')?.addEventListener('input', function() {
        var query = this.value.toLowerCase().trim();
        document.querySelectorAll('.menu-module-card').forEach(function(card) {
            var items = card.querySelectorAll('.permission-item-box');
            var cardHasMatch = false;

            items.forEach(function(item) {
                var text = item.getAttribute('data-keywords') || '';
                if (!query || text.indexOf(query) !== -1) {
                    item.style.display = '';
                    cardHasMatch = true;
                } else {
                    item.style.display = 'none';
                }
            });

            card.style.display = cardHasMatch ? '' : 'none';
        });
    });
});
</script>
