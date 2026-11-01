<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Student_registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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
        $students = Student_registration::query()
            ->activeForListings()
            ->where('status', 'r')
            ->get(['class_name', 'json_str']);

        $rowsByClass = [];
        foreach ($students as $student) {
            $className = trim((string) $student->class_name);
            $className = $className !== '' ? $className : 'Unassigned';
            $details = json_decode($student->json_str ?? '', true);
            $details = is_array($details) ? $details : [];
            $category = $this->categoryKey($details['category'] ?? '');
            $gender = $this->genderKey($details['gender'] ?? '');

            if (!isset($rowsByClass[$className])) {
                $rowsByClass[$className] = [
                    'class_name' => $className,
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

        $databaseName = (string) Config::get('database.connections.dynamic.database', '');
        $academicSession = preg_match('/^\d{4}_\d{4}$/', $databaseName)
            ? str_replace('_', '-', $databaseName)
            : $databaseName;

        return view('backend.AcademicsModules.caste_wise_report', [
            'classOptions' => $classOptions,
            'rows' => $rows,
            'totals' => $totals,
            'categories' => self::CATEGORIES,
            'selectedClass' => $selectedClass,
            'academicSession' => $academicSession,
        ]);
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
