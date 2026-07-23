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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', ['scholarship', 'bootcamp', 'micro_task', 'grant']);
            $table->text('description');
            $table->string('provider_name');
            $table->text('eligibility_criteria')->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('external_link')->nullable();
            $table->json('region_tags')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
