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
        <button type="button" class="btn btn-sm btn-success" onclick="printAdmitCardPages()">Print Admit Card</button>
    </div>

    <div id="printme">
        @include('backend.AcademicsModules.admit_card_fragment')
    </div>
</div>

<style id="admit-card-custom-print-style">
    @media print {
        @page {
            size: A4 landscape;
            margin: 5mm 8mm !important;
        }

        *, *:before, *:after {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            height: auto !important;
            min-height: 100% !important;
            overflow: visible !important;
        }

        .main-header,
        .sidebar-left,
        .sidebar-left-secondary,
        .sidebar-overlay,
        .search-header,
        .app-footer,
        .alert,
        .text-center,
        .breadcrumb,
        .separator-breadcrumb {
            display: none !important;
        }

        .app-admin-wrap,
        .main-content-wrap,
        .main-content {
            display: block !important;
            position: static !important;
            width: 100% !important;
            min-height: 0 !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            float: none !important;
            border: none !important;
            box-shadow: none !important;
        }

        #printme {
            display: block !important;
            position: static !important;
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            float: none !important;
        }

        .page {
            width: 100% !important;
            height: 196mm !important;
            max-height: 196mm !important;
            box-sizing: border-box !important;
            page-break-after: always !important;
            break-after: page !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        .page:last-child {
            page-break-after: auto !important;
            break-after: auto !important;
        }

        .page-break {
            display: none !important;
        }
    }
</style>

<script>
function printAdmitCardPages() {
    var printContent = document.getElementById('printme');
    if (!printContent) {
        window.print();
        return;
    }

    var iframe = document.getElementById('admit-print-iframe');
    if (iframe) {
        iframe.parentNode.removeChild(iframe);
    }
    iframe = document.createElement('iframe');
    iframe.id = 'admit-print-iframe';
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);

    var stylesHtml = '';
    document.querySelectorAll('style').forEach(function(s) {
        stylesHtml += s.outerHTML + '\n';
    });

    var doc = iframe.contentWindow.document;
    doc.open();
    doc.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Admit Cards</title>' +
        stylesHtml +
        '<style>' +
        '@page { size: A4 landscape; margin: 5mm 8mm; }' +
        '*, *:before, *:after { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }' +
        'html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; width: 100% !important; }' +
        '.page { width: 100% !important; height: 196mm !important; max-height: 196mm !important; margin: 0 !important; padding: 0 !important; page-break-after: always !important; break-after: page !important; page-break-inside: avoid !important; break-inside: avoid !important; box-shadow: none !important; overflow: hidden !important; }' +
        '.page:last-child { page-break-after: auto !important; break-after: auto !important; }' +
        '.page-break { display: none !important; }' +
        '</style>' +
        '</head><body>' +
        printContent.innerHTML +
        '</body></html>');
    doc.close();

    setTimeout(function() {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (e) {
            window.print();
        }
    }, 350);
}
</script>

@endsection
