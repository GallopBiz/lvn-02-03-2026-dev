<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Student_registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CasteWiseReportController extends Controller
{
    private const CATEGORIES = [
        'SC' => 'SC',
        'ST' => 'ST',
        'OBC' => 'OBC',
        'GEN' => 'General / UR',
        'OTHER' => 'Other / unclassified',
    ];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'class_name' => 'nullable|string|max:100',
        ]);
        $selectedClass = $validated['class_name'] ?? '';
        $reportAccess = app(\App\Services\StaffReportAccessService::class);
        $isStaffUser = $reportAccess->isStaffUser();

        $teacherScope = collect();
        if ($isStaffUser) {
            // Staff members MUST be assigned as Class Teacher to access caste-wise reports.
            // If they are not a Class Teacher, deny any class filtering with 403,
            // or show an empty report state.
            if (!$reportAccess->isClassTeacher()) {
                if ($selectedClass !== '') {
                    abort(403, 'You are not authorized to access reports for this class.');
                }

                return view('backend.AcademicsModules.caste_wise_report', [
                    'classOptions' => [],
                    'rows' => [],
                    'totals' => [
                        'overall' => $this->emptyCounts(),
                        'categories' => array_fill_keys(array_keys(self::CATEGORIES), $this->emptyCounts()),
                    ],
                    'categories' => self::CATEGORIES,
                    'selectedClass' => '',
                    'academicSession' => $this->academicSession(),
                    'reportRouteName' => 'staff.academic.caste-wise-report',
                    'isStaffUser' => true,
                ]);
            }

            if ($selectedClass !== '' && !$reportAccess->canAccessAsClassTeacher($selectedClass)) {
                abort(403, 'You are not authorized to access reports for this class.');
            }

            $teacherScope = $reportAccess->classTeacherScope();
        }

        $connection = config('database.connections.dynamic.database') ? 'dynamic' : null;
        $schema = Schema::connection($connection ?: 'dynamic');

        $columns = ['class_name', 'json_str'];
        if ($schema->hasColumn('student_registration', 'section_name')) {
            $columns[] = 'section_name';
        }
        if ($schema->hasColumn('student_registration', 'class_id')) {
            $columns[] = 'class_id';
        }

        $students = Student_registration::query()
            ->activeForListings()
            ->where('status', 'r')
            ->get($columns);

        if ($isStaffUser) {
            $students = $students->filter(function ($student) use ($teacherScope, $reportAccess, $schema) {
                $studentClassName = trim((string) $student->class_name);
                $studentClassId = isset($student->class_id) ? trim((string) $student->class_id) : '';

                $details = json_decode($student->json_str ?? '', true);
                $details = is_array($details) ? $details : [];
                $studentSection = trim((string) ($student->section_name ?? ($details['section_name'] ?? ($details['section'] ?? ''))));

                $hasSectionInfo = ($studentSection !== '')
                    || $schema->hasColumn('student_registration', 'section_name')
                    || isset($details['section_name'])
                    || isset($details['section']);

                foreach ($teacherScope as $scope) {
                    $classMatches = false;
                    if ($studentClassName !== '' && in_array($reportAccess->normalize($studentClassName), $scope['class_aliases'], true)) {
                        $classMatches = true;
                    } elseif ($studentClassId !== '' && in_array($reportAccess->normalize($studentClassId), $scope['class_aliases'], true)) {
                        $classMatches = true;
                    }

                    if (!$classMatches) {
                        continue;
                    }

                    $assignedSection = $scope['section_name'];
                    if ($assignedSection === '' || strcasecmp($assignedSection, 'All') === 0) {
                        return true;
                    }

                    if ($studentSection !== '' && strcasecmp($studentSection, $assignedSection) === 0) {
                        return true;
                    }

                    if (!$hasSectionInfo) {
                        return true;
                    }
                }

                return false;
            });
        }

        $rowsByClass = [];
        foreach ($students as $student) {
            $className = trim((string) $student->class_name);
            $className = $className !== '' ? $className : 'Unassigned';
            $details = json_decode($student->json_str ?? '', true);
            $details = is_array($details) ? $details : [];
            $category = $this->categoryKey($details['category'] ?? '');
            $gender = $this->genderKey($details['gender'] ?? '');

            if (!isset($rowsByClass[$className])) {
                $sectionLabel = '';
                if ($isStaffUser) {
                    $sections = $teacherScope
                        ->filter(fn ($s) => in_array($reportAccess->normalize($className), $s['class_aliases'], true))
                        ->pluck('section_name')
                        ->filter()
                        ->unique()
                        ->values();
                    if ($sections->isNotEmpty() && !$sections->contains('All')) {
                        $sectionLabel = $sections->implode(', ');
                    }
                }

                $rowsByClass[$className] = [
                    'class_name' => $className,
                    'section_name' => $sectionLabel,
                    'overall' => $this->emptyCounts(),
                    'categories' => array_fill_keys(array_keys(self::CATEGORIES), $this->emptyCounts()),
                ];
            }

            $rowsByClass[$className]['overall'][$gender]++;
            $rowsByClass[$className]['overall']['total']++;
            $rowsByClass[$className]['categories'][$category][$gender]++;
            $rowsByClass[$className]['categories'][$category]['total']++;
        }

        $classOptions = array_keys($rowsByClass);
        if ($isStaffUser) {
            $assignedClassOptions = $reportAccess->assignedClassTeacherClassOptions();
            $classOptions = $assignedClassOptions->isNotEmpty()
                ? $assignedClassOptions->all()
                : array_keys($rowsByClass);

            // Ensure empty assigned class row is present if no students registered yet
            foreach ($classOptions as $assignedClass) {
                if (!isset($rowsByClass[$assignedClass])) {
                    $sections = $teacherScope
                        ->filter(fn ($s) => in_array($reportAccess->normalize($assignedClass), $s['class_aliases'], true))
                        ->pluck('section_name')
                        ->filter()
                        ->unique()
                        ->values();
                    $sectionLabel = ($sections->isNotEmpty() && !$sections->contains('All'))
                        ? $sections->implode(', ')
                        : '';

                    $rowsByClass[$assignedClass] = [
                        'class_name' => $assignedClass,
                        'section_name' => $sectionLabel,
                        'overall' => $this->emptyCounts(),
                        'categories' => array_fill_keys(array_keys(self::CATEGORIES), $this->emptyCounts()),
                    ];
                }
            }

            $rowsByClass = array_intersect_key($rowsByClass, array_flip($classOptions));
        }
        usort($classOptions, 'strnatcasecmp');

        $rows = array_values($rowsByClass);
        usort($rows, fn ($left, $right) => strnatcasecmp($left['class_name'], $right['class_name']));
        if ($selectedClass !== '') {
            $rows = array_values(array_filter(
                $rows,
                fn ($row) => $row['class_name'] === $selectedClass
            ));
        }

        $totals = [
            'overall' => $this->emptyCounts(),
            'categories' => array_fill_keys(array_keys(self::CATEGORIES), $this->emptyCounts()),
        ];
        foreach ($rows as $row) {
            foreach ($row['overall'] as $gender => $count) {
                $totals['overall'][$gender] += $count;
            }
            foreach ($row['categories'] as $category => $counts) {
                foreach ($counts as $gender => $count) {
                    $totals['categories'][$category][$gender] += $count;
                }
            }
        }

        return view('backend.AcademicsModules.caste_wise_report', [
            'classOptions' => $classOptions,
            'rows' => $rows,
            'totals' => $totals,
            'categories' => self::CATEGORIES,
            'selectedClass' => $selectedClass,
            'academicSession' => $this->academicSession(),
            'reportRouteName' => $isStaffUser ? 'staff.academic.caste-wise-report' : 'academic.caste-wise-report',
            'isStaffUser' => $isStaffUser,
        ]);
    }

    private function academicSession(): string
    {
        $databaseName = (string) Config::get('database.connections.dynamic.database', '');
        return preg_match('/^\d{4}_\d{4}$/', $databaseName)
            ? str_replace('_', '-', $databaseName)
            : $databaseName;
    }

    private function emptyCounts(): array
    {
        return [
            'boys' => 0,
            'girls' => 0,
            'other' => 0,
            'total' => 0,
        ];
    }

    private function categoryKey($value): string
    {
        $category = strtoupper(trim((string) $value));
        if ($category === 'GENERAL' || $category === 'UR') {
            $category = 'GEN';
        }

        return array_key_exists($category, self::CATEGORIES) ? $category : 'OTHER';
    }

    private function genderKey($value): string
    {
        return match (strtolower(trim((string) $value))) {
            'male', 'boy', 'boys' => 'boys',
            'female', 'girl', 'girls' => 'girls',
            default => 'other',
        };
    }
}
