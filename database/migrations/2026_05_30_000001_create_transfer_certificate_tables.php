<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('dynamic')->create('tc_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_no', 60)->unique();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('scholar_no', 50)->index();
            $table->string('session_name', 50)->index();
            $table->string('class_name', 50)->nullable()->index();
            $table->string('section_name', 100)->nullable()->index();
            $table->string('student_name', 200)->index();
            $table->string('status', 30)->default('issued')->index();
            $table->date('issue_date')->index();
            $table->date('leaving_date')->nullable();
            $table->string('reason_for_leaving', 500)->nullable();
            $table->string('conduct', 200)->nullable();
            $table->text('remarks')->nullable();
            $table->json('snapshot')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable()->index();
            $table->unsignedBigInteger('last_issue_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['student_id', 'session_name'], 'tc_student_session_unique');
        });

        Schema::connection('dynamic')->create('tc_certificate_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_id')->index();
            $table->unsignedInteger('issue_no')->default(1);
            $table->boolean('is_duplicate')->default(false)->index();
            $table->string('duplicate_label', 100)->nullable();
            $table->string('reason', 500)->nullable();
            $table->string('pdf_path', 500)->nullable();
            $table->unsignedBigInteger('issued_by')->nullable()->index();
            $table->timestamp('issued_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('certificate_id', 'tc_issues_certificate_fk')
                ->references('id')
                ->on('tc_certificates')
                ->cascadeOnDelete();
            $table->unique(['certificate_id', 'issue_no'], 'tc_issue_no_unique');
        });

        Schema::connection('dynamic')->create('tc_student_session_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('scholar_no', 50)->index();
            $table->unsignedBigInteger('certificate_id')->nullable()->index();
            $table->string('tc_session_name', 50)->index();
            $table->string('inactive_from_session', 50)->nullable()->index();
            $table->string('status', 30)->default('transferred')->index();
            $table->string('reason', 500)->nullable();
            $table->unsignedBigInteger('marked_by')->nullable()->index();
            $table->timestamp('marked_at')->nullable();
            $table->timestamps();

            $table->foreign('certificate_id', 'tc_status_certificate_fk')
                ->references('id')
                ->on('tc_certificates')
                ->nullOnDelete();
            $table->unique(['scholar_no', 'tc_session_name'], 'tc_status_student_session_unique');
        });

        Schema::connection('dynamic')->create('tc_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('view_path', 255);
            $table->string('paper_size', 50)->default('A4');
            $table->string('orientation', 20)->default('portrait');
            $table->boolean('is_default')->default(false)->index();
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });

        Schema::connection('dynamic')->create('tc_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_id')->nullable()->index();
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('scholar_no', 50)->nullable()->index();
            $table->string('action', 80)->index();
            $table->string('description', 500)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->foreign('certificate_id', 'tc_audit_certificate_fk')
                ->references('id')
                ->on('tc_certificates')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::connection('dynamic')->dropIfExists('tc_audit_logs');
        Schema::connection('dynamic')->dropIfExists('tc_templates');
        Schema::connection('dynamic')->dropIfExists('tc_student_session_statuses');
        Schema::connection('dynamic')->dropIfExists('tc_certificate_issues');
        Schema::connection('dynamic')->dropIfExists('tc_certificates');
    }
};
