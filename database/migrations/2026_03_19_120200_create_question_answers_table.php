<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('author_name', 80);
            $table->string('participant_key', 100)->index();
            $table->text('answer_text')->nullable();
            $table->string('selected_option', 120)->nullable();
            $table->timestamps();

            $table->unique(['question_id', 'participant_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_answers');
    }
};
