@extends('layouts.app')
@section('main-container')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Edit Role</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back</a>
        </div>
    </div>
</div>
@if (count($errors) > 0)
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
@endif
{!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id]]) !!}
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Name:</strong>
            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 row">
    
        <!-- Sidebar Menu Permissions -->
        <div class="form-group col-md-12 mt-4">
            <strong>Sidebar Menu Permissions</strong>
            <ul style="list-style: none;">
                @php
                function renderMenuTree($items, $selected, $prefix = '') {
                    foreach ($items as $item) {
                        $key = $prefix . $item['title'];
                        $isChecked = is_array($selected) && in_array($key, $selected);
                        echo '<li>';
                        echo '<label>';
                        echo '<input type="checkbox" name="menu[]" value="' . $key . '" ' . ($isChecked ? 'checked' : '') . '> ';
                        echo $item["title"];
                        echo '</label>';
                        if (!empty($item['children'])) {
                            echo '<ul style="list-style: none; margin-left:20px;">';
                            renderMenuTree($item['children'], $selected, $key . ' > ');
                            echo '</ul>';
                        }
                        echo '</li>';
                    }
                }
                renderMenuTree($menu, $selectedMenu);
                @endphp
            </ul>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.check-all').forEach(function(checkAll) {
                checkAll.addEventListener('change', function() {
                    var targetClass = this.getAttribute('data-target');
                    var checkboxes = document.querySelectorAll('.' + targetClass);
                    checkboxes.forEach(function(box) {
                        box.checked = checkAll.checked;
                    });
                });
            });
        });
    </script>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>
{!! Form::close() !!}
@endsection
