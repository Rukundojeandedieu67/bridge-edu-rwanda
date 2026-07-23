<?php

namespace Database\Seeders;

use App\Models\MentorshipRequest;
use App\Models\Opportunity;
use App\Models\Pathway;
use App\Models\PathwayStep;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultPassword = 'password123';

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'full_name' => 'Admin User',
                'password' => Hash::make($defaultPassword),
                'role' => 'admin',
                'phone_number' => '+250780000001',
                'district' => 'Kigali',
                'sector' => 'Nyarugenge',
                'education_level' => 'University',
                'is_verified_mentor' => false,
            ]
        );

        $student = User::updateOrCreate(
            ['email' => 'test2@example.com'],
            [
                'name' => 'Test Student',
                'full_name' => 'Test Student',
                'password' => Hash::make($defaultPassword),
                'role' => 'student',
                'phone_number' => '+250780000002',
                'district' => 'Kigali',
                'sector' => 'Gasabo',
                'education_level' => 'Secondary',
                'is_verified_mentor' => false,
            ]
        );

        $mentorOne = User::updateOrCreate(
            ['email' => 'mentor1@example.com'],
            [
                'name' => 'Mentor One',
                'full_name' => 'Mentor One',
                'password' => Hash::make($defaultPassword),
                'role' => 'mentor',
                'phone_number' => '+250780000003',
                'district' => 'Kigali',
                'sector' => 'Kicukiro',
                'education_level' => 'University',
                'is_verified_mentor' => true,
            ]
        );

        $mentorTwo = User::updateOrCreate(
            ['email' => 'mentor2@example.com'],
            [
                'name' => 'Mentor Two',
                'full_name' => 'Mentor Two',
                'password' => Hash::make($defaultPassword),
                'role' => 'mentor',
                'phone_number' => '+250780000004',
                'district' => 'Musanze',
                'sector' => 'Muhoza',
                'education_level' => 'University',
                'is_verified_mentor' => true,
            ]
        );

        $opportunities = [
            [
                'title' => 'Rwanda Digital Skills Scholarship',
                'category' => 'scholarship',
                'description' => 'Support for learners building digital skills in Rwanda.',
                'provider_name' => 'Rwanda TVET Board',
                'eligibility_criteria' => 'Applicants must be enrolled in a technical training program.',
                'application_deadline' => now()->addMonth()->toDateString(),
                'external_link' => 'https://example.com/scholarship',
                'region_tags' => ['Kigali', 'Western'],
                'is_verified' => true,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Youth Coding Bootcamp',
                'category' => 'bootcamp',
                'description' => 'A six-week coding bootcamp for young job seekers.',
                'provider_name' => 'BridgeEdu',
                'eligibility_criteria' => 'Open to applicants aged 18-30.',
                'application_deadline' => now()->addWeeks(2)->toDateString(),
                'external_link' => 'https://example.com/bootcamp',
                'region_tags' => ['Kigali'],
                'is_verified' => true,
                'created_by' => $admin->id,
            ],
        ];

        foreach ($opportunities as $opportunity) {
            Opportunity::updateOrCreate(
                ['title' => $opportunity['title']],
                $opportunity
            );
        }

        $pathway = Pathway::updateOrCreate(
            ['title' => 'Software Engineering Fundamentals'],
            ['title' => 'Software Engineering Fundamentals', 'target_role' => 'Software Engineer']
        );

        $steps = [
            [
                'position' => 1,
                'title' => 'Learn HTML and CSS',
                'description' => 'Create simple landing pages with semantic HTML and modern CSS.',
                'resource_link' => 'https://example.com/html-css',
                'estimated_hours' => 6,
            ],
            [
                'position' => 2,
                'title' => 'Build with JavaScript',
                'description' => 'Practice DOM manipulation and event handling.',
                'resource_link' => 'https://example.com/javascript',
                'estimated_hours' => 8,
            ],
            [
                'position' => 3,
                'title' => 'Deploy your first project',
                'description' => 'Launch a project to a free hosting platform.',
                'resource_link' => 'https://example.com/deployment',
                'estimated_hours' => 4,
            ],
        ];

        foreach ($steps as $step) {
            PathwayStep::updateOrCreate(
                [
                    'pathway_id' => $pathway->id,
                    'position' => $step['position'],
                ],
                [
                    'pathway_id' => $pathway->id,
                    'position' => $step['position'],
                    'title' => $step['title'],
                    'description' => $step['description'],
                    'resource_link' => $step['resource_link'],
                    'estimated_hours' => $step['estimated_hours'],
                ]
            );
        }

        MentorshipRequest::updateOrCreate(
            [
                'student_id' => $student->id,
                'mentor_id' => $mentorTwo->id,
                'topic_of_interest' => 'Web Development',
            ],
            [
                'student_id' => $student->id,
                'mentor_id' => $mentorTwo->id,
                'status' => 'completed',
                'topic_of_interest' => 'Web Development',
            ]
        );

        MentorshipRequest::updateOrCreate(
            [
                'student_id' => $student->id,
                'mentor_id' => null,
                'topic_of_interest' => 'Data Science',
            ],
            [
                'student_id' => $student->id,
                'mentor_id' => null,
                'status' => 'pending',
                'topic_of_interest' => 'Data Science',
            ]
        );
    }
}
