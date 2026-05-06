<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE academic_student_roll_no AS ar
            INNER JOIN student_registration AS sr ON sr.id = ar.student_id
            INNER JOIN classes AS c ON c.id = sr.class_id
            SET
                ar.class_id = c.class_name,
                ar.section_id = c.section_name,
                ar.session_id = sr.session_name
            WHERE
                c.class_name IS NOT NULL
                AND c.section_name IS NOT NULL
                AND sr.session_name IS NOT NULL
        ");
    }

    public function down(): void
    {
        //
    }
};
