<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Group;
use App\Models\Participant;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Scopes\UserGroupScope;
use Illuminate\Support\Facades\Auth;

trait DisablesUserGroupScope
{
    /**
     * Helper method to get models without UserGroupScope restrictions
     */
    protected function getCompanyWithoutScope()
    {
        return Company::withoutGlobalScope(UserGroupScope::class)->first();
    }

    protected function getParticipantWithoutScope()
    {
        return Participant::withoutGlobalScope(UserGroupScope::class)->first();
    }

    protected function getActivityWithoutScope()
    {
        return Activity::withoutGlobalScope(UserGroupScope::class)->first();
    }

    protected function getCompaniesWithoutScope($limit = 10)
    {
        return Company::withoutGlobalScope(UserGroupScope::class)->take($limit)->get();
    }

    protected function getParticipantsWithoutScope($limit = 10)
    {
        return Participant::withoutGlobalScope(UserGroupScope::class)->take($limit)->get();
    }

    protected function getActivitiesWithoutScope($limit = 10)
    {
        return Activity::withoutGlobalScope(UserGroupScope::class)->take($limit)->get();
    }

    protected function getWorkflowTemplateWithoutScope()
    {
        return WorkflowTemplate::withoutGlobalScope(UserGroupScope::class)->first();
    }

    protected function getWorkflowTemplatesWithoutScope($limit = 10)
    {
        return WorkflowTemplate::withoutGlobalScope(UserGroupScope::class)->take($limit)->get();
    }

    /**
     * Create and authenticate a user with access to all groups
     * This allows Livewire components to work properly with UserGroupScope
     */
    protected function authenticateUserWithFullAccess(): void
    {
        $user = User::factory()->create();

        // Get all groups and attach them to the user so they have access to all records
        $groups = Group::withoutGlobalScope(UserGroupScope::class)->get();
        if ($groups->isNotEmpty()) {
            $user->groups()->attach($groups->pluck('id'));
        }

        Auth::login($user);
    }
}
