<?php

namespace App\Http\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class DataTableResponse
{
    public static function make(int $draw, int $recordsTotal, int $recordsFiltered, Collection $rows, callable $mapper): JsonResponse
    {
        $data = [];
        $no = 1;

        foreach ($rows as $row) {
            $data[] = $mapper($row, $no++);
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }
}