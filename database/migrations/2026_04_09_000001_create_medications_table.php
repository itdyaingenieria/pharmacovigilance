<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('lot_number', 50)->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['lot_number', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
