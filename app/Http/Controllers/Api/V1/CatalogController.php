<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Zone;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function zones()
    {
        return response()->json(
            Zone::where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ])
        );
    }

    public function branches(Request $request)
    {
        $request->validate([
            'zone_id' => ['required', 'exists:zones,id'],
        ]);

        return response()->json(
            Branch::where('zone_id', $request->zone_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ])
        );
    }

    public function warehouses(Request $request)
    {
        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $branch = Branch::findOrFail($request->branch_id);

        return response()->json(
            Warehouse::where('id', $branch->warehouse_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ])
        );
    }
}
