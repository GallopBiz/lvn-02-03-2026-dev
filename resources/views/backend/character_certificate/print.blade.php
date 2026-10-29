<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Character Certificate &mdash; {{ $certificate['student_name'] ?: 'Print' }}</title>
    <style id="cert-print-styles">
        /* Standard A4 Portrait Page */
        @page {
            size: A4 portrait;
            margin: 0; /* Zero page margin so border covers complete page with exact equal margins */
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: "Times New Roman", Times, Georgia, serif;
            color: #111;
        }

        /* Top Action Bar (Screen Only - Hidden during print) */
        .no-print-bar {
            background-color: #1e293b;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 13px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            position: sticky;
            top: 0;
            z-index: 9999;
        }

        .no-print-bar .action-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-print {
            background-color: #663399;
            color: #fff;
            border: none;
            padding: 8px 22px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-print:hover {
            background-color: #52277d;
        }

        .btn-close-window {
            background-color: #475569;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-size: 13px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .spacing-control {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #cbd5e1;
        }

        .spacing-control select {
            background: #334155;
            color: #fff;
            border: 1px solid #64748b;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .editable-tip {
            color: #93c5fd;
            font-size: 12px;
            font-style: italic;
        }

        /* ==========================================================================
           A4 Sheet & Complete Page Border Structure (Exact Equal Space All Sides)
           ========================================================================== */
        :root {
            --equal-margin: 10mm; /* Equal space from Top, Bottom, Left, Right */
            --sheet-width: 210mm;
            --sheet-height: 297mm;
        }

        /* Screen View */
        @media screen {
            body {
                background: #475569;
            }

            .sheet-wrapper {
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding: 24px 15px 48px 15px;
            }

            .page-sheet {
                background: #ffffff;
                width: 210mm;
                height: 297mm;
                padding: var(--equal-margin); /* Exact equal 10mm space from all 4 paper edges */
                box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35), 0 3px 10px rgba(0, 0, 0, 0.2);
                box-sizing: border-box;
                position: relative;
            }

            .certificate-border-box {
                box-sizing: border-box;
                width: 100%;
                height: 100%;
                border: 2px solid #111111;
                padding: 8mm 14mm 8mm 14mm;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                position: relative;
                background: #ffffff;
            }
        }

        /* Print View (100% Exact Equal Margins & Complete Page Coverage) */
        @media print {
            .no-print-bar {
                display: none !important;
            }

            html, body {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: hidden !important;
            }

            .sheet-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                display: block !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .page-sheet {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: var(--equal-margin) !important; /* EXACT EQUAL SPACE FROM TOP, BOTTOM, LEFT, RIGHT */
                box-shadow: none !important;
                box-sizing: border-box !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
                background: #fff !important;
            }

            .certificate-border-box {
                box-sizing: border-box !important;
                width: 100% !important;
                height: 100% !important;
                border: 2px solid #000000 !important;
                padding: 8mm 14mm 8mm 14mm !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        /* School Header (Shown only in plain paper mode) */
        .school-header {
            text-align: center;
            border-bottom: 2px solid #111;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .school-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .school-header h2 {
            margin: 3px 0 2px 0;
            font-size: 13.5px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .school-header p {
            margin: 2px 0;
            font-size: 11.5px;
            color: #333;
        }

        /* Blank Header Area for Pre-printed Letterhead */
        .letterhead-blank-space {
            width: 100%;
            height: {{ $certificate['blank_header_height'] ?? 55 }}mm;
            background: transparent;
            position: relative;
        }

        /* Certificate Title */
        .cert-title {
            text-align: center;
            font-size: 21px;
            font-weight: bold;
            font-style: italic;
            text-decoration: underline;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
            text-underline-offset: 3.5px;
        }

        /* Academic Session */
        .cert-session {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 28px;
            text-underline-offset: 3.5px;
        }

        /* Certificate Body Paragraphs */
        .cert-paragraph {
            font-size: 16px;
            font-style: italic;
            line-height: 1.88;
            text-align: justify;
            margin-bottom: 22px;
            letter-spacing: 0.2px;
            word-spacing: 1px;
        }

        .cert-paragraph u {
            text-decoration: underline;
            font-weight: normal;
            text-underline-offset: 3px;
        }

        /* Contenteditable on screen */
        [contenteditable="true"] {
            outline: none;
            cursor: text;
        }

        @media screen {
            [contenteditable="true"]:hover {
                background-color: #fafafa;
                box-shadow: 0 0 0 1px #cbd5e1;
                border-radius: 2px;
            }
            [contenteditable="true"]:focus {
                background-color: #fff;
                box-shadow: 0 0 0 2px #93c5fd;
                border-radius: 2px;
            }
        }

        /* Signature Area (Always positioned near the bottom inside the complete border) */
        .signature-area {
            margin-top: auto; /* Pushes signature block towards the bottom border */
            display: flex;
            justify-content: flex-end;
            padding-right: 15px;
            padding-bottom: 8mm;
        }

        .sig-block {
            text-align: center;
            min-width: 160px;
        }

        .sig-space {
            height: 40px;
        }

        .sig-title {
            font-weight: bold;
            font-size: 15px;
            font-style: normal;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    {{-- Top Action & Alignment Bar --}}
    <div class="no-print-bar">
        <div class="action-group">
            <button type="button" class="btn-print" onclick="window.print()">
                &#128424; Print Certificate
            </button>
            <div class="spacing-control">
                <label for="spacingSelect">Letterhead Blank Space:</label>
                <select id="spacingSelect" onchange="adjustTopSpacing(this.value)">
                    <option value="40" {{ ($certificate['blank_header_height'] ?? 55) == 40 ? 'selected' : '' }}>40 mm</option>
                    <option value="45" {{ ($certificate['blank_header_height'] ?? 55) == 45 ? 'selected' : '' }}>45 mm</option>
                    <option value="50" {{ ($certificate['blank_header_height'] ?? 55) == 50 ? 'selected' : '' }}>50 mm</option>
                    <option value="55" {{ ($certificate['blank_header_height'] ?? 55) == 55 ? 'selected' : '' }}>55 mm (Default)</option>
                    <option value="60" {{ ($certificate['blank_header_height'] ?? 55) == 60 ? 'selected' : '' }}>60 mm</option>
                    <option value="65" {{ ($certificate['blank_header_height'] ?? 55) == 65 ? 'selected' : '' }}>65 mm</option>
                    <option value="70" {{ ($certificate['blank_header_height'] ?? 55) == 70 ? 'selected' : '' }}>70 mm</option>
                </select>
            </div>
            <div class="spacing-control">
                <label for="marginSelect">Equal Page Margin:</label>
                <select id="marginSelect" onchange="adjustEqualMargin(this.value)">
                    <option value="8">8 mm</option>
                    <option value="10" selected>10 mm (Standard Equal)</option>
                    <option value="12">12 mm</option>
                </select>
            </div>
            <div class="spacing-control">
                <label>
                    <input type="checkbox" id="toggleBorderCheck" {{ $certificate['include_border'] ? 'checked' : '' }} onchange="toggleBorder(this.checked)">
                    Border
                </label>
            </div>
        </div>

        <div class="action-group">
            <span class="editable-tip">&#9998; Tip: Click anywhere on the certificate to edit directly before printing.</span>
            <a href="javascript:window.close()" class="btn-close-window">Close</a>
        </div>
    </div>

    {{-- Outer Sheet Wrapper --}}
    <div class="sheet-wrapper">
        <div class="page-sheet" id="pageSheet">
            <div class="certificate-border-box" id="certBorderBox" style="{{ $certificate['include_border'] ? '' : 'border: none !important;' }}">

                @if(!empty($certificate['include_header']))
                    {{-- Optional Plain Paper School Header --}}
                    <div class="school-header">
                        <h1>Lokmanya Vidya Niketan</h1>
                        <h2>HIGHER SECONDARY SCHOOL</h2>
                        <p>KESAR BAGH ROAD, LOKMANYA NAGAR, INDORE (M.P.)</p>
                        <p>C.B.S.E. Affiliation No. 1030429 &nbsp;|&nbsp; Phone: 0731-2360169, 9479460169</p>
                        <p>Email: lvnschool@hotmail.com &nbsp;|&nbsp; lvnschoolindore.org.in</p>
                    </div>
                @else
                    {{-- Blank Space to accommodate Pre-printed School Letterhead --}}
                    <div class="letterhead-blank-space" id="letterheadBlank"></div>
                @endif

                {{-- Certificate Title --}}
                <div class="cert-title" contenteditable="true">
                    {{ $certificate['cert_title'] ?? 'CHARACTER - CERTIFICATE' }}
                </div>

                {{-- Academic Session --}}
                <div class="cert-session" contenteditable="true">
                    {{ $certificate['session_year'] ?? '2025-26' }}
                </div>

                {{-- Paragraph 1: Main Conduct Statement with Underlined Student Details --}}
                <div class="cert-paragraph" contenteditable="true">
                    {!! $certificate['paragraph_1'] !!}
                </div>

                {{-- Paragraph 2: Examination Passed --}}
                <div class="cert-paragraph" contenteditable="true">
                    {!! $certificate['paragraph_2'] !!}
                </div>

                {{-- Paragraph 3: Future Wish --}}
                <div class="cert-paragraph" contenteditable="true">
                    {!! $certificate['paragraph_3'] !!}
                </div>

                {{-- Signature Area (Pushed to bottom of the border box) --}}
                <div class="signature-area">
                    <div class="sig-block">
                        <div class="sig-space"></div>
                        <div class="sig-title" contenteditable="true">PRINCIPAL</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function adjustTopSpacing(heightMm) {
            const blankEl = document.getElementById('letterheadBlank');
            if (blankEl) {
                blankEl.style.height = heightMm + 'mm';
            }
        }

        function adjustEqualMargin(marginMm) {
            document.documentElement.style.setProperty('--equal-margin', marginMm + 'mm');
        }

        function toggleBorder(hasBorder) {
            const box = document.getElementById('certBorderBox');
            if (box) {
                box.style.border = hasBorder ? '2px solid #111111' : 'none';
            }
        }
    </script>
</body>
</html>
