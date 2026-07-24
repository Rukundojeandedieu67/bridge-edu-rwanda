<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('users_old')) {
            Schema::drop('users_old');
        }

        DB::statement('ALTER TABLE users RENAME TO users_old');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('full_name')->nullable();
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('student');
            $table->string('phone_number')->nullable();
            $table->string('district')->nullable();
            $table->string('sector')->nullable();
            $table->string('education_level')->nullable();
            $table->boolean('is_verified_mentor')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement(
            'INSERT INTO users (id, name, full_name, email, email_verified_at, password, role, phone_number, district, sector, education_level, is_verified_mentor, remember_token, created_at, updated_at) ' .
            'SELECT id, name, full_name, email, email_verified_at, password, role, phone_number, district, sector, education_level, is_verified_mentor, remember_token, created_at, updated_at FROM users_old'
        );

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $indexExists = DB::selectOne("SHOW INDEX FROM users WHERE Key_name = 'users_email_unique'");

            if (empty($indexExists)) {
                DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
            }
        } else {
            try {
                DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
            } catch (\Throwable $e) {
                // Ignore duplicate index errors on SQLite and other databases.
            }
        }

        $this->rebuildDependentTables();

        Schema::dropIfExists('users_old');

        Schema::enableForeignKeyConstraints();
    }

    private function rebuildDependentTables(): void
    {
        $this->recreateTableWithUsersReference('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
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

        $this->recreateTableWithUsersReference('mentorship_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('mentor_id')->nullable();
            $table->enum('status', ['pending', 'matched', 'completed'])->default('pending');
            $table->string('topic_of_interest');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mentor_id')->references('id')->on('users')->onDelete('set null');
        });

        $this->recreateTableWithUsersReference('opportunity_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'reviewed', 'accepted', 'rejected'])->default('pending');
            $table->text('cover_letter')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['opportunity_id', 'student_id']);
        });
    }

    private function recreateTableWithUsersReference(string $tableName, callable $callback): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $rows = DB::table($tableName)->get()->map(fn ($row) => (array) $row)->all();

        Schema::dropIfExists($tableName);
        Schema::create($tableName, $callback);

        if (! empty($rows)) {
            DB::table($tableName)->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::statement('ALTER TABLE users RENAME TO users_new');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement('INSERT INTO users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) SELECT id, name, email, email_verified_at, password, remember_token, created_at, updated_at FROM users_new');

        Schema::dropIfExists('users_new');

        Schema::enableForeignKeyConstraints();
    }
};
