<?php

namespace Tests\Feature;

use App\Models\MentorshipRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorshipWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_a_mentorship_request(): void
    {
        $student = User::create([
            'name' => 'Student Mentor',
            'full_name' => 'Student Mentor',
            'email' => 'studentmentor@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $token = $student->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/mentorship-requests', [
                'topic_of_interest' => 'Digital skills transition',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.topic_of_interest', 'Digital skills transition')
            ->assertJsonPath('data.student.id', $student->id);
    }

    public function test_mentor_can_claim_a_pending_request(): void
    {
        $student = User::create([
            'name' => 'Student One',
            'full_name' => 'Student One',
            'email' => 'studentone@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $mentor = User::create([
            'name' => 'Mentor One',
            'full_name' => 'Mentor One',
            'email' => 'mentorone@example.com',
            'password' => bcrypt('password123'),
            'role' => 'mentor',
            'is_verified_mentor' => true,
        ]);

        $request = MentorshipRequest::create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'status' => 'pending',
            'topic_of_interest' => 'Career guidance',
        ]);

        $token = $mentor->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/mentorship-requests/'.$request->id, []);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'matched')
            ->assertJsonPath('data.mentor.id', $mentor->id);
    }

    public function test_mentor_can_list_and_filter_requests_by_status(): void
    {
        $student = User::create([
            'name' => 'Student Two',
            'full_name' => 'Student Two',
            'email' => 'studenttwo@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $mentor = User::create([
            'name' => 'Mentor Two',
            'full_name' => 'Mentor Two',
            'email' => 'mentortwo@example.com',
            'password' => bcrypt('password123'),
            'role' => 'mentor',
            'is_verified_mentor' => true,
        ]);

        MentorshipRequest::create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'status' => 'matched',
            'topic_of_interest' => 'Career guidance',
        ]);

        MentorshipRequest::create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'status' => 'pending',
            'topic_of_interest' => 'Study planning',
        ]);

        $token = $mentor->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/mentorship-requests?status=matched');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_accept_and_assign_a_mentorship_request(): void
    {
        $student = User::create([
            'name' => 'Student Admin',
            'full_name' => 'Student Admin',
            'email' => 'studentadmin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $mentor = User::create([
            'name' => 'Mentor Admin',
            'full_name' => 'Mentor Admin',
            'email' => 'mentoradmin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'mentor',
            'is_verified_mentor' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'full_name' => 'Admin User',
            'email' => 'adminuser@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $request = MentorshipRequest::create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'status' => 'pending',
            'topic_of_interest' => 'Mentor assignment',
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/mentorship-requests/'.$request->id, [
                'mentor_id' => $mentor->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'matched')
            ->assertJsonPath('data.mentor.id', $mentor->id)
            ->assertJsonPath('data.assigned_by_admin_id', $admin->id)
            ->assertJsonPath('data.assigned_by_admin.id', $admin->id);
    }
}
