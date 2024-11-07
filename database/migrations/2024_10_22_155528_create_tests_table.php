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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->integer('score')->nullable();
            $table->string('ip_address')->nullable();
            $table->time('time_spent')->nullable();
            $table->unsignedBigInteger('room_id'); // Correct data type for foreign key
            $table->unsignedBigInteger('participant_id'); // Correct data type for foreign key
            $table->foreignId('quiz_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();

            // Add foreign key constraints
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
