<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourseFeesStructureSpellingTest extends TestCase
{
    /**
     * Test the correct_caution_money_display helper function directly.
     */
    public function test_correct_caution_money_display_helper()
    {
        $this->assertEquals('CAUTION MONEY', correct_caution_money_display('CUATION MONEY'));
        $this->assertEquals('Caution Money', correct_caution_money_display('Cuation Money'));
        $this->assertEquals('CAUTION', correct_caution_money_display('CUATION'));
        $this->assertEquals('Caution', correct_caution_money_display('Cuation'));
        $this->assertEquals('TUITION FEES', correct_caution_money_display('TUITION FEES'));
        $this->assertEquals('', correct_caution_money_display(null));
        $this->assertEquals('', correct_caution_money_display(''));
    }

    /**
     * Test that the edit course fees structure master view renders CAUTION MONEY for display.
     */
    public function test_edit_course_fees_structure_master_view_displays_caution_money()
    {
        $errors = new \Illuminate\Support\ViewErrorBag();
        $headMaster = DB::connection('dynamic')->table('course_fees_head_master')->where('is_delete', 0)->get();
        $cfsRecord = DB::connection('dynamic')->table('course_fees_structure_master')->where('id', 1)->first();

        $html = view('backend.cfs_master.edit_course_fees_structure_master_list', [
            'errors' => $errors,
            'Vehicallist' => [$cfsRecord],
            'courseFeesStructure' => [],
            'holds_structure_row' => [],
            'course_fees_head_master' => $headMaster,
            'class_names' => [],
            'fees_types' => [],
            'terms' => []
        ])->render();

        // Must display CAUTION MONEY to user
        $this->assertStringContainsString("option.textContent = 'CAUTION MONEY'", $html);
        // Underlying value must remain intact
        $this->assertStringContainsString("option.value = 'CUATION MONEY'", $html);
    }

    /**
     * Test that CFS index view displays CAUTION MONEY in the list and select dropdown.
     */
    public function test_cfs_index_view_displays_caution_money()
    {
        $errors = new \Illuminate\Support\ViewErrorBag();
        $headMaster = DB::connection('dynamic')->table('course_fees_head_master')->where('is_delete', 0)->get();

        $html = view('backend.cfs_master.index', [
            'errors' => $errors,
            'Vehicallist' => [],
            'holds_structure_row' => [],
            'course_fees_head_master' => $headMaster,
            'class_names' => [],
            'fees_types' => [],
            'terms' => []
        ])->render();

        $this->assertStringContainsString('<td>CAUTION MONEY</td>', $html);
        $this->assertStringContainsString('>CAUTION MONEY</option>', $html);
        $this->assertStringContainsString('value="CUATION MONEY"', $html);
    }
}
