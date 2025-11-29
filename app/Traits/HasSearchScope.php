<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSearchScope
{
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $columns = $this->getSearchableColumns();

        return $query->where(function (Builder $query) use ($columns, $term) {
            foreach ($columns as $column) {
                // Check if it's a relationship-based search (contains dot but not a table.column)
                if (str_contains($column, '.') && !$this->isJoinedColumn($query, $column)) {
                    $this->addRelationshipSearch($query, $column, $term);
                } else {
                    // Regular column search (including joined columns)
                    try {
                        $query->orWhere($column, 'like', "%{$term}%");
                    } catch (\Exception $e) {
                        // Skip if column doesn't exist (e.g., join not available)
                        continue;
                    }
                }
            }
        });
    }

    private function isJoinedColumn(Builder $query, string $column): bool
    {
        if (!str_contains($column, '.')) {
            return false;
        }

        [$table] = explode('.', $column, 2);

        if (!isset($query->joins)) {
            return false;
        }

        foreach ($query->joins as $join) {
            $joinTable = $join->table;

            // Handle aliased tables (e.g., "companies as subject_companies")
            if (str_contains($joinTable, ' as ')) {
                [, $alias] = explode(' as ', $joinTable, 2);
                if (trim($alias) === $table) {
                    return true;
                }
            }

            if ($joinTable === $table) {
                return true;
            }
        }

        return false;
    }

    private function addRelationshipSearch(Builder $query, string $relationPath, string $term): void
    {
        [$relation, $column] = explode('.', $relationPath, 2);

        // Check if the relationship method exists before using whereHas
        $model = $query->getModel();
        if (!method_exists($model, $relation)) {
            return;
        }

        $query->orWhereHas($relation, function (Builder $subQuery) use ($column, $term) {
            $subQuery->where($column, 'like', "%{$term}%");
        });
    }

    /**
     * Models using this trait must define this method
     * @return array<int, string>
     */
    abstract public function getSearchableColumns(): array;
}
