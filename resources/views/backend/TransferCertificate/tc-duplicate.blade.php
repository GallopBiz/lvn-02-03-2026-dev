@extends('backend.layouts.main')

@section('main-container')
<div class="main-content">
    <div class="breadcrumb">
        <h1>Duplicate Transfer Certificate</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <p>
                Generate duplicate for <strong>{{ $certificate->student_name }}</strong>
                ({{ $certificate->scholar_no }}) - {{ $certificate->certificate_no }}.
            </p>

            <form method="POST" action="{{ route('transfercertificate.duplicate.store', $certificate->id) }}">
                @csrf
                <div class="form-group">
                    <label>Reason for Reissue</label>
                    <textarea name="reissue_reason" class="form-control" rows="3" required>{{ old('reissue_reason') }}</textarea>
                </div>
                <button type="submit" class="btn btn-warning">Generate Duplicate</button>
                <a href="{{ route('transfercertificate.index') }}" class="btn btn-light">Back</a>
            </form>
        </div>
    </div>
</div>
@endsection
