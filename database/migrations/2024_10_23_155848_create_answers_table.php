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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->boolean('correct')->default(0);
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('test_id')->nullable();
            $table->unsignedBigInteger('question_id');
            $table->string('sub_answer');
            $table->timestamps();
            $table->softDeletes();

            // Define foreign keys with specific onDelete actions
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('set null');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};

