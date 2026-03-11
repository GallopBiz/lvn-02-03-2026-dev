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
        
        <div class="form-group col-md-2">
            <strong>Scholars functions</strong>
            <input type="checkbox" class="check-all" data-target="sch_"> <small>Check All</small>
            <br/>
            @foreach($Scholars_functions as $value)
                <label>{{ Form::checkbox('permission[]', $value['functionname'], in_array($value['functionname'], $rolePermissions) ? true : false, array('class' => 'name sch_')) }}
                {{ $value['label'] }}</label>
            <br/>
            @endforeach
        </div>
        <div class="form-group col-md-2">
            <strong>Fees functions</strong>
            <input type="checkbox" class="check-all" data-target="fee_"> <small>Check All</small>
            <br/>
            @foreach($Fees_functions as $value)
                <label>{{ Form::checkbox('permission[]', $value['functionname'], in_array($value['functionname'], $rolePermissions) ? true : false, array('class' => 'name fee_')) }}
                {{ $value['label'] }}</label>
            <br/>
            @endforeach
        </div>
        <div class="form-group col-md-2">
            <strong>Transport functions</strong>
            <input type="checkbox" class="check-all" data-target="trans_"> <small>Check All</small>
            <br/>
            @foreach($Transport_functions as $value)
                <label>{{ Form::checkbox('permission[]', $value['functionname'], in_array($value['functionname'], $rolePermissions) ? true : false, array('class' => 'name trans_')) }}
                {{ $value['label'] }}</label>
            <br/>
            @endforeach
        </div>
        <div class="form-group col-md-2">
            <strong>Academic functions</strong>
            <input type="checkbox" class="check-all" data-target="acad_"> <small>Check All</small>
            <br/>
            @foreach($Academic_functions as $value)
                <label>{{ Form::checkbox('permission[]', $value['functionname'], in_array($value['functionname'], $rolePermissions) ? true : false, array('class' => 'name acad_')) }}
                {{ $value['label'] }}</label>
            <br/>
            @endforeach
        </div>
        <div class="form-group col-md-2">
            <strong>HRMS functions</strong>
            <input type="checkbox" class="check-all" data-target="hrms_"> <small>Check All</small>
            <br/>
            @foreach($hrms_functions as $value)
                <label>{{ Form::checkbox('permission[]', $value['functionname'], in_array($value['functionname'], $rolePermissions) ? true : false, array('class' => 'name hrms_')) }}
                {{ $value['label'] }}</label>
            <br/>
            @endforeach
        </div>
        <div class="form-group col-md-2">
                <strong>General Permissions</strong>
                <input type="checkbox" class="check-all" data-target="perm_"> <small>Check All</small>
                <br/>
                @foreach($permission as $value)
                    <label>{{ Form::checkbox('permission[]', $value->name, in_array($value->name, $rolePermissions) ? true : false, array('class' => 'name perm_')) }}
                    {{ $value->name }}</label>
                <br/>
                @endforeach
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
