<style>
    @page { size: A4 landscape; margin: 5mm 8mm; }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px; color: #000; }

    .page {
        width: 100%;
        height: 196mm;
        max-height: 196mm;
        box-sizing: border-box;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        page-break-after: always !important;
        break-after: page !important;
        overflow: hidden;
        margin: 0 !important;
        padding: 0 !important;
    }
    .page:last-child {
        page-break-after: auto !important;
        break-after: auto !important;
    }

    @media screen {
        .page {
            margin: 0 auto 24px auto;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    }

    /* 2x2 landscape layout ensuring exactly 4 cards fit on each A4 page */
    .bulk-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8mm 6mm;
        table-layout: fixed;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .bulk-table tr,
    .bulk-td {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .bulk-td {
        width: 50%;
        vertical-align: top;
        text-align: center;
        padding: 0;
    }
    .bulk-table tr:first-child .bulk-td {
        padding-bottom: 6mm;
    }

    .admit-card {
        border: 2px solid #000;
        position: relative;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        background: #fff;
        text-align: left;
    }
    .admit-card:before {
        content: '';
        position: absolute;
        inset: 3px;
        border: 1px solid #000;
        pointer-events: none;
    }
    .admit-card-inner {
        position: relative;
        z-index: 1;
    }

    .admit-card.bulk-card {
        width: 95%;
        max-width: 124mm;
        height: 82mm;
        max-height: 82mm;
        box-sizing: border-box;
        margin: 0 auto;
        padding: 10px 12px;
    }
    .admit-card.single-card {
        width: 48%;
        max-width: 124mm;
        height: 82mm;
        max-height: 82mm;
        box-sizing: border-box;
        padding: 6px 12px;
        margin: 10mm auto;
    }

    .school-logo { display: block; width: 72%; max-width: 250px; height: auto; margin: 0 auto 1px auto; }
    .exam-title { text-align: center; font-weight: 700; margin: 10px 0 1px 0; font-size: 16px; text-transform: uppercase; line-height: 1.2; }
    .sub-title { text-align: center; font-weight: 700; margin: 1px 0 4px 0; font-size: 13px; letter-spacing: 1px; text-decoration: underline; line-height: 1.2; }

    .details {
        margin: 8px 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .details p {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.3;
    }
    .details .row-line { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .details .row-line span { display: inline-block; }

    .roll-no-wrapper { font-size: 14px; font-weight: 700; }
    .roll-no-value { font-size: 18px; font-weight: 800; padding: 0 4px; letter-spacing: 0.5px; }

    .room-box { display: inline-block; border: 2px solid #000; padding: 0 8px; font-size: 14px; font-weight: 700; line-height: 1.1; min-width: 42px; text-align: center; }

    .installment-row { margin-top: 6px; display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; }
    .installment-grid { border-collapse: collapse; }
    .installment-grid td { border: 1px solid #000; width: 32px; height: 18px; text-align: center; font-size: 11px; font-weight: 700; }

    .sign-row { margin-top: 18px; display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; }
    .sign-row span { width: 33.333%; }
    .sign-row span:nth-child(1) { text-align: left; }
    .sign-row span:nth-child(2) { text-align: center; }
    .sign-row span:nth-child(3) { text-align: right; }

    /* card scaling for bulk and single-student print */
    .bulk-card .school-logo,
    .single-card .school-logo { width: 72%; max-width: 250px; }
    .bulk-card .exam-title,
    .single-card .exam-title { font-size: 16px; }
    .bulk-card .sub-title,
    .single-card .sub-title { font-size: 13px; margin-bottom: 12px; margin-top: 6px; }
    .bulk-card .details,
    .single-card .details {
        margin: 8px 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .bulk-card .details p,
    .single-card .details p {
        font-size: 14px;
        margin: 0;
        line-height: 1.3;
    }
    .bulk-card .room-box,
    .single-card .room-box { font-size: 14px; min-width: 42px; padding: 0 6px; }
    .bulk-card .installment-row,
    .single-card .installment-row { font-size: 12px; margin-top: 6px; }
    .bulk-card .installment-grid td,
    .single-card .installment-grid td { width: 32px; height: 18px; font-size: 11px; }
    .bulk-card .sign-row,
    .single-card .sign-row {
        margin-top: 50px;
        font-size: 11px;
    }

    @media print {
        .page {
            margin: 0 !important;
            padding: 0 !important;
            height: 196mm !important;
            max-height: 196mm !important;
            page-break-after: always !important;
            break-after: page !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
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

@php
    $examHeading = 'I TERM (HALF YEARLY)';
    if (!empty($exam)) {
        $examHeading = trim((string) ($exam->exam_title ?? ''))
            ?: trim((string) ($exam->exam_name ?? ''));
    }
    $isSingle = isset($students) && $students->count() === 1;
    $perPage = $isSingle ? 1 : 4;
@endphp

@foreach($students->chunk($perPage) as $chunk)
    <div class="page">
        @php
            $cells = $chunk->values();
            $rows = [];
            for ($i = 0; $i < 4; $i += 2) {
                $rows[] = [$cells->get($i), $cells->get($i + 1)];
            }
        @endphp

        @if($isSingle)
            @php $student = $cells->first(); @endphp
            <div class="admit-card single-card">
                <div class="admit-card-inner">
                    <img class="school-logo" src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="Lokmanya Vidya Niketan">
                    <div class="exam-title">{{ $examHeading }}</div>
                    <div class="sub-title">ADMIT CARD</div>

                    <div class="details">
                        <p>Name of the Student :- {{ $student->student_name }}</p>
                        <p>Class / Section :- {{ $student->class_name }} - {{ $student->section_name }}</p>
                        <p class="row-line">
                            <span class="roll-no-wrapper">Roll No. : <span class="roll-no-value">{{ $student->roll_no }}</span></span>
                            <span>ROOM NO:- <span class="room-box">{{ $student->room_no ?? '-' }}</span></span>
                        </p>
                    </div>

                    <div class="installment-row">
                        <span>Installments paid -</span>
                        <table class="installment-grid">
                            <tr>
                                <td>1<sup>st</sup></td>
                                <td>2<sup>nd</sup></td>
                                <td>3<sup>rd</sup></td>
                                <td>4<sup>th</sup></td>
                            </tr>
                        </table>
                    </div>

                    <div class="sign-row">
                        <span>Student's Signature</span>
                        <span>Class Teacher</span>
                        <span>Principal</span>
                    </div>
                </div>
            </div>
        @else
            <table class="bulk-table">
                @foreach($rows as $row)
                    <tr>
                        @foreach($row as $student)
                            <td class="bulk-td">
                                @if($student)
                                    <div class="admit-card bulk-card">
                                        <div class="admit-card-inner">
                                            <img class="school-logo" src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="Lokmanya Vidya Niketan">
                                            <div class="exam-title">{{ $examHeading }}</div>
                                            <div class="sub-title">ADMIT CARD</div>

                                            <div class="details">
                                                <p>Name of the Student :- {{ $student->student_name }}</p>
                                                <p>Class / Section :- {{ $student->class_name }} - {{ $student->section_name }}</p>
                                                <p class="row-line">
                                                    <span class="roll-no-wrapper">Roll No. : <span class="roll-no-value">{{ $student->roll_no }}</span></span>
                                                    <span>ROOM NO:- <span class="room-box">{{ $student->room_no ?? '-' }}</span></span>
                                                </p>
                                            </div>

                                            <div class="installment-row">
                                                <span>Installments paid -</span>
                                                <table class="installment-grid">
                                                    <tr>
                                                        <td>1<sup>st</sup></td>
                                                        <td>2<sup>nd</sup></td>
                                                        <td>3<sup>rd</sup></td>
                                                        <td>4<sup>th</sup></td>
                                                    </tr>
                                                </table>
                                            </div>

                                            <div class="sign-row">
                                                <span>Student's Signature</span>
                                                <span>Class Teacher</span>
                                                <span>Principal</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        @endif
    </div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
