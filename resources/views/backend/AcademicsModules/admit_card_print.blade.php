@extends('backend.layouts.main')
@section('main-container')

<div class="main-content">
    <div class="text-center mb-3">
        <a
            href="{{ route('academic.roll-no-tools', ['class_name' => $className ?? '', 'section_name' => $sectionName ?? '', 'exam_id' => $exam->id ?? null]) }}"
            class="btn btn-sm btn-secondary"
        >
            Back
        </a>
        <button type="button" class="btn btn-sm btn-success" onclick="window.print()">Print Admit Card</button>
    </div>

    <div id="printme">
        @include('backend.AcademicsModules.admit_card_fragment')
    </div>
</div>

<style>
    @media print {
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .app-admin-wrap,
        .main-content-wrap,
        .main-content {
            display: block !important;
            width: 100% !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .main-header,
        .sidebar-left,
        .sidebar-left-secondary,
        .sidebar-overlay,
        .search-header,
        .app-footer,
        .alert,
        .text-center {
            display: none !important;
        }
        body * { visibility: hidden; }
        #printme, #printme * { visibility: visible; }
        #printme {
            display: block !important;
            position: static !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>

@endsection
