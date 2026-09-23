<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blocked_slots', function (Blueprint $table) {
            // The barber's leave window. Null on both means the whole day is blocked
            // (legacy blocks and days without configured operating hours).
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('blocked_slots', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
