<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Group;
use App\Models\Note;
use App\Models\Participant;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateStep;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed the database with sample data for development.
     */
    public function run(): void
    {

        $adminUser = User::factory()->admin()->create([
            'name'       => 'Development',
            'email'      => 'info@fruitcake.nl',
            'created_at' => Carbon::now()->subDays(23),
        ]);

        $users = User::factory(15)->unverified()->for($adminUser, 'creator')->for($adminUser, 'updater')->create();

        $groups = Group::factory()->count(4)->create();
        $adminUser->groups()->attach($groups->pluck('id')); // All groups to admin

        $users->each(function ($user) use ($groups) {
            $randomGroupIds = $groups->random(rand(2, 4))->pluck('id')->toArray(); // Random amount to users, at least one
            $user->groups()->attach($randomGroupIds);
        });

        $companies = Company::factory(15)->for($adminUser, 'creator')->for($adminUser, 'updater')->create();
        $participants = Participant::factory(15)->for($adminUser, 'creator')->create();

        $companies->each(function (Company $company) use ($adminUser) {
            $this->createNotes($company, rand(0, 2), $adminUser);
        });

        $participants->each(function (Participant $participant) use ($adminUser) {
            $this->createNotes($participant, rand(0, 2), $adminUser);
        });

        $activities = Activity::factory(5)->for($adminUser, 'creator')->for($adminUser, 'updater')->create()->each(function (Activity $activity) use ($companies, $participants, $adminUser) {

            $randomParticipants = $participants->random(rand(3, 6))->values();
            $participantPivotData = $randomParticipants->mapWithKeys(function ($participant, $index) {
                return [$participant->id => ['sort_order' => $index]];
            })->toArray();
            $activity->participants()->attach($participantPivotData);

            $randomCompanies = $companies->random(rand(1, 2))->values();
            $companyPivotData = $randomCompanies->mapWithKeys(function ($company, $index) {
                return [$company->id => ['sort_order' => $index]];
            })->toArray();
            $activity->companies()->attach($companyPivotData);

            $this->createNotes($activity, rand(0, 2), $adminUser);
        });

        WorkflowTemplate::factory(15)->for($adminUser, 'creator')->for($adminUser, 'updater')->create()->each(function (WorkflowTemplate $workflowTemplate) use ($activities, $companies, $participants, $adminUser) {
            $sortOrder = 1;
            WorkflowTemplateStep::factory()->count(rand(1, 3))->for($workflowTemplate)->create()->each(function ($workflowTemplateStep) use (&$sortOrder) {
                $workflowTemplateStep->update(['sort_order' => $sortOrder]);
                $sortOrder++;
            });

            $randomCompanies = $companies->random(rand(1, 2))->values();
            $randomCompanies->each(function ($company, $index) use ($workflowTemplate, $adminUser) {
                $this->attachWorkflowTemplateToSubject($company, $workflowTemplate, $adminUser);
            });

            $randomParticipants = $participants->random(rand(3, 5))->values();
            $randomParticipants->each(function ($participant, $index) use ($workflowTemplate, $adminUser) {
                $this->attachWorkflowTemplateToSubject($participant, $workflowTemplate, $adminUser);
            });

            $randomActivities = $activities->random(rand(2, 3))->values();
            $randomActivities->each(function ($activity, $index) use ($workflowTemplate, $adminUser) {
                $this->attachWorkflowTemplateToSubject($activity, $workflowTemplate, $adminUser);
            });
        });
    }

    private function createNotes(Model $model, int $count, User $createdBy): void
    {
        if (! $count) {
            return;
        }
        $notes = Note::factory()->for($createdBy, 'creator')->for($createdBy, 'updater')->count($count)->make();
        $notes->each(function (Note $note) use ($model) {
            $model->notes()->save($note);
        });
    }

    private function attachWorkflowTemplateToSubject(Model $subject, WorkflowTemplate $workflowTemplate, User $createdBy): void
    {
        $workflow = Workflow::factory()
            ->for($subject, 'subject')
            ->for($workflowTemplate)
            ->for($createdBy, 'creator')
            ->for($createdBy, 'updater')
            ->create();

        $workflowTemplate->workflowTemplateSteps->each(function (WorkflowTemplateStep $workflowTemplateStep) use ($workflow, $createdBy) {
            WorkflowStep::factory()
                ->for($workflow)
                ->for($workflowTemplateStep)
                ->for($createdBy, 'creator')
                ->for($createdBy, 'updater')
                ->create();
        });

    }
}
