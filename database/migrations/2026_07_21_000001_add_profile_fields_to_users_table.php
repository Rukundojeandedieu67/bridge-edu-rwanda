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
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->after('name');
            $table->enum('role', ['student', 'mentor', 'admin'])->default('student')->after('full_name');
            $table->string('phone_number')->nullable()->after('role');
            $table->string('district')->nullable()->after('phone_number');
            $table->string('sector')->nullable()->after('district');
            $table->string('education_level')->nullable()->after('sector');
            $table->boolean('is_verified_mentor')->default(false)->after('education_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'role', 'phone_number', 'district', 'sector', 'education_level', 'is_verified_mentor']);
        });
    }
};
