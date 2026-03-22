@php
$sidebar = config('sidebar');
@endphp

<div class="container-fluid">
    <div class="row">
        @foreach($sidebar as $parent)
            @if($parent['title'] === 'Dashboard')
                @continue
            @endif
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <input type="checkbox" class="parent-menu" data-parent="{{ $parent['title'] }}" name="menu[]" value="{{ $parent['title'] }}"
                            {{ isset($selectedMenus) && in_array($parent['title'], $selectedMenus) ? 'checked' : '' }}>
                        <strong>{{ $parent['title'] }}</strong>
                    </div>
                    <div class="card-body">
                        @if(isset($parent['children']) && count($parent['children']))
                            <ul class="list-group list-group-flush">
                                @foreach($parent['children'] as $child)
                                    <li class="list-group-item">
                                        <input type="checkbox" class="child-menu child-of-{{ Str::slug($parent['title']) }}" data-parent="{{ $parent['title'] }}" name="menu[]" value="{{ $parent['title'] . ' > ' . $child['title'] }}"
                                            {{ isset($selectedMenus) && in_array($parent['title'] . ' > ' . $child['title'], $selectedMenus) ? 'checked' : '' }}>
                                        {{ $child['title'] }}
                                        @if(isset($child['children']) && count($child['children']))
                                            <ul class="list-unstyled ml-3 mt-2">
                                                @foreach($child['children'] as $sub)
                                                    <li>
                                                        <input type="checkbox" class="subchild-menu child-of-{{ Str::slug($parent['title']) }}" data-parent="{{ $parent['title'] }}" name="menu[]" value="{{ $parent['title'] . ' > ' . $child['title'] . ' > ' . $sub['title'] }}"
                                                            {{ isset($selectedMenus) && in_array($parent['title'] . ' > ' . $child['title'] . ' > ' . $sub['title'], $selectedMenus) ? 'checked' : '' }}>
                                                        {{ $sub['title'] }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">No child menus</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.parent-menu').forEach(function(parentCheckbox) {
        parentCheckbox.addEventListener('change', function() {
            var parent = this.getAttribute('data-parent');
            var slug = parent.toLowerCase().replace(/[^a-z0-9]+/g, '-');
            var checked = this.checked;
            document.querySelectorAll('.child-of-' + slug).forEach(function(child) {
                child.checked = checked;
            });
        });
    });
});
</script>
