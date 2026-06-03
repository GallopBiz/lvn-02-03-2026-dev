@extends('backend.layouts.main')

@section('main-container')
<div class="main-content">
    <div class="breadcrumb">
        <h1>Transfer Certificate</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="mb-3 tc-print-actions">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
        <a href="{{ route('transfercertificate.print', [$certificate->id, 'download' => 'pdf']) }}" class="btn btn-success">Download PDF</a>
        <a href="{{ route('transfercertificate.index') }}" class="btn btn-light">Back</a>
    </div>

    @include('backend.TransferCertificate.pdf.default', ['certificate' => $certificate, 'isPdf' => $isPdf ?? false])
</div>
@endsection
