<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

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
