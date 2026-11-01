@extends('backend.layouts.main')

@section('main-container')
@php
    $displayClassName = static function ($className) {
        $romanNumerals = [
            1000 => 'M',
            900 => 'CM',
            500 => 'D',
            400 => 'CD',
            100 => 'C',
            90 => 'XC',
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I',
        ];

        return preg_replace_callback('/(?<!\d)\d+(?!\d)/', static function ($matches) use ($romanNumerals) {
            $number = (int) $matches[0];
            if ($number < 1 || $number > 3999) {
                return $matches[0];
            }

            $roman = '';
            foreach ($romanNumerals as $value => $numeral) {
                while ($number >= $value) {
                    $roman .= $numeral;
                    $number -= $value;
                }
            }

            return $roman;
        }, (string) $className);
    };
@endphp
<style>
    .caste-report { color: #24324b; }
    .caste-report-card { background: #fff; border: 1px solid #cbd5e0; border-radius: 6px; padding: 18px; }
    .caste-report-heading { text-align: center; margin-bottom: 18px; }
    .caste-report-heading h2 { margin: 0; font-size: 22px; font-weight: 700; }
    .caste-report-heading p { margin: 4px 0 0; color: #64748b; }
    .caste-report-controls { display: flex; justify-content: space-between; align-items: end; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .caste-report-filter { display: flex; align-items: end; gap: 8px; flex-wrap: wrap; }
    .caste-report-filter label { display: block; margin-bottom: 4px; font-weight: 600; }
    .caste-report-filter select { min-width: 180px; }
    .caste-report-table-wrap { overflow-x: auto; }
    .caste-report-table { width: 100%; min-width: 1180px; border-collapse: collapse; text-align: center; font-size: 12px; }
    .caste-report-table th, .caste-report-table td { border: 1px solid #9aa8b8; padding: 6px 5px; }
    .caste-report-table thead th { background: #edf1f7; color: #24324b; }
    .caste-report-table thead tr:first-child th { background: #e2e8f0; }
    .caste-report-table .class-cell { text-align: center; font-weight: 600; }
    .caste-report-table tfoot th, .caste-report-table tfoot td { background: #f1f5f9; font-weight: 700; }
    .caste-report-empty { padding: 24px; text-align: center; color: #64748b; }
    @media print {
        @page { size: landscape; margin: 10mm; }
        html, body { width: 100%; height: auto; margin: 0 !important; padding: 0 !important; background: #fff !important; }
        body * { visibility: hidden !important; }
        .caste-report, .caste-report * { visibility: visible !important; }
        .caste-report { position: absolute; top: 0; left: 0; width: 100%; }
        .caste-report-controls, .no-print { display: none !important; }
        .caste-report-card { border: 0; padding: 0; }
        .caste-report-table-wrap { overflow: visible; }
        .caste-report-table { min-width: 0; font-size: 8px; }
        .caste-report-table th, .caste-report-table td { padding: 3px 2px; }
        .caste-report-table thead { display: table-header-group; }
        .caste-report-table tr { break-inside: avoid; }
    }
</style>

<div class="caste-report">
    <div class="caste-report-card">
        <div class="caste-report-heading">
            <h2>Lokmanya Vidya Niketan</h2>
            <p>Caste Wise Strength of Institute</p>
            @if($academicSession)
                <p>Academic Session: {{ $academicSession }}</p>
            @endif
        </div>

        <div class="caste-report-controls no-print">
            <form method="GET" action="{{ route('academic.caste-wise-report') }}" class="caste-report-filter">
                <div>
                    <label for="casteReportClass">Class</label>
                    <select class="form-control" name="class_name" id="casteReportClass">
                        <option value="">All Classes</option>
                        @foreach($classOptions as $className)
                            <option value="{{ $className }}" @selected($selectedClass === $className)>{{ $displayClassName($className) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Show Report</button>
                <a href="{{ route('academic.caste-wise-report') }}" class="btn btn-outline-secondary">Reset</a>
            </form>
            <button type="button" class="btn btn-dark" onclick="window.print(); return false;">Print / Save PDF</button>
        </div>

        <div class="caste-report-table-wrap">
            <table class="caste-report-table">
                <thead>
                    <tr>
                        <th rowspan="2">Class</th>
                        <th colspan="3">All Castes</th>
                        @foreach($categories as $label)
                            <th colspan="3">{{ $label }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @for($group = 0; $group < count($categories) + 1; $group++)
                            <th>Boys</th>
                            <th>Girls</th>
                            <th>Total</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="class-cell">{{ $displayClassName($row['class_name']) }}</td>
                            <td>{{ $row['overall']['boys'] }}</td>
                            <td>{{ $row['overall']['girls'] }}</td>
                            <td>{{ $row['overall']['total'] }}</td>
                            @foreach($categories as $category => $label)
                                <td>{{ $row['categories'][$category]['boys'] }}</td>
                                <td>{{ $row['categories'][$category]['girls'] }}</td>
                                <td>{{ $row['categories'][$category]['total'] }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td class="caste-report-empty" colspan="{{ 1 + (count($categories) + 1) * 3 }}">
                                No active registered students found for this selection.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows)
                    <tfoot>
                        <tr>
                            <th class="class-cell">Total</th>
                            <td>{{ $totals['overall']['boys'] }}</td>
                            <td>{{ $totals['overall']['girls'] }}</td>
                            <td>{{ $totals['overall']['total'] }}</td>
                            @foreach($categories as $category => $label)
                                <td>{{ $totals['categories'][$category]['boys'] }}</td>
                                <td>{{ $totals['categories'][$category]['girls'] }}</td>
                                <td>{{ $totals['categories'][$category]['total'] }}</td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
