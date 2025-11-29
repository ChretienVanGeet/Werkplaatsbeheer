<?php

declare(strict_types=1);

use App\Livewire\Activities;
use App\Livewire\Companies;
use App\Livewire\Dashboard;
use App\Livewire\Groups;
use App\Livewire\Participants;
use App\Livewire\Resources;
use App\Livewire\Instructors;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Users;
use App\Livewire\WorkflowTemplates;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', '2fa'])->group(function () {
    Route::get('/', Dashboard\Index::class)->name('dashboard');

    Route::middleware('can:read')->group(function () {
        Route::get('companies', Companies\Index::class)->name('companies.index');
        Route::get('companies/show/{company}', Companies\Show::class)->name('companies.show');

        Route::get('participants', Participants\Index::class)->name('participants.index');
        Route::get('participants/show/{participant}', Participants\Show::class)->name('participants.show');

        Route::get('activities', Activities\Index::class)->name('activities.index');
        Route::get('activities/show/{activity}', Activities\Show::class)->name('activities.show');

        Route::get('instructors', Instructors\Index::class)->name('instructors.index');
        Route::get('instructors/show/{instructor}', Instructors\Show::class)->name('instructors.show');

        Route::get('resources', Resources\Index::class)->name('resources.index');
        Route::get('resources/show/{resource}', Resources\Show::class)->name('resources.show');
    });

    Route::middleware('can:write')->group(function () {
        Route::get('companies/create', Companies\Create::class)->name('companies.create');
        Route::get('companies/edit/{company}', Companies\Edit::class)->name('companies.edit');

        Route::get('participants/create', Participants\Create::class)->name('participants.create');
        Route::get('participants/edit/{participant}', Participants\Edit::class)->name('participants.edit');

        Route::get('activities/create', Activities\Create::class)->name('activities.create');
        Route::get('activities/edit/{activity}', Activities\Edit::class)->name('activities.edit');

        Route::get('instructors/create', Instructors\Create::class)->name('instructors.create');
        Route::get('instructors/edit/{instructor}', Instructors\Edit::class)->name('instructors.edit');

        Route::get('resources/create', Resources\Create::class)->name('resources.create');
        Route::get('resources/edit/{resource}', Resources\Edit::class)->name('resources.edit');
    });

    Route::middleware('can:manage')->group(function () {

        Route::get('users', Users\Index::class)->name('users.index');
        Route::get('users/create', Users\Create::class)->name('users.create');
        Route::get('users/edit/{user}', Users\Edit::class)->name('users.edit');

        Route::get('groups', Groups\Index::class)->name('groups.index');
        Route::get('groups/create', Groups\Create::class)->name('groups.create');
        Route::get('groups/edit/{group}', Groups\Edit::class)->name('groups.edit');

        Route::get('workflow-templates', WorkflowTemplates\Index::class)->name('workflow-templates.index');
        Route::get('workflow-templates/create', WorkflowTemplates\Create::class)->name('workflow-templates.create');
        Route::get('workflow-templates/edit/{workflowTemplate}', WorkflowTemplates\Edit::class)->name('workflow-templates.edit');

    });

    // TODO: move these
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/two-factor', \App\Livewire\Settings\TwoFactorAuthentication::class)->name('settings.two-factor');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

});

require __DIR__.'/auth.php';
