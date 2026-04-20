<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Inventory::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Device::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Category::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('serial_number')->nullable()->unique();
            $table->enum('condition', ['new', 'ok', 'outdated'])->default('new');
            $table->enum('status', ['available', 'assigned', 'maintenance', 'retired'])->default('available');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
