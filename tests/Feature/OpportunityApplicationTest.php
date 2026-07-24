<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_an_application_for_an_opportunity(): void
    {
        $student = User::create([
            'name' => 'Student One',
            'full_name' => 'Student One',
            'email' => 'student@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $opportunity = Opportunity::create([
            'title' => 'Digital Skills Bootcamp',
            'category' => 'bootcamp',
            'description' => 'A short bootcamp for youth.',
            'provider_name' => 'BridgeEdu',
            'eligibility_criteria' => 'Open to all students.',
            'application_deadline' => now()->addMonth()->toDateString(),
            'is_verified' => true,
            'created_by' => $student->id,
        ]);

        $token = $student->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/opportunity-applications', [
                'opportunity_id' => $opportunity->id,
                'cover_letter' => 'I am excited to join this opportunity.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.student.id', $student->id)
            ->assertJsonPath('data.opportunity.id', $opportunity->id)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_mentor_can_submit_an_application_for_an_opportunity(): void
    {
        $mentor = User::create([
            'name' => 'Mentor One',
            'full_name' => 'Mentor One',
            'email' => 'mentor@example.com',
            'password' => bcrypt('password123'),
            'role' => 'mentor',
        ]);

        $opportunity = Opportunity::create([
            'title' => 'Mentor Career Support Program',
            'category' => 'program',
            'description' => 'Support for mentors building their profile.',
            'provider_name' => 'BridgeEdu',
            'eligibility_criteria' => 'Open to mentors.',
            'application_deadline' => now()->addMonth()->toDateString(),
            'is_verified' => true,
            'created_by' => $mentor->id,
        ]);

        $token = $mentor->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/opportunity-applications', [
                'opportunity_id' => $opportunity->id,
                'cover_letter' => 'I would like to join this opportunity.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.student.id', $mentor->id)
            ->assertJsonPath('data.opportunity.id', $opportunity->id)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_admin_can_update_application_status(): void
    {
        $student = User::create([
            'name' => 'Student Two',
            'full_name' => 'Student Two',
            'email' => 'student2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $opportunity = Opportunity::create([
            'title' => 'Scholarship for Youth',
            'category' => 'scholarship',
            'description' => 'A scholarship opportunity.',
            'provider_name' => 'BridgeEdu',
            'eligibility_criteria' => 'Open to applicants.',
            'application_deadline' => now()->addMonth()->toDateString(),
            'is_verified' => true,
            'created_by' => $admin->id,
        ]);

        $application = $student->applications()->create([
            'opportunity_id' => $opportunity->id,
            'status' => 'pending',
            'cover_letter' => 'I would like to apply.',
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/opportunity-applications/'.$application->id, [
                'status' => 'accepted',
                'notes' => 'Approved by admin.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.notes', 'Approved by admin.');
    }
}
