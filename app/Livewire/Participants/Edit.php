<?php

declare(strict_types=1);

namespace App\Livewire\Participants;

use App\Livewire\AbstractFormModelComponentInterface;
use App\Models\Participant;
use Flux\Flux;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Edit extends AbstractFormModelComponentInterface
{
    public string $name = '';
    public ?string $comments = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $city = null;

    public array $groups;

    public Participant $participant;

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|min:3',
            'comments' => 'nullable|string',
            'phone'    => 'nullable|string',
            'email'    => 'nullable|email',
            'city'     => 'nullable|string',
            'groups'   => 'array|required|min:1',
        ];
    }

    public function mount(?Participant $participant): void
    {
        $this->participant = ($participant ?? new Participant());
        //            ->load([
        //                'workflows' => function ($query) {
        //                    $query->with(['workflowSteps']);
        //                    $query->withCount([
        //                        'workflowSteps',
        //                        'workflowSteps as completed_steps_count' => fn ($q) => $q->where('status', WorkflowStepStatus::FINISHED->value),
        //                    ]);
        //                },
        //            ]);
        $this->name = $this->participant->name ?? '';
        $this->phone = $this->participant->phone;
        $this->email = $this->participant->email;
        $this->city = $this->participant->city;
        $this->comments = $this->participant->comments;

        $this->groups = $this->participant->groups->pluck('id')->toArray();
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

        $this->participant->exists
            ? $this->participant->update($validated)
            : $this->participant = $this->participant->create($validated);

        $this->participant->groups()->sync($this->groups);

        Flux::toast(variant: 'success', text: __('Your changes have been saved.'));
        $this->redirect(url: route('participants.show', $this->participant->id), navigate: true);
    }

    public function getFormView(): View
    {
        return view('livewire.participants.form');
    }

    public function getHeading(): string
    {
        return __('Edit :model', ['model' => __('Participant')]);
    }
}
