<style>
    @page { size: A4 landscape; margin: 4mm; }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 13px; color: #000; }

    .page { width: 100%; page-break-inside: avoid; }

    /* Dompdf-safe 2x2 landscape layout (tables render reliably) */
    .bulk-table { width: 100%; border-collapse: separate; border-spacing: 5mm; table-layout: fixed; page-break-inside: avoid; }
    .bulk-table tr,
    .bulk-td { page-break-inside: avoid; }
    .bulk-td { width: 50%; vertical-align: top; }

    .admit-card { border: 2px solid #000; padding: 8px 10px; position: relative; overflow: hidden; page-break-inside: avoid; }
    .admit-card:before { content: ''; position: absolute; inset: 4px; border: 1px solid #000; pointer-events: none; }
    .admit-card-inner { position: relative; z-index: 1; }

    .admit-card.bulk-card { height: 86mm; padding: 8px 10px; }
    .admit-card.single-card { width: 50%; height: 86mm; padding: 8px 10px; margin: 0 auto; }

    .school-logo { display: block; width: 82%; max-width: 330px; height: auto; margin: 0 auto 3px auto; }
    .exam-title { text-align: center; font-weight: 700; margin: 10px 0; font-size: 14px; text-transform: uppercase; }
    .sub-title { text-align: center; font-weight: 700; margin: 4px 0 8px 0; font-size: 16px; letter-spacing: 1px; text-decoration: underline; }

    .details p { margin: 6px 0; font-size: 14px; font-weight: 700; }
    .details .row-line { display: flex; justify-content: space-between; gap: 10px; }
    .details .row-line span { display: inline-block; }

    .room-box { display: inline-block; border: 2px solid #000; padding: 2px 10px; font-size: 18px; font-weight: 700; line-height: 1.1; min-width: 60px; text-align: center; }

    .installment-row { margin-top: 10px; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; }
    .installment-grid { border-collapse: collapse; }
    .installment-grid td { border: 1px solid #000; width: 40px; height: 28px; text-align: center; font-size: 14px; font-weight: 700; }

    .sign-row { margin-top: 50px; display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; }
    .sign-row span { width: 33.333%; }
    .sign-row span:nth-child(1) { text-align: left; }
    .sign-row span:nth-child(2) { text-align: center; }
    .sign-row span:nth-child(3) { text-align: right; }

    /* card scaling for bulk and single-student print */
    .bulk-card .school-logo,
    .single-card .school-logo { width: 82%; max-width: 330px; }
    .bulk-card .exam-title,
    .single-card .exam-title { font-size: 18px; }
    .bulk-card .sub-title,
    .single-card .sub-title { font-size: 15px; margin-bottom: 7px; }
    .bulk-card .details p,
    .single-card .details p { font-size: 13px; margin: 5px 0; }
    .bulk-card .room-box,
    .single-card .room-box { font-size: 16px; min-width: 54px; padding: 2px 8px; }
    .bulk-card .installment-row,
    .single-card .installment-row { font-size: 12px; margin-top: 8px; }
    .bulk-card .installment-grid td,
    .single-card .installment-grid td { width: 36px; height: 24px; font-size: 12px; }
    .bulk-card .sign-row,
    .single-card .sign-row { margin-top: 28px; font-size: 11px; }

    @media print {
        .page-break { page-break-after: always; }
    }
</style>

@php
    $examHeading = !empty($exam) ? trim(($exam->exam_name ?? '') . ' ' . ($exam->exam_type ?? '')) : 'I TERM (HALF YEARLY)';
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
                    <div class="exam-title">{{ $examHeading }} EXAMINATION ({{ str_replace('_', '-', $sessionName) }})</div>
                    <div class="sub-title">ADMIT CARD</div>

                    <div class="details">
                        <p>Name of the Student :- {{ $student->student_name }}</p>
                        <p>Class / Section :- {{ $student->class_name }} - {{ $student->section_name }}</p>
                        <p class="row-line">
                            <span>Roll No. : {{ $student->roll_no }}</span>
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
                                            <div class="exam-title">{{ $examHeading }} EXAMINATION ({{ str_replace('_', '-', $sessionName) }})</div>
                                            <div class="sub-title">ADMIT CARD</div>

                                            <div class="details">
                                                <p>Name of the Student :- {{ $student->student_name }}</p>
                                                <p>Class / Section :- {{ $student->class_name }} - {{ $student->section_name }}</p>
                                                <p class="row-line">
                                                    <span>Roll No. : {{ $student->roll_no }}</span>
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
