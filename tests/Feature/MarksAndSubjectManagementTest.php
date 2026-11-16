<?php

namespace Tests\Feature;

use App\Models\Classname;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarksAndSubjectManagementTest extends TestCase
{
    public function test_subject_model_global_scope_excludes_deleted_subjects()
    {
        $allSubjects = Subject::all();
        foreach ($allSubjects as $s) {
            $this->assertEquals(0, (int) $s->is_delete, "Subject {$s->subject_name} (ID: {$s->id}) should not be soft-deleted.");
        }

        $allWithDeleted = Subject::withDeletedSubjects()->get();
        $hasDeleted = $allWithDeleted->contains(fn($s) => (int) $s->is_delete === 1);
        $this->assertTrue($hasDeleted, "withDeletedSubjects() should include soft-deleted subjects.");
    }

    public function test_classname_subjects_relation_excludes_deleted_subjects()
    {
        $class = Classname::where('class_name', '06')->orWhere('id', 9)->first();
        if ($class) {
            $subjects = $class->subjects;
            foreach ($subjects as $s) {
                $this->assertEquals(0, (int) $s->is_delete, "Class subjects should not contain soft-deleted subject ID: {$s->id}");
            }
        }
    }

    public function test_fetch_subjects_for_teacher_excludes_soft_deleted_subjects()
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get(route('fetchSubjectsForTeacher', ['class_id' => 9]));

        $response->assertStatus(200);
        $subjects = $response->json();
        $this->assertIsArray($subjects);

        foreach ($subjects as $subject) {
            $this->assertEquals(0, (int) ($subject['is_delete'] ?? 0), "fetchSubjectsForTeacher returned a soft-deleted subject: " . ($subject['subject_name'] ?? ''));
            $this->assertNotEquals(46, $subject['id'], "Subject ID 46 (te4st) is deleted and must not be returned.");
        }
    }

    public function test_marks_blade_has_blank_defaults_and_proper_absent_handling()
    {
        $bladeContent = file_get_contents(resource_path('views/backend/AcademicsModules/marks.blade.php'));

        // Verify mark_theory does not default to 0 in blade rendering
        $this->assertStringNotContainsString('value="{{ $marks->mark_theory ?? $marks->subject_marks ?? 0 }}"', $bladeContent);
        // Verify total_marks does not default to 0 in blade rendering
        $this->assertStringNotContainsString('value="{{ $marks->total_marks ?? $marks->subject_marks ?? 0 }}"', $bladeContent);
        // Verify AJAX student load does not hardcode value="0"
        $this->assertStringNotContainsString('mark_theory" value="0"', $bladeContent);
        $this->assertStringNotContainsString('total_marks" value="0"', $bladeContent);

        // Verify absent student disables only theory marks, not internal assessments
        $this->assertStringContainsString("row.find('.mark_theory').prop('disabled', isAbsent || isCurrentMarksEntryLocked)", $bladeContent);
        $this->assertStringContainsString("row.find('.internal_assessment_mark, .grade-dropdown').prop('disabled', isCurrentMarksEntryLocked)", $bladeContent);

        // Verify auto-select on focus and keydown
        $this->assertStringContainsString("targetField.select()", $bladeContent);
        $this->assertStringContainsString("$(document).on('focus', '.mark_theory, .mark_practical, .internal_assessment_mark'", $bladeContent);
    }

    public function test_teachersubject_page_renders_successfully_without_null_subject_error()
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get(route('teachersubject'));

        $response->assertStatus(200);
        $response->assertSee('Teacher Class Subject');
        $this->assertStringNotContainsString('Attempt to read property "subject_name" on null', $response->getContent());
    }
}
