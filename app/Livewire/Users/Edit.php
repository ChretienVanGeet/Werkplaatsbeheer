<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Livewire\AbstractFormModelComponentInterface;
use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Edit extends AbstractFormModelComponentInterface
{
    public string $name = '';
    public string $email = '';
    public ?string $mobile = null;
    public ?string $organisation = null;
    public string $role;
    public array $groups;
    public array $userRoles;

    public User $user;

    protected function rules(): array
    {
        return [
            'name'         => 'string|required',
            'email'        => 'required|string|email',
            'mobile'       => 'nullable|string',
            'organisation' => 'nullable|string',
            'role'         => ['required', new Enum(UserRole::class)],
            'groups'       => 'array|required|min:1',
        ];
    }

    public function mount(?User $user): void
    {
        $this->user = $user ?? new User();
        $this->name = $this->user->name ?? '';
        $this->email = $this->user->email ?? '';
        $this->mobile = $this->user->mobile;
        $this->organisation = $this->user->organisation;
        $this->role = $this->user->role->value;
        $this->groups = $this->user->groups->pluck('id')->toArray();
        $this->userRoles = UserRole::list();
    }

    public function save(): void
    {
        try {
            $validated = $this->validate();
        } catch (ValidationException $e) {
            $errors = collect($e->errors())->flatten();

            $text = $errors->count() === 1
                ? $errors->first()
                : __('There are :count validation errors. Please check the form.', ['count' => $errors->count()]);

            Flux::toast(text: $text, variant: 'danger');

            throw $e;
        }

        $this->user->exists
            ? $this->user->update($validated)
            : $this->user = $this->user->create($validated);

        $this->user->groups()->sync($this->groups);

        Flux::toast(variant: 'success', text: __('Your changes have been saved.'));
        $this->redirect(url: route('users.edit', $this->user->id), navigate: true);
    }

    public function getFormView(): View
    {
        return view('livewire.users.form');
    }

    public function getHeading(): string
    {
        return __('Edit :model', ['model' => __('User')]);
    }
}
