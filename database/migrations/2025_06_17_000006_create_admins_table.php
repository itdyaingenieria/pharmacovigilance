<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->primary();
            $table->string('employee_id', 50)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('company_title', 100)->nullable();
            $table->date('start_date')->nullable();
            $table->string('employment_status', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('global_ops_email', 100)->nullable();
            $table->string('phone', 20)->nullable();

            $table->timestamps();
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
