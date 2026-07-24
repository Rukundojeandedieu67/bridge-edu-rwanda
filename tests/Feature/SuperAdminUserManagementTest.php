<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_creates_configured_super_admin_account_from_environment(): void
    {
        putenv('SUPER_ADMIN_EMAIL=bootstrap@example.com');
        putenv('SUPER_ADMIN_PASSWORD=bootstrap-pass');
        putenv('SUPER_ADMIN_NAME=Bootstrap Admin');
        putenv('SUPER_ADMIN_FULL_NAME=Bootstrap Admin');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'bootstrap@example.com',
            'password' => 'bootstrap-pass',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'email' => 'bootstrap@example.com',
            'role' => 'super_admin',
        ]);
    }

    public function test_super_admin_can_view_all_users(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'full_name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $student = User::create([
            'name' => 'Student User',
            'full_name' => 'Student User',
            'email' => 'student@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $token = $superAdmin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/users');

        $response->assertOk();
        $response->assertJsonFragment(['email' => $student->email]);
    }
}
