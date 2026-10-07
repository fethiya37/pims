<?php

namespace App\Http\DataTables;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DataTableQuery
{
    protected Builder $query;
    protected DataTableRequest $request;
    protected array $searchable = [];
    protected array $searchRelations = [];
    protected array $searchDates = [];

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

    public function searchDates(array $dateColumns): static
    {
        $this->searchDates = $dateColumns;
        return $this;
    }

    public function paginate(): array
{
    $recordsTotal = $this->countRows($this->query);

    $filtered = clone $this->query;

    $this->applySearch($filtered);

    $recordsFiltered = $this->request->search === ''
        ? $recordsTotal
        : $this->countRows($filtered);

    $rows = $filtered
        ->orderBy($this->request->orderBy, $this->request->orderDir)
        ->skip($this->request->start)
        ->take($this->request->length)
        ->get();

    return [$recordsTotal, $recordsFiltered, $rows];
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
        $parsedDate = $this->tryParseDate($term);

        $query->where(function ($q) use ($term, $parsedDate) {
            foreach ($this->searchable as $col) {
                $q->orWhere($col, 'like', "%{$term}%");
            }

            if ($parsedDate) {
                foreach ($this->searchDates as $col) {
                    $q->orWhereDate($col, $parsedDate);
                }
            }

            foreach ($this->searchRelations as $relation => $columns) {
                $q->orWhereHas($relation, function ($sub) use ($columns, $term) {
                    $sub->where(function ($relationQuery) use ($columns, $term) {
                        foreach ($columns as $index => $col) {
                            if ($index === 0) {
                                $relationQuery->where($col, 'like', "%{$term}%");
                            } else {
                                $relationQuery->orWhere($col, 'like', "%{$term}%");
                            }
                        }
                    });
                });
            }
        });
    }

  protected function tryParseDate(string $term): ?string
{
    $term = trim($term);

    $formats = [
        'Y-m-d',
        'Y-m-d H:i', 
        'Y/m/d',
        'd/m/Y',
        'm/d/Y',
        'd-m-Y',
        'd M Y',
        'M d Y',
        'd F Y',
        'F d Y',
        'M j, Y',
        'F j, Y',
    ];

    foreach ($formats as $format) {
        try {
            $date = Carbon::createFromFormat($format, $term);

            if ($date && $date->format($format) === $term) {
                return $date->toDateString();
            }
        } catch (\Exception $e) {
            continue;
        }
    }

    return null;
}
}