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
        Schema::create('mentorship_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentorship_request_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('body');
            $table->timestamps();

            $table->foreign('mentorship_request_id')->references('id')->on('mentorship_requests')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentorship_messages');
    }
};
