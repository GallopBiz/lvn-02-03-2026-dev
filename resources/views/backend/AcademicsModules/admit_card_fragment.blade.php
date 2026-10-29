<style>
    @page { size: A4 landscape; margin: 16.5mm; }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px; color: #000; }

    .page {
        width: 264mm;
        height: 177mm;
        max-height: 177mm;
        box-sizing: border-box;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        page-break-after: always !important;
        break-after: page !important;
        overflow: hidden;
        margin: 0 auto;
        padding: 0;
        display: grid;
        grid-template-columns: 105.5mm 105.5mm;
        grid-template-rows: 80mm 80mm;
        gap: 17mm 53mm;
        justify-content: center;
        align-content: center;
    }
    .page:last-child {
        page-break-after: auto !important;
        break-after: auto !important;
    }
    .page.single-page {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media screen {
        .page {
            margin: 0 auto 24px auto;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    }

    .admit-card {
        width: 105.5mm;
        height: 70mm;
        max-width: 105.5mm;
        max-height: 70mm;
        border: 2px solid #000;
        position: relative;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        background: #fff;
        text-align: left;
        box-sizing: border-box;
        padding: 6px 10px;
        display: flex;
        flex-direction: column;
    }
    .admit-card:before {
        content: '';
        position: absolute;
        inset: 2px;
        border: 1px solid #000;
        pointer-events: none;
    }
    .admit-card-inner {
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .admit-card.bulk-card {
        margin: 0;
    }
    .admit-card.single-card {
        margin: 0 auto;
    }

    .school-logo { display: block; width: 68%; max-width: 210px; height: auto; margin: 0 auto 1px auto; }
    .exam-title { text-align: center; font-weight: 700; margin: 2px 0 1px 0; font-size: 15px; text-transform: uppercase; line-height: 1.15; }
    .sub-title { text-align: center; font-weight: 700; margin: 1px 0 3px 0; font-size: 13px; letter-spacing: 0.5px; text-decoration: underline; line-height: 1.15; }

    .details {
        margin: 10px 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .details p {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.25;
    }
    .details .row-line { display: flex; justify-content: space-between; align-items: center; gap: 6px; }
    .details .row-line span { display: inline-block; }

    .roll-no-wrapper { font-size: 14px; font-weight: 700; }
    .roll-no-value { font-size: 17.5px; font-weight: 800; padding: 0 3px; letter-spacing: 0.5px; }

    .room-box { display: inline-block; border: 2px solid #000; padding: 0 5px; font-size: 14px; font-weight: 700; line-height: 1.1; min-width: 38px; text-align: center; }

    .installment-row { margin-top: 4px; display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; }
    .installment-grid { border-collapse: collapse; }
    .installment-grid td { border: 1px solid #000; width: 30px; height: 16px; text-align: center; font-size: 11px; font-weight: 700; }

    .sign-row {
        margin-top: 45px;
        padding-top: 0px;
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 700;
    }
    .sign-row span { width: 33.333%; }
    .sign-row span:nth-child(1) { text-align: left; }
    .sign-row span:nth-child(2) { text-align: center; }
    .sign-row span:nth-child(3) { text-align: right; }

    @media print {
        @page { size: A4 landscape; margin: 12.5mm; }
        .page {
            margin: 0 auto !important;
            padding: 0 !important;
            width: 264mm !important;
            height: 177mm !important;
            max-height: 177mm !important;
            page-break-after: always !important;
            break-after: page !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            overflow: hidden !important;
            display: grid !important;
            grid-template-columns: 105.5mm 105.5mm !important;
            grid-template-rows: 70mm 70mm !important;
            gap: 30mm 53mm !important;
            justify-content: center !important;
            align-content: center !important;
        }
        .page:last-child {
            page-break-after: auto !important;
            break-after: auto !important;
        }
        .page.single-page {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
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
    <div class="page {{ $isSingle ? 'single-page' : '' }}">
        @if($isSingle)
            @php $student = $chunk->first(); @endphp
            <div class="admit-card single-card">
                <div class="admit-card-inner">
                    <img class="school-logo" src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="Lokmanya Vidya Niketan">
                    <div class="exam-title">{{ $examHeading }}</div>
                    <div class="sub-title">ADMIT CARD</div>

                    <div class="details">
                        <p>Name of the Student :- {{ $student->student_name }}</p>
                        <p>Class / Section :- {{ \App\Academic\AcademicHelper::formatClassSection($student->class_name, $student->section_name) }}</p>
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
            @foreach($chunk as $student)
                @if($student)
                    <div class="admit-card bulk-card">
                        <div class="admit-card-inner">
                            <img class="school-logo" src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="Lokmanya Vidya Niketan">
                            <div class="exam-title">{{ $examHeading }}</div>
                            <div class="sub-title">ADMIT CARD</div>

                            <div class="details">
                                <p>Name of the Student :- {{ $student->student_name }}</p>
                                <p>Class / Section :- {{ \App\Academic\AcademicHelper::formatClassSection($student->class_name, $student->section_name) }}</p>
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
            @endforeach
        @endif
    </div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach