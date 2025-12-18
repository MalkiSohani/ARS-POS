<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('draft_sales', function (Blueprint $table) {
        $table->string('vehicle_number')->nullable()->after('notes');
        $table->string('meter_reading')->nullable()->after('vehicle_number');
        $table->string('job_no')->nullable()->after('meter_reading');
        $table->time('time_in')->nullable()->after('job_no');
        $table->time('time_out')->nullable()->after('time_in');
        $table->date('next_service_due')->nullable()->after('time_out');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draft_sales', function (Blueprint $table) {
          $table->dropColumn(['vehicle_number', 'meter_reading', 'job_no', 'time_in', 'time_out', 'next_service_due']);
        });
    }
};
