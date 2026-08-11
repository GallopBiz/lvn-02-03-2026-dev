@extends('backend.layouts.main')

@section('main-container')
<style>
    .tc-print-page {
        width: 100%;
        max-width: 210mm;
        margin: 0 auto;
        padding: 0;
        background: #fff;
    }

    .tc-print-actions {
        margin-bottom: 10px;
    }

    @media print {
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            min-height: 100vh;
        }

        .breadcrumb,
        .separator-breadcrumb,
        .tc-print-actions,
        .btn,
        .btn-primary,
        .btn-success,
        .btn-light,
        .main-header,
        .sidebar,
        .sidebar-overlay,
        .page-footer,
        .footer,
        .navbar,
        .nav,
        .breadcrumb {
            display: none !important;
        }

        .main-content.tc-print-page,
        .tc-print-page {
            display: block !important;
            width: 210mm !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        .tc-page-wrap {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 210mm !important;
            min-height: 297mm !important;
            padding: 0 !important;
            background: #fff !important;
            overflow: visible !important;
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
        }

        .tc-a4 {
            width: 205mm !important;
            min-height: 287mm !important;
            margin: 0 !important;
            padding: 8mm 9mm 6mm !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
        }
    }
</style>

<div class="main-content tc-print-page" id="printme">
    <div class="breadcrumb">
        <h1>Transfer Certificate</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="mb-3 tc-print-actions">
        <button type="button" onclick="window.print(); return false;" class="btn btn-primary">Print</button>
        <a href="{{ route('transfercertificate.print', [$certificate->id, 'download' => 'pdf']) }}" class="btn btn-success">Download PDF</a>
        <a href="{{ route('transfercertificate.index') }}" class="btn btn-light">Back</a>
    </div>

    @include('backend.TransferCertificate.pdf.default', ['certificate' => $certificate, 'isPdf' => $isPdf ?? false])
</div>
@endsection
