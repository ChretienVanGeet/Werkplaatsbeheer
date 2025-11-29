<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Traits\HasFluxTable;
use App\Models\Company;
use App\Models\Participant;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SelectItemsModal extends Component
{
    use HasFluxTable;
    #[Locked]
    public array $idsOnPage = [];
    #[Locked]
    public array $existingItems = [];
    #[Locked]
    public string $modalId;
    #[Locked]
    public string $className;

    public string $title;
    public string $subTitle;
    public string $modalTitle;
    public string $modalSubTitle;
    public string $addLabel;
    public ?int $deletingItemIndex = null;

    public bool $viewOnly = false;

    public function render(): View
    {
        $this->idsOnPage = $this->rows()->pluck('id')->toArray();

        return view('livewire.components.select-items-modal');
    }

    public function addItems(): void
    {
        $items = $this->baseQuery()->whereIn('id', $this->selectedItems)->get();
        $items->each(function (Model $item) {
            /** @var Participant|Company $item */
            $this->existingItems[] = ['id' => $item->id, 'label' => $item->name];
        });
        Flux::modal($this->modalId)->close();
        $this->selectedItems = [];
        $this->existingItemsUpdated();
    }

    protected function sortableFields(): array
    {
        return ['id', 'name'];
    }

    protected function baseQuery(): Builder
    {
        return $this->className::query();
    }

    protected function query(): Builder
    {
        return $this->baseQuery()->whereNotIn('id', collect($this->existingItems)->pluck('id'));
    }

    public function updateItemOrder(array $order): void
    {
        $reordered = [];

        foreach ($order as $item) {
            $key = (int) $item['value'];
            if (isset($this->existingItems[$key])) {
                $reordered[] = $this->existingItems[$key];
            }
        }

        $this->existingItems = $reordered;
        $this->existingItemsUpdated();
    }

    public function confirmDeleteItem(int $index): void
    {
        $this->deletingItemIndex = $index;
        Flux::modal('confirm-item-delete-'.$this->modalId)->show();
    }

    public function deleteItem(): void
    {
        if ($this->deletingItemIndex !== null) {
            unset($this->existingItems[$this->deletingItemIndex]);
            $this->existingItems = array_values($this->existingItems);
            $this->existingItemsUpdated();
            $this->deletingItemIndex = null;
        }
        Flux::modal('confirm-item-delete-'.$this->modalId)->close();
    }

    protected function existingItemsUpdated(): void
    {
        $this->dispatch('updateSelectedItems', modalId: $this->modalId, items: $this->existingItems);
    }
}
