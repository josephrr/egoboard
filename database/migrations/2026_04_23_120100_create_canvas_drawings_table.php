<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canvas_drawings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('author_name', 80);
            $table->string('participant_key', 100)->index();
            $table->longText('canvas_data');
            $table->longText('preview_png')->nullable();
            $table->timestamps();

            $table->unique(['room_id', 'participant_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canvas_drawings');
    }
};
