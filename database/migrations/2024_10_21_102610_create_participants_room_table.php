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
        Schema::create('participants_room', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id'); // Correct data type for foreign key
            $table->unsignedBigInteger('participant_id'); // Correct data type for foreign key
            $table->boolean('is_at_room')->default(0);
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
        Schema::dropIfExists('participants_room');
    }
};
