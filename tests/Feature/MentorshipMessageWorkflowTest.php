<?php

namespace Tests\Feature;

use App\Models\MentorshipRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorshipMessageWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_and_mentor_can_exchange_messages_after_match(): void
    {
        $student = User::create([
            'name' => 'Student Message',
            'full_name' => 'Student Message',
            'email' => 'studentmessage@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $mentor = User::create([
            'name' => 'Mentor Message',
            'full_name' => 'Mentor Message',
            'email' => 'mentormessage@example.com',
            'password' => bcrypt('password123'),
            'role' => 'mentor',
            'is_verified_mentor' => true,
        ]);

        $request = MentorshipRequest::create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'status' => 'matched',
            'topic_of_interest' => 'Career planning',
        ]);

        $studentToken = $student->createToken('test-token')->plainTextToken;

        $studentResponse = $this->withHeader('Authorization', 'Bearer '.$studentToken)
            ->postJson('/api/v1/mentorship-requests/'.$request->id.'/messages', [
                'body' => 'Hello mentor, I am ready to discuss my career path.',
            ]);

        $studentResponse->assertStatus(201)
            ->assertJsonPath('data.sender.id', $student->id)
            ->assertJsonPath('data.body', 'Hello mentor, I am ready to discuss my career path.');

        $mentorToken = $mentor->createToken('test-token')->plainTextToken;

        $mentorResponse = $this->actingAs($mentor, 'sanctum')
            ->postJson('/api/v1/mentorship-requests/'.$request->id.'/messages', [
                'body' => 'Great, let us book a first meeting.',
            ]);

        $mentorResponse->assertStatus(201)
            ->assertJsonPath('data.sender.id', $mentor->id)
            ->assertJsonPath('data.body', 'Great, let us book a first meeting.');

        $listResponse = $this->actingAs($mentor, 'sanctum')
            ->getJson('/api/v1/mentorship-requests/'.$request->id.'/messages');

        $listResponse->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
