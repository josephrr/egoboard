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
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('admin_token')->unique()->after('slug');
            $table->boolean('is_open')->default(true)->after('description');
            $table->boolean('allow_anonymous')->default(true)->after('is_open');
            $table->boolean('allow_reactions')->default(true)->after('allow_anonymous');
            $table->boolean('allow_one_note_per_participant')->default(false)->after('allow_reactions');
            $table->string('theme')->default('sunrise')->after('allow_one_note_per_participant');
            $table->timestamp('closes_at')->nullable()->after('theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'admin_token',
                'is_open',
                'allow_anonymous',
                'allow_reactions',
                'allow_one_note_per_participant',
                'theme',
                'closes_at',
            ]);
        });
    }
};
