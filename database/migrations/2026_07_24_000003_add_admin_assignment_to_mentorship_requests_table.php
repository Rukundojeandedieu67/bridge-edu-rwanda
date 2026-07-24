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
        Schema::table('mentorship_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_by_admin_id')->nullable()->after('mentor_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by_admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorship_requests', function (Blueprint $table) {
            $table->dropColumn(['assigned_by_admin_id', 'assigned_at']);
        });
    }
};
