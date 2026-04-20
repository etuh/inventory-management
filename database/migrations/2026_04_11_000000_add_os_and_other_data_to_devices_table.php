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
        Schema::table('devices', function (Blueprint $table) {
            // Add columns to store device-level configuration for optional fields
            $table->boolean('include_operating_system')->default(false)->after('specification_fields');
            $table->json('other_data_fields')->nullable()->after('include_operating_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('include_operating_system');
            $table->dropColumn('other_data_fields');
        });
    }
};
