@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4 topper-report">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <div>
            <h2 class="mb-1">Topper List &amp; Result Analysis</h2>
            <p class="text-muted mb-0">Exam merit list ranked by each student's overall percentage.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('academic.topper-list.index') }}" class="row align-items-end">
                <div class="form-group col-12 col-md-6 col-xl-2">
                    <label for="session" class="font-weight-bold">Academic Session</label>
                    <select class="form-control" name="session" id="session" onchange="this.form.submit()">
                        @foreach($sessionOptions as $sessionOption)
                            <option value="{{ $sessionOption }}" {{ $selectedSession === $sessionOption ? 'selected' : '' }}>
                                {{ str_replace('_', '-', $sessionOption) }}
                            </option>
                        @endforeach
                        @if(!$sessionOptions->contains($selectedSession))
                            <option value="{{ $selectedSession }}" selected>{{ str_replace('_', '-', $selectedSession) }}</option>
                        @endif
                    </select>
                </div>
                <div class="form-group col-12 col-md-6 col-xl-2">
                    <label for="class_name" class="font-weight-bold">Class</label>
                    <select class="form-control" name="class_name" id="class_name" onchange="this.form.submit()">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class }}" {{ (string) request('class_name') === (string) $class ? 'selected' : '' }}>
                                Class {{ \App\Academic\AcademicHelper::toRoman($class) ?: $class }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-6 col-xl-2">
                    <label for="ranking_type" class="font-weight-bold">Ranking Type</label>
                    <select class="form-control" name="ranking_type" id="ranking_type" onchange="this.form.submit()">
                        <option value="all_sections" {{ $rankingType === 'all_sections' ? 'selected' : '' }}>All Sections Combined</option>
                        <option value="section" {{ $rankingType === 'section' ? 'selected' : '' }}>Individual Section</option>
                        <option value="stream_wise" {{ $rankingType === 'stream_wise' ? 'selected' : '' }} {{ !$isSeniorClass ? 'disabled' : '' }}>Stream-Wise (Classes 11 &amp; 12)</option>
                    </select>
                </div>
                @if($rankingType === 'section')
                <div class="form-group col-12 col-md-6 col-xl-2">
                    <label for="section_name" class="font-weight-bold">Section</label>
                    <select class="form-control" name="section_name" id="section_name" onchange="this.form.submit()">
                        <option value="">Select Section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section }}" {{ (string) request('section_name') === (string) $section ? 'selected' : '' }}>
                                {{ $section }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="form-group col-12 col-md-6 col-xl-2">
                    <label for="exam_id" class="font-weight-bold">Examination</label>
                    <select class="form-control" name="exam_id" id="exam_id" onchange="this.form.submit()">
                        <option value="">Select Examination</option>
                        @foreach($groupedExams as $examOption)
                            <option value="{{ $examOption['id'] }}" {{ (string) request('exam_id') === (string) $examOption['id'] ? 'selected' : '' }}>
                                {{ $examOption['display_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-xl-2 d-flex">
                    <button class="btn btn-primary mr-2" type="submit">Show Report</button>
                    <a class="btn btn-outline-secondary" href="{{ route('academic.topper-list.index', ['session' => $selectedSession]) }}">Clear</a>
                </div>
            </form>
        </div>
    </div>

    @if(!$hasFilters)
        <div class="alert alert-info">Select an academic session, class, ranking type, and examination to view the merit list. Individual Section mode also requires a section.</div>
    @elseif($rankGroups->isEmpty())
        <div class="alert alert-warning">No student result marks were found for the selected filters.</div>
    @else
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h4 class="mb-1">{{ $rankGroups->count() > 1 ? 'Stream-Wise Merit Lists' : $rankGroups->first()['label'] }}</h4>
                <div class="text-muted">{{ $selectedExam->exam_title ?: $selectedExam->exam_name }} &middot; Session {{ str_replace('_', '-', $sessionYear) }}</div>
            </div>
            <a class="btn btn-success mt-2 mt-md-0" target="_blank" href="{{ route('academic.topper-list.print', request()->only('session', 'class_name', 'section_name', 'exam_id', 'ranking_type')) }}">
                <i class="i-Printer mr-1"></i> Print Report
            </a>
        </div>

        @foreach($rankGroups as $rankGroup)
            <section class="topper-section mb-4">
                <h3 class="section-title">
                    {{ $rankingType === 'stream_wise' ? $rankGroup['label'] . ' Stream' : $rankGroup['label'] }} &mdash; Top 3 Toppers
                </h3>
                <div class="row">
                    @php
                        $medals = ['🥇', '🥈', '🥉'];
                    @endphp
                    @foreach($rankGroup['top_students'] as $index => $topper)
                        @php
                            $photoUrl = trim($topper['photo']);
                            $photoSrc = $photoUrl !== ''
                                ? (preg_match('/^https?:\/\//i', $photoUrl) ? $photoUrl : asset($photoUrl))
                                : null;
                            $studentInitial = mb_strtoupper(mb_substr(trim($topper['student_name']), 0, 1));
                        @endphp
                        <div class="col-12 col-md-4 mb-3">
                            <article class="topper-card topper-rank-{{ $topper['rank'] }}">
                                <div class="topper-medal">{{ $medals[$index] }}</div>
                                <div class="topper-position">{{ $index + 1 }}{{ $index === 0 ? 'st' : ($index === 1 ? 'nd' : 'rd') }} Position</div>
                                @if($photoSrc)
                                    <img class="topper-photo" src="{{ $photoSrc }}" alt="{{ $topper['student_name'] }}">
                                @else
                                    <div class="topper-photo topper-initial">{{ $studentInitial ?: '?' }}</div>
                                @endif
                                <h4 class="topper-name">{{ $topper['student_name'] }}</h4>
                                <div class="topper-class">Class {{ \App\Academic\AcademicHelper::toRoman($topper['class_name']) ?: $topper['class_name'] }} - {{ $topper['section_name'] }}</div>
                                <div class="topper-roll">Roll No: {{ $topper['roll_no'] ?: '-' }}</div>
                                <div class="topper-score">
                                    <span>{{ number_format($topper['total_obtained'], 1) }}</span>
                                    <small>of {{ number_format($topper['total_max'], 1) }} marks</small>
                                </div>
                                <div class="topper-percentage">{{ number_format($topper['percentage'], 2) }}%</div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="merit-section mb-4">
                <h3 class="section-title">{{ $rankingType === 'stream_wise' ? $rankGroup['label'] . ' Stream' : $rankGroup['label'] }} &mdash; Complete Merit List</h3>
                <div class="table-responsive bg-white">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student Name</th>
                                <th>Roll Number</th>
                                <th>Class &amp; Section</th>
                                <th>Total Obtained</th>
                                <th>Total Maximum</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rankGroup['students'] as $rankedStudent)
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $rankedStudent['rank'] }}</td>
                                    <td>{{ $rankedStudent['student_name'] }}</td>
                                    <td class="text-center">{{ $rankedStudent['roll_no'] ?: '-' }}</td>
                                    <td class="text-center">
                                        Class {{ \App\Academic\AcademicHelper::toRoman($rankedStudent['class_name']) ?: $rankedStudent['class_name'] }} - {{ $rankedStudent['section_name'] }}
                                    </td>
                                    <td class="text-right">{{ number_format($rankedStudent['total_obtained'], 1) }}</td>
                                    <td class="text-right">{{ number_format($rankedStudent['total_max'], 1) }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($rankedStudent['percentage'], 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    @endif
</div>

<style>
    .topper-report .section-title {
        color: #23395d;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .topper-card {
        height: 100%;
        min-height: 330px;
        padding: 1.5rem 1rem;
        text-align: center;
        background: linear-gradient(145deg, #fff, #f3f6fb);
        border: 1px solid #dfe6f0;
        border-radius: 14px;
        box-shadow: 0 6px 20px rgba(35, 57, 93, .10);
    }

    .topper-rank-1 {
        border-top: 5px solid #e8b923;
    }

    .topper-rank-2 {
        border-top: 5px solid #9aa8bb;
    }

    .topper-rank-3 {
        border-top: 5px solid #bd7953;
    }

    .topper-medal {
        font-size: 2rem;
        line-height: 1;
    }

    .topper-position {
        margin: .45rem 0 .8rem;
        color: #53647b;
        font-weight: 700;
    }

    .topper-photo {
        width: 82px;
        height: 82px;
        margin-bottom: .8rem;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .18);
    }

    .topper-initial {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: #5272a0;
        font-size: 2rem;
        font-weight: 700;
    }

    .topper-name {
        margin: 0;
        color: #203653;
        font-size: 1.15rem;
        font-weight: 700;
    }

    .topper-class,
    .topper-roll {
        color: #64748b;
        font-size: .9rem;
    }

    .topper-score {
        display: flex;
        justify-content: center;
        align-items: baseline;
        gap: .35rem;
        margin-top: .8rem;
        color: #334155;
    }

    .topper-score span {
        font-size: 1.15rem;
        font-weight: 700;
    }

    .topper-percentage {
        margin-top: .2rem;
        color: #176b53;
        font-size: 1.6rem;
        font-weight: 800;
    }

    .merit-section thead {
        color: #fff;
        background: #263f64;
    }

    @media print {
        .topper-report .card,
        .topper-card {
            box-shadow: none !important;
        }

        .topper-section,
        .merit-section {
            break-inside: avoid;
        }
    }
</style>
@endsection
