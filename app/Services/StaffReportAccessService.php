<?php

namespace App\Services;

use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffReportAccessService
{
    private ?Collection $assignments = null;
    private array $classAliasesCache = [];
    private array $subjectAliasesCache = [];

    private const CLASS_FIELDS = [
        'class_id',
        'class_name',
        'classname',
        'class',
        'generated_class_id',
        'generated_class_name',
    ];
    private const SECTION_FIELDS = ['section_name', 'section'];
    private const SUBJECT_FIELDS = ['subject_id', 'subject_name', 'subject'];

    public function isStaffUser(): bool
    {
        return Auth::guard('staff')->check()
            && (
                request()->cookie('auth_portal') === 'staff'
                || (request()->hasSession() && request()->session()->get('auth_portal') === 'staff')
                || request()->is('staff', 'staff/*')
                || !Auth::guard('web')->check()
            );
    }

    public function appliesToRequest(Request $request): bool
    {
        if (!$this->isStaffUser()) {
            return false;
        }

        $routeName = strtolower((string) optional($request->route())->getName());
        $path = strtolower(trim($request->path(), '/'));

        return str_contains($routeName, 'report')
            || str_contains($routeName, 'marksheet')
            || str_contains($routeName, 'topper')
            || str_contains($routeName, 'result-analysis')
            || str_contains($routeName, 'attendance')
            || str_contains($routeName, 'attandence')
            || str_contains($routeName, 'attendence')
            || str_contains($routeName, 'attandance')
            || str_contains($path, 'report')
            || str_contains($path, 'marksheet')
            || str_contains($path, 'topper')
            || str_contains($path, 'result-analysis')
            || str_contains($path, 'attendance')
            || str_contains($path, 'attandence')
            || str_contains($path, 'attendence')
            || str_contains($path, 'attandance');
    }

    public function authorizeRequest(Request $request): void
    {
        if (!$this->appliesToRequest($request)) {
            return;
        }

        $classes = $this->filledValues($request, self::CLASS_FIELDS);
        $sections = $this->filledValues($request, self::SECTION_FIELDS);
        $subjects = $this->filledValues($request, self::SUBJECT_FIELDS);

        if (!$classes && !$sections && !$subjects) {
            return;
        }

        $section = $sections[0] ?? null;
        $class = $classes[0] ?? null;
        $subject = $subjects[0] ?? null;
        $authorized = $class !== null && $this->canAccess($class, $section, $subject);
        if ($authorized) {
            foreach (array_slice($classes, 1) as $additionalClass) {
                if (!$this->canAccess($additionalClass, $section, $subject)) {
                    $authorized = false;
                    break;
                }
            }
        }

        if (!$authorized) {
            $message = 'You are not authorized to access reports for this class, section, or subject.';
            if ($request->expectsJson() || $request->ajax()) {
                abort(response()->json(['error' => $message], 403));
            }

            abort(403, $message);
        }
    }

    public function canAccess($class, $section = null, $subject = null): bool
    {
        if (!$this->isStaffUser()) {
            return true;
        }

        if ($class === null || is_array($class) || is_array($section) || is_array($subject)) {
            return false;
        }

        $assignments = $this->staffAssignments();

        $classAliases = $this->classAliases((string) $class);
        if (!$classAliases) {
            return false;
        }

        $matchingClassRows = $assignments->filter(function ($assignment) use ($classAliases) {
            return $this->aliasesIntersect($classAliases, $this->classAliases((string) $assignment->class_id));
        });

        if ($matchingClassRows->isEmpty()) {
            return false;
        }

        if ($section !== null) {
            $matchingClassRows = $matchingClassRows->filter(function ($assignment) use ($section) {
                return $this->sameValue($assignment->section_name, $section)
                    || strcasecmp(trim((string) $assignment->section_name), 'All') === 0;
            });
        }

        if ($matchingClassRows->isEmpty()) {
            return false;
        }

        if ($subject === null) {
            return true;
        }

        $subjectAliases = $this->subjectAliases((string) $subject);
        if (!$subjectAliases) {
            return false;
        }

        return $matchingClassRows->contains(function ($assignment) use ($subjectAliases) {
            $assignedSubject = trim((string) $assignment->subject_id);
            if ($assignedSubject === '' || $assignedSubject === '0') {
                return strcasecmp(trim((string) $assignment->role), 'Class Teacher') === 0;
            }

            return $this->aliasesIntersect(
                $subjectAliases,
                $this->subjectAliases($assignedSubject)
            );
        });
    }

    public function clientScope(): array
    {
        if (!$this->isStaffUser()) {
            return [];
        }

        return $this->staffAssignments()
            ->map(function ($assignment) {
                $classId = trim((string) $assignment->class_id);
                $classNames = $this->classAliases($classId);
                $className = $classNames ? end($classNames) : $classId;
                $subjectId = trim((string) $assignment->subject_id);
                $subjects = $subjectId !== '' && $subjectId !== '0'
                    ? $this->subjectAliases($subjectId)
                    : [];

                return [
                    'class_id' => $classId,
                    'class_name' => $className,
                    'sections' => [trim((string) $assignment->section_name)],
                    'subjects' => $subjects,
                    'all_subjects' => ($subjectId === '' || $subjectId === '0')
                        && strcasecmp(trim((string) $assignment->role), 'Class Teacher') === 0,
                ];
            })
            ->values()
            ->all();
    }

    public function filterClassOptions($classes)
    {
        $classes = collect($classes);
        if (!$this->isStaffUser()) {
            return $classes;
        }

        return $classes->filter(function ($class) {
            $classValue = is_object($class)
                ? ($class->id ?? $class->class_name ?? null)
                : $class;
            return $classValue !== null && $this->canAccess($classValue);
        })->values();
    }

    public function allowedSections($class): ?array
    {
        if (!$this->isStaffUser()) {
            return null;
        }

        $classAliases = $this->classAliases((string) $class);
        $assignments = $this->staffAssignments()
            ->map(fn ($assignment) => (object) [
                'class_id' => $assignment->class_id,
                'section_name' => $assignment->section_name,
            ])
            ->filter(fn ($assignment) => $this->aliasesIntersect(
                $classAliases,
                $this->classAliases((string) $assignment->class_id)
            ));

        if ($assignments->contains(fn ($assignment) => strcasecmp(trim((string) $assignment->section_name), 'All') === 0)) {
            return null;
        }

        return $assignments->pluck('section_name')
            ->filter()
            ->map(fn ($section) => trim((string) $section))
            ->unique()
            ->values()
            ->all();
    }

    public function allowedSubjectIds($class, $section = null): ?array
    {
        if (!$this->isStaffUser()) {
            return null;
        }

        $classAliases = $this->classAliases((string) $class);
        $assignments = $this->staffAssignments()
            ->filter(function ($assignment) use ($classAliases, $section) {
                $classMatches = $this->aliasesIntersect(
                    $classAliases,
                    $this->classAliases((string) $assignment->class_id)
                );
                $sectionMatches = $section === null
                    || $this->sameValue($assignment->section_name, $section)
                    || strcasecmp(trim((string) $assignment->section_name), 'All') === 0;

                return $classMatches && $sectionMatches;
            });

        if ($assignments->contains(function ($assignment) {
            $subjectId = trim((string) $assignment->subject_id);
            return ($subjectId === '' || $subjectId === '0')
                && strcasecmp(trim((string) $assignment->role), 'Class Teacher') === 0;
        })) {
            return null;
        }

        return $assignments->pluck('subject_id')
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function filterSubjectData($rows, $class, $section, string $idKey = 'id')
    {
        $rows = collect($rows);
        $allowedIds = $this->allowedSubjectIds($class, $section);

        if ($allowedIds === null) {
            return $rows;
        }

        return $rows->filter(function ($row) use ($allowedIds, $idKey) {
            $subjectId = is_array($row) ? ($row[$idKey] ?? null) : ($row->{$idKey} ?? null);
            return $subjectId !== null && in_array((int) $subjectId, $allowedIds, true);
        })->values();
    }

    private function filledValues(Request $request, array $fields): array
    {
        $values = [];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $values[] = $request->input($field);
            }
        }

        return $values;
    }

    private function classAliases(string $value): array
    {
        if (array_key_exists($value, $this->classAliasesCache)) {
            return $this->classAliasesCache[$value];
        }

        $aliases = [$this->normalize($value)];

        $classesTable = DB::connection('dynamic')->table('classes');
        $classRows = $classesTable
            ->where('id', $value)
            ->orWhere('class_name', $value)
            ->get(['id', 'class_name']);

        foreach ($classRows as $row) {
            $aliases[] = $this->normalize((string) $row->id);
            $aliases[] = $this->normalize((string) $row->class_name);
        }

        $classNamesTable = DB::connection('dynamic')->table('class_name');
        $classNameRows = $classNamesTable
            ->where('id', $value)
            ->orWhere('class_name', $value)
            ->get(['id', 'class_name']);

        foreach ($classNameRows as $row) {
            $aliases[] = $this->normalize((string) $row->id);
            $aliases[] = $this->normalize((string) $row->class_name);
        }

        return $this->classAliasesCache[$value] = array_values(array_unique(array_filter($aliases)));
    }

    private function subjectAliases(string $value): array
    {
        if (array_key_exists($value, $this->subjectAliasesCache)) {
            return $this->subjectAliasesCache[$value];
        }

        $aliases = [$this->normalize($value)];
        $subjectRows = DB::connection('dynamic')->table('subjectmaster')
            ->where('id', $value)
            ->orWhere('subject_name', $value)
            ->get(['id', 'subject_name']);

        foreach ($subjectRows as $row) {
            $aliases[] = $this->normalize((string) $row->id);
            $aliases[] = $this->normalize((string) $row->subject_name);
        }

        return $this->subjectAliasesCache[$value] = array_values(array_unique(array_filter($aliases)));
    }

    private function staffAssignments(): Collection
    {
        if ($this->assignments !== null) {
            return $this->assignments;
        }

        $employeeId = (int) (Auth::guard('staff')->user()->employee_id ?? 0);
        $this->assignments = $employeeId > 0
            ? TeacherSubject::query()
                ->where('is_delete', 0)
                ->where('teacher_id', $employeeId)
                ->get(['class_id', 'section_name', 'subject_id', 'role'])
            : collect();

        return $this->assignments;
    }

    private function aliasesIntersect(array $left, array $right): bool
    {
        return (bool) array_intersect($left, $right);
    }

    private function sameValue($left, $right): bool
    {
        return $this->normalize((string) $left) === $this->normalize((string) $right);
    }

    private function normalize(string $value): string
    {
        return strtolower(trim($value));
    }
}
