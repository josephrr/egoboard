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
        Schema::table('notes', function (Blueprint $table) {
            $table->string('participant_key', 100)->nullable()->after('author_name')->index();
            $table->string('category', 30)->default('idea')->after('message');
            $table->boolean('is_anonymous')->default(false)->after('category');
            $table->boolean('is_visible')->default(true)->after('is_anonymous');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn([
                'participant_key',
                'category',
                'is_anonymous',
                'is_visible',
            ]);
        });
    }
};
