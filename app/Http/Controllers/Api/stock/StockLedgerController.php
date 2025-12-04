<?php

namespace App\Http\Controllers\Api\stock;

use App\Http\Controllers\Controller;
use App\Models\StockLedger;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        $query = StockLedger::with(['product', 'warehouse', 'creator']);

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by type (in/out)
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by reference type
        if ($request->has('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        // Search by reference number or product name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'transaction_date', 'quantity', 'unit_cost', 'total_cost', 'balance_after', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'transaction_date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $ledgers = $query->paginate($request->get('per_page', 25));
        
        return response()->json($ledgers);
    }

    public function show(StockLedger $stockLedger)
    {
        $stockLedger->load(['product', 'warehouse', 'creator']);
        return response()->json($stockLedger);
    }

    public function warehouses()
    {
        $warehouses = \App\Models\Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($warehouse) {
                return [
                    'value' => $warehouse->id,
                    'label' => $warehouse->name . ' (' . $warehouse->code . ')',
                ];
            });

        return response()->json([
            'warehouses' => $warehouses
        ]);
    }
}

