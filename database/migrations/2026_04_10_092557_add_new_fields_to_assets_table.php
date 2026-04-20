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
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\User::class, 'assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('department')->nullable();
            $table->string('brand_model')->nullable();
            $table->string('model_no')->nullable();
            $table->json('operating_system')->nullable();
            $table->json('specifications')->nullable();
            $table->json('other_data')->nullable();
            $table->date('purchase_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            //
        });
    }
};