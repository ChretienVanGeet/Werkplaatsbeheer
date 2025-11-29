<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

trait HasFluxTable
{
    use WithPagination;

    public string $search = '';

    #[Url(as: 'sort')]
    public ?string $sortBy = null;

    #[Url(as: 'asc')]
    public string $sortDirection = 'desc';

    public array $selectedItems = [];

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function isFiltered(): bool
    {
        return $this->search !== '';
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage($this->getPageName());
    }

    #[On('refresh-table')]
    public function refresh(): void
    {
        $this->setPage($this->getPageName(), $this->getPage());
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $this->sortBy = in_array($this->sortBy, $this->sortableFields()) ? $this->sortBy : null;

        /** @var Builder $query */
        $query = $this->query()
            ->tap(function (Builder $query): Builder {
                return $this->sortBy
                    ? $query->orderBy($this->sortBy, $this->sortDirection)
                    : $query;
            })
            ->when($this->search, function (Builder $query): Builder {
                // @phpstan-ignore-next-line
                return $query->search($this->search);
            })
            ->tap(function (Builder $query): Builder {
                return $this->applyFilters($query);
            });

        return $query->paginate($this->getPageSize(), pageName: $this->getPageName());
    }

    public function updatedSearch(): void
    {
        $this->resetPage($this->getPageName());
    }

    protected function getPageName(): string
    {
        return 'page';
    }

    abstract protected function query(): Builder;

    protected function sortableFields(): array
    {
        return [];
    }

    protected function getPageSize(): int
    {
        return 10;
    }

    protected function applySorting(Builder $query): Builder
    {
        if ($this->sortBy === null) {
            return $query;
        }

        return $query->orderBy($this->sortBy, $this->sortDirection);
    }

    protected function clearSelections(): void
    {
        $this->reset('selectedItems');
    }

    protected function applyFilters(Builder $query): Builder
    {
        return $query;
    }
}
