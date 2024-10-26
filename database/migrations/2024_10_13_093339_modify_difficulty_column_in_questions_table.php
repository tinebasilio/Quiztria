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
        Schema::table('questions', function (Blueprint $table) {
            // Drop the existing difficulty column
            $table->dropColumn('difficulty');

            // Add the foreign key column
            $table->foreignId('difficulty_id')->constrained('difficulties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['difficulty_id']);

            // Drop the difficulty_id column
            $table->dropColumn('difficulty_id');

            // Restore the original difficulty column
            $table->string('difficulty')->default('easy');
        });
    }
};
