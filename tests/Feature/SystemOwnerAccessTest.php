<?php

namespace Tests\Feature;

use App\Models\MentorshipRequest;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemOwnerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_and_delete_opportunities(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'full_name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $token = $superAdmin->createToken('test-token')->plainTextToken;

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/opportunities', [
                'title' => 'Super Admin Opportunity',
                'category' => 'grant',
                'description' => 'Managed by system owner',
                'provider_name' => 'BridgeEdu',
                'eligibility_criteria' => 'Open to all',
                'application_deadline' => now()->addMonth()->toDateString(),
                'is_verified' => true,
            ]);

        $createResponse->assertStatus(201);

        $opportunityId = $createResponse->json('data.id');

        $deleteResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/opportunities/'.$opportunityId);

        $deleteResponse->assertStatus(200);
    }

    public function test_super_admin_can_delete_mentorship_requests(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'full_name' => 'Super Admin',
            'email' => 'superadmin2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $student = User::create([
            'name' => 'Student X',
            'full_name' => 'Student X',
            'email' => 'studentx@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $request = MentorshipRequest::create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'status' => 'pending',
            'topic_of_interest' => 'Career planning',
        ]);

        $token = $superAdmin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/mentorship-requests/'.$request->id);

        $response->assertStatus(200);
    }
}
