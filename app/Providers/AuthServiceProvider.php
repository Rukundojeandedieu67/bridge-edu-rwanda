<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Opportunity;
use App\Policies\OpportunityPolicy;
use App\Models\Pathway;
use App\Policies\PathwayPolicy;
use App\Models\PathwayStep;
use App\Policies\PathwayStepPolicy;
use App\Models\MentorshipRequest;
use App\Policies\MentorshipRequestPolicy;
use App\Models\OpportunityApplication;
use App\Policies\OpportunityApplicationPolicy;
use App\Models\User;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<string,string>
     */
    protected $policies = [
        Opportunity::class => OpportunityPolicy::class,
        OpportunityApplication::class => OpportunityApplicationPolicy::class,
        User::class => UserPolicy::class,
        Pathway::class => PathwayPolicy::class,
        PathwayStep::class => PathwayStepPolicy::class,
        MentorshipRequest::class => MentorshipRequestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
