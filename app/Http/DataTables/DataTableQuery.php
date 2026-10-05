<?php

namespace App\Http\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DataTableQuery
{
    protected Builder $query;
    protected DataTableRequest $request;
    protected array $searchable = [];
    protected array $searchRelations = [];

    public function __construct(Builder $query, DataTableRequest $request)
    {
        $this->query   = $query;
        $this->request = $request;
    }

    public function searchable(array $columns): static
    {
        $this->searchable = $columns;
        return $this;
    }

    public function searchRelations(array $relations): static
    {
        $this->searchRelations = $relations;
        return $this;
    }

    public function paginate(): array
    {
        $filtered = clone $this->query;
        $this->applySearch($filtered);

        $recordsFiltered = $this->countRows($filtered);

        $rows = $filtered
            ->orderBy($this->request->orderBy, $this->request->orderDir)
            ->skip($this->request->start)
            ->take($this->request->length)
            ->get();

        return [$recordsFiltered, $recordsFiltered, $rows];
    }

    protected function countRows(Builder $query): int
    {
        $base = $query->getQuery();

        if (empty($base->groups)) {
            return (int) $query->count();
        }

        $sql = $query->toSql();
        $bindings = $query->getBindings();

        $result = DB::selectOne("SELECT COUNT(*) AS c FROM ({$sql}) AS sub", $bindings);

        return (int) ($result->c ?? 0);
    }

    protected function applySearch(Builder $query): void
    {
        if ($this->request->search === '') {
            return;
        }

        $term = $this->request->search;

        $query->where(function (Builder $q) use ($term) {
            foreach ($this->searchable as $col) {
                $q->orWhere($col, 'like', "%{$term}%");
            }

            foreach ($this->searchRelations as $relation => $columns) {
                $q->orWhereHas($relation, function (Builder $sub) use ($columns, $term) {
                    foreach ($columns as $col) {
                        $sub->orWhere($col, 'like', "%{$term}%");
                    }
                });
            }
        });
    }
}