@extends('layouts.app')
@section('content')
<div class="row col-12">

    {{-- <div class="col-6">
        <h2>Create New Role</h2>
    </div> --}}
    <div class="col-6 mb-3">
        <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back</a>
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
{!! Form::open(array('route' => 'roles.store','method'=>'POST', 'class' => 'col-12')) !!}
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 d-flex align-items-end">
        <div class="form-group col-6">
            <strong>Name:</strong>
            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
        </div>
        <div class="form-group col-2 ms-2">            
        <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 row">
        <div class="form-group col-2">
            <strong>Permission:</strong>
            <br />
            @foreach($permission as $value)
            <label>{{ Form::checkbox('permission[]', $value->id, false, array('class' => 'name')) }}
                {{ $value->name }}</label>
            <br />
            @endforeach
        </div>

        <div class="form-group col-2">
            <strong>Scholars functions</strong>
            <br />        
            {{-- for functions only --}}
            @foreach($Scholars_functions as $value)
            <label>{{ Form::checkbox('permission[]', $value['functionname'], false, array('class' => 'name')) }}
                {{ $value['label'] }}</label>
            <br />
            @endforeach
        </div>

        <div class="form-group col-2">
            <strong>Fees functions</strong>
            <br />        
            {{-- for functions only --}}
            @foreach($Fees_functions as $value)
            <label>{{ Form::checkbox('permission[]', $value['functionname'], false, array('class' => 'name')) }}
                {{ $value['label'] }}</label>
            <br />
            @endforeach
        </div>

        <div class="form-group col-2">
            <strong>Transport functions</strong>
            <br />        
            {{-- for functions only --}}
            @foreach($Transport_functions as $value)
            <label>{{ Form::checkbox('permission[]', $value['functionname'], false, array('class' => 'name')) }}
                {{ $value['label'] }}</label>
            <br />
            @endforeach
        </div>

        <div class="form-group col-2">
            <strong>Academic functions</strong>
            <br />        
            {{-- for functions only --}}
            @foreach($Academic_functions as $value)
            <label>{{ Form::checkbox('permission[]', $value['functionname'], false, array('class' => 'name')) }}
                {{ $value['label'] }}</label>
            <br />
            @endforeach
        </div>

        <div class="form-group col-2">
            <strong>HRMS functions</strong>
            <br />        
            {{-- for functions only --}}
            @foreach($hrms_functions as $value)
            <label>{{ Form::checkbox('permission[]', $value['functionname'], false, array('class' => 'name')) }}
                {{ $value['label'] }}</label>
            <br />
            @endforeach
        </div>

    </div>
   
</div>
{!! Form::close() !!}

@endsection
