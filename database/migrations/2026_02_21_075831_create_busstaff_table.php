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
        Schema::create('busstaff', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('role', 50)->nullable();
            $table->string('ename', 100)->nullable();
            $table->string('mobile_number', 100)->nullable();
            $table->string('aadhar_number', 50)->nullable();
            $table->string('sssmid', 100)->nullable();
            $table->text('current_address')->nullable();
            $table->text('parmanent_address')->nullable();
            $table->string('license_no', 100)->nullable();
            $table->string('license_expire', 20)->nullable();
            $table->string('license_lssue', 100)->nullable();
            $table->string('voter_id_no', 50)->nullable();
            $table->string('joining_date', 20)->nullable();
            $table->string('leaving_date', 20)->nullable();
            $table->string('leaving_date1', 10)->nullable();
            $table->string('call_no', 100)->nullable();
            $table->string('offical_mobile_no', 100)->nullable();
            $table->string('emergency_contact_no', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->string('healthstatus', 10)->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('busstaff');
    }
};
