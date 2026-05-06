<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admit Card</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  @page {
    size: A4 portrait;
    margin: 0;
  }

  body {
    width: 210mm;
    font-family: Arial, sans-serif;
    background: white;
    font-size: 12px;
    color: #000;
  }

  .page {
    width: 210mm;
    height: 297mm;
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: 0;
    padding: 4mm;
  }

  .card {
    border: 2px solid black;
    padding: 3mm 4mm;
    display: flex;
    flex-direction: column;
    gap: 0;
    margin: 1.5mm;
  }

  .card-header {
    text-align: center;
    border-bottom: 1.5px solid black;
    padding-bottom: 2mm;
    margin-bottom: 1.5mm;
  }

  .school-logo-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4mm;
    margin-bottom: 1mm;
  }

  svg.diamond-logo {
    width: 12mm;
    height: 12mm;
    flex-shrink: 0;
  }

  .school-name {
    font-size: 13pt;
    font-weight: bold;
    letter-spacing: 0.5px;
  }

  .school-name span {
    text-decoration: underline;
    text-underline-offset: 2px;
  }

  .cbse {
    font-size: 7pt;
    color: #333;
  }

  .exam-title {
    font-size: 8.5pt;
    font-weight: bold;
    text-align: center;
    line-height: 1.3;
  }

  .admit-card-title {
    font-size: 9pt;
    font-weight: bold;
    text-decoration: underline;
    text-align: center;
  }

  .card-body {
    font-size: 8pt;
    display: flex;
    flex-direction: column;
    gap: 1.5mm;
  }

  .field-row {
    display: flex;
    align-items: baseline;
    gap: 1mm;
  }

  .field-label {
    font-weight: bold;
    white-space: nowrap;
    font-size: 8pt;
  }

  .field-value {
    font-weight: bold;
    font-size: 10pt;
  }

  .room-box {
    display: inline-block;
    border: 2px solid black;
    padding: 0.5mm 3mm;
    font-weight: bold;
    font-size: 11pt;
    min-width: 10mm;
    text-align: center;
  }

  .inline-row {
    display: flex;
    align-items: baseline;
    gap: 4mm;
    flex-wrap: wrap;
  }

  .installments-row {
    display: flex;
    align-items: center;
    gap: 2mm;
  }

  .inst-boxes {
    display: flex;
    gap: 1mm;
  }

  .inst-box {
    border: 1px solid black;
    padding: 0.5mm 2.5mm;
    font-size: 7.5pt;
    min-width: 8mm;
    text-align: center;
  }

  .signatures {
    display: flex;
    justify-content: space-between;
    margin-top: 2mm;
    padding-top: 2mm;
    border-top: 1px solid #aaa;
  }

  .sig-item {
    font-size: 6.5pt;
    font-weight: bold;
    text-align: center;
  }

  .page-break { page-break-after: always; }
</style>
</head>
<body>

@php
    $examHeading = !empty($exam)
        ? trim(($exam->exam_name ?? '') . ' ' . ($exam->exam_type ?? ''))
        : 'I TERM (HALF YEARLY) EXAMINATION';
    $sessionDisplay = str_replace('_', '-', $sessionName ?? '');
    $cardsPerPage = 4;
@endphp

@foreach($students->chunk($cardsPerPage) as $pageIndex => $chunk)
<div class="page">

    @foreach($chunk as $student)
    <div class="card">
        <div class="card-header">
            <div class="school-logo-row">
                <svg class="diamond-logo" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
                    <rect x="5" y="5" width="50" height="50" fill="none" stroke="black" stroke-width="2"/>
                    <polygon points="30,8 52,30 30,52 8,30" fill="none" stroke="black" stroke-width="2"/>
                    <polygon points="30,16 44,30 30,44 16,30" fill="none" stroke="black" stroke-width="2"/>
                    <text x="30" y="33" text-anchor="middle" font-size="9" font-weight="bold" font-family="Arial">LVN</text>
                </svg>
                <div>
                    <div class="school-name"><span>L</span>OKMANYA <span>V</span>IDYA <span>N</span>IKETAN</div>
                    <div class="cbse">C.B.S.E. No. 1030429</div>
                </div>
            </div>
            <div class="exam-title">{{ strtoupper($examHeading) }} ({{ $sessionDisplay }})</div>
            <div class="admit-card-title">ADMIT CARD</div>
        </div>

        <div class="card-body">
            <div class="field-row">
                <span class="field-label">Name of the Student: -</span>
                <span class="field-value">{{ $student->student_name }}</span>
            </div>
            <div class="inline-row">
                <div class="field-row">
                    <span class="field-label">Class/ Section: -</span>
                    <span class="field-value">{{ $className }} - {{ $sectionName }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Roll No.:</span>
                    <span class="field-value">{{ $rollMap[$student->id] ?? '' }}</span>
                </div>
            </div>
            <div class="field-row" style="align-items:center;">
                <span class="field-label">ROOM NO:-</span>
                <span class="room-box">{{ $student->room_no ?? '' }}</span>
            </div>
            <div class="installments-row">
                <span class="field-label">Installments paid -</span>
                <div class="inst-boxes">
                    <div class="inst-box">1<sup style="font-size:5pt">st</sup></div>
                    <div class="inst-box">2<sup style="font-size:5pt">nd</sup></div>
                    <div class="inst-box">3<sup style="font-size:5pt">rd</sup></div>
                    <div class="inst-box">4<sup style="font-size:5pt">th</sup></div>
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="sig-item">Student's Signature</div>
            <div class="sig-item">Class Teacher</div>
            <div class="sig-item">Principal</div>
        </div>
    </div>
    @endforeach

</div>
@if(!$loop->last)
    <div class="page-break"></div>
@endif
@endforeach

</body>
</html>