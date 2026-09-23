<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every row of shop data belongs to exactly one account. Without this the
     * app is a single global shop shared by all registered users. These tables
     * get a user_id and per-account unique constraints; existing rows are
     * backfilled onto the first (owner) user so the seeded demo data survives.
     */
    private array $tables = [
        'services',
        'customers',
        'bookings',
        'expenses',
        'business_settings',
        'blocked_slots',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
            });
        }

        // The global uniques become per-account uniques. (No DB-level foreign
        // keys here: SQLite rebuilds the table to add them, and users are
        // never deleted, so the index alone is enough.)
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('bookings_unique_slot');
            $table->unique(
                ['user_id', 'customer_id', 'service_id', 'appointment_date', 'appointment_time'],
                'bookings_user_slot_unique'
            );
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_messenger_id_unique');
            $table->unique(['user_id', 'messenger_id'], 'customers_user_messenger_unique');
        });

        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropUnique('business_settings_key_unique');
            $table->unique(['user_id', 'key'], 'business_settings_user_key_unique');
        });

        Schema::table('blocked_slots', function (Blueprint $table) {
            $table->dropUnique('blocked_slots_date_unique');
            $table->unique(['user_id', 'date'], 'blocked_slots_user_date_unique');
        });

        // Claim all pre-existing rows for the owner account so today's data
        // keeps working after scoping kicks in.
        $ownerId = DB::table('users')->orderBy('id')->first()?->id;
        if ($ownerId) {
            foreach ($this->tables as $table) {
                DB::table($table)->whereNull('user_id')->update(['user_id' => $ownerId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('bookings_user_slot_unique');
            $table->unique(
                ['customer_id', 'service_id', 'appointment_date', 'appointment_time'],
                'bookings_unique_slot'
            );
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_user_messenger_unique');
            $table->unique('messenger_id', 'customers_messenger_id_unique');
        });

        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropUnique('business_settings_user_key_unique');
            $table->unique('key', 'business_settings_key_unique');
        });

        Schema::table('blocked_slots', function (Blueprint $table) {
            $table->dropUnique('blocked_slots_user_date_unique');
            $table->unique('date', 'blocked_slots_date_unique');
        });

        foreach (array_reverse($this->tables) as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
