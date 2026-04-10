<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->primary();
            $table->string('viventium_id', 50)->unique();
            $table->string('gender', 10)->nullable();
            $table->date('birthday')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('personal_email', 100)->nullable();
            $table->string('company_title', 100)->nullable();
            $table->string('manager_name', 100)->nullable();
            $table->string('security_license_id', 50)->nullable();
            $table->string('security_license_type', 20)->nullable();
            $table->date('license_expiration_date')->nullable();
            $table->boolean('armed_status')->default(false);
            $table->string('employment_status', 10)->nullable();
            $table->date('hire_date')->nullable();
            $table->boolean('vehicle_access')->default(false);
            $table->string('home_address', 255)->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('overtime_rate', 10, 2)->nullable();
            $table->decimal('holiday_rate', 10, 2)->nullable();
            $table->string('training_level', 50)->nullable();
            $table->text('completed_trainings')->nullable();
            $table->text('document_paths')->nullable();

            $table->timestamps();
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
