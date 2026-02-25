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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->enum('question_type', [
                'multiple_choice_single',
                'checkbox_multiple',
                'text_only',
                'mcq_textarea',
                'checkbox_textarea',
            ]);
            $table->enum('text_response', ['no_text', 'optional', 'required'])->default('no_text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
