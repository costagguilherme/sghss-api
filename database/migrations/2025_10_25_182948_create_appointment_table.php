<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('hospital_id')->nullable()->constrained('hospitals')->onDelete('cascade');
            $table->enum('type', ['in_person', 'online']);
            $table->string('meeting_id')->nullable();
            $table->text('start_url')->nullable();
            $table->string('join_url')->nullable();
            $table->dateTime('scheduled_at');
            $table->text('medical_notes')->nullable();
            $table->text('prescription')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('certificate')->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
