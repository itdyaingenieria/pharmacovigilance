<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->primary();
            $table->string('organization_name', 100);
            $table->string('primary_contact_name', 100)->nullable();
            $table->string('primary_contact_email', 100)->nullable();
            $table->string('client_type', 50)->nullable();
            $table->date('relationship_start_date')->nullable();
            $table->text('contracted_services')->nullable();
            $table->text('billing_rates')->nullable();
            $table->text('locations')->nullable();
            $table->text('emergency_contacts')->nullable();
            $table->text('uploaded_documents')->nullable();
            $table->string('submitted_by', 100)->nullable();
            $table->timestamp('last_edited')->nullable();

            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
