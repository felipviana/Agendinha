<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecurrenceDetailsToEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('recurrence_days')->nullable()->after('recurrence_group');
            $table->date('recurrence_start_date')->nullable()->after('recurrence_days');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_start_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'recurrence_days',
                'recurrence_start_date',
                'recurrence_end_date',
            ]);
        });
    }
}
