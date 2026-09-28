<?php

namespace App\Http\DataTables;

use Illuminate\Http\Request;

class DataTableRequest
{
    public int $draw;
    public int $start;
    public int $length;
    public string $search;
    public string $orderBy;
    public string $orderDir;

    public function __construct(Request $request, array $columns, string $defaultOrderBy = 'created_at')
    {
        $colIndex = (int) $request->input('order.0.column', -1);

        $this->draw     = (int) $request->input('draw', 1);
        $this->start    = max(0, (int) $request->input('start', 0));
        $this->length   = min(200, max(1, (int) $request->input('length', 25)));
        $this->search   = trim((string) $request->input('search.value', ''));
        $this->orderBy  = $columns[$colIndex] ?? $defaultOrderBy;
        $this->orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
    }
}