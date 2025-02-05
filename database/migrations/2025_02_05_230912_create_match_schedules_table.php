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
        Schema::create('match_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->integer('round_number');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('participant1_id');
            $table->unsignedBigInteger('participant2_id');
            $table->date('match_date');
            $table->time('match_time');
            $table->unsignedBigInteger('venue_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_schedules');
    }
};
