<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hrms_employee_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id')->index('hrms_employee_documents_employee_id_foreign');
            $table->string('document_type')->nullable();
            $table->boolean('is_uploaded')->nullable()->default(false);
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'document_type'], 'unique_employee_document');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_employee_documents');
    }
};
