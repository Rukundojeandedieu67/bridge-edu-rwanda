<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = User::create([
            'name' => 'Profile User',
            'full_name' => 'Profile User',
            'email' => 'profile@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'district' => 'Huye',
            'sector' => 'Mbuye',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.district', 'Huye')
            ->assertJsonPath('data.role', 'student');
    }

    public function test_admin_can_list_users_with_filters(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'full_name' => 'Admin User',
            'email' => 'adminprofile@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Mentor User',
            'full_name' => 'Mentor User',
            'email' => 'mentorprofile@example.com',
            'password' => bcrypt('password123'),
            'role' => 'mentor',
            'district' => 'Nyaruguru',
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/users?role=mentor&district=Nyaruguru');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_update_email_through_profile_update(): void
    {
        $user = User::create([
            'name' => 'Profile Edit User',
            'full_name' => 'Profile Edit User',
            'email' => 'profileedit@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/profile', [
                'full_name' => 'Profile Edited',
                'email' => 'changed@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Email cannot be updated.');

        $user->refresh();

        $this->assertSame('profileedit@example.com', $user->email);
    }
}
