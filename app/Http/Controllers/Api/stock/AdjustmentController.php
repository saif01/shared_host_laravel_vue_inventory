<?php

namespace App\Http\Controllers\Api\stock;

use App\Http\Controllers\Controller;
use App\Models\Adjustment;
use App\Models\Stock;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Adjustment::with(['warehouse', 'createdBy', 'approvedBy', 'items.product']);

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search by adjustment number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'adjustment_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'adjustment_number', 'adjustment_date', 'status', 'type', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'adjustment_date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $adjustments = $query->paginate($request->get('per_page', 10));
        
        return response()->json($adjustments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_date' => 'required|date',
            'type' => 'required|in:increase,decrease',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Generate adjustment number
        $adjustmentNumber = 'ADJ-' . strtoupper(Str::random(6));
        while (Adjustment::where('adjustment_number', $adjustmentNumber)->exists()) {
            $adjustmentNumber = 'ADJ-' . strtoupper(Str::random(6));
        }

        $adjustment = Adjustment::create([
            'adjustment_number' => $adjustmentNumber,
            'warehouse_id' => $validated['warehouse_id'],
            'adjustment_date' => $validated['adjustment_date'],
            'status' => 'draft',
            'type' => $validated['type'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Create adjustment items
        foreach ($validated['items'] as $item) {
            $adjustment->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'] ?? 0,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $adjustment->load(['warehouse', 'createdBy', 'items.product']);
        
        return response()->json($adjustment, 201);
    }

    public function show(Adjustment $adjustment)
    {
        $adjustment->load(['warehouse', 'createdBy', 'approvedBy', 'items.product']);
        return response()->json($adjustment);
    }

    public function update(Request $request, Adjustment $adjustment)
    {
        // Only allow updating draft adjustments
        if ($adjustment->status !== 'draft') {
            return response()->json(['message' => 'Only draft adjustments can be updated'], 422);
        }

        $validated = $request->validate([
            'adjustment_date' => 'sometimes|required|date',
            'type' => 'sometimes|required|in:increase,decrease',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        if (isset($validated['adjustment_date'])) {
            $adjustment->adjustment_date = $validated['adjustment_date'];
        }
        if (isset($validated['type'])) {
            $adjustment->type = $validated['type'];
        }
        if (isset($validated['reason'])) {
            $adjustment->reason = $validated['reason'];
        }
        if (isset($validated['notes'])) {
            $adjustment->notes = $validated['notes'];
        }

        // Update items if provided
        if (isset($validated['items'])) {
            $adjustment->items()->delete();
            foreach ($validated['items'] as $item) {
                $adjustment->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        }

        $adjustment->save();
        $adjustment->load(['warehouse', 'createdBy', 'items.product']);
        
        return response()->json($adjustment);
    }

    public function approve(Request $request, Adjustment $adjustment)
    {
        if ($adjustment->status !== 'draft') {
            return response()->json(['message' => 'Only draft adjustments can be approved'], 422);
        }

        // Check stock availability for decrease adjustments
        if ($adjustment->type === 'decrease') {
            foreach ($adjustment->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $adjustment->warehouse_id)
                    ->first();
                
                if (!$stock || $stock->quantity < $item->quantity) {
                    return response()->json([
                        'message' => "Insufficient stock for product: {$item->product->name}"
                    ], 422);
                }
            }
        }

        $adjustment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json($adjustment);
    }

    public function complete(Request $request, Adjustment $adjustment)
    {
        if ($adjustment->status !== 'approved') {
            return response()->json(['message' => 'Only approved adjustments can be completed'], 422);
        }

        // Process stock movement
        foreach ($adjustment->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $adjustment->warehouse_id)
                ->first();
            
            if ($adjustment->type === 'increase') {
                // Stock increase
                if ($stock) {
                    $oldQty = $stock->quantity;
                    $newQty = $oldQty + $item->quantity;
                    $oldValue = $stock->total_value;
                    $unitCost = $item->unit_cost > 0 ? $item->unit_cost : $stock->average_cost;
                    $newValue = $oldValue + ($item->quantity * $unitCost);
                    $avgCost = $newValue / $newQty;

                    $stock->update([
                        'quantity' => $newQty,
                        'average_cost' => $avgCost,
                        'total_value' => $newValue,
                    ]);
                } else {
                    $unitCost = $item->unit_cost > 0 ? $item->unit_cost : 0;
                    Stock::create([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $adjustment->warehouse_id,
                        'quantity' => $item->quantity,
                        'average_cost' => $unitCost,
                        'total_value' => $item->quantity * $unitCost,
                    ]);
                }

                $balanceAfter = $stock ? $stock->quantity + $item->quantity : $item->quantity;
            } else {
                // Stock decrease
                if ($stock && $stock->quantity >= $item->quantity) {
                    $oldQty = $stock->quantity;
                    $newQty = $oldQty - $item->quantity;
                    $unitCost = $stock->average_cost;
                    $totalCost = $item->quantity * $unitCost;
                    $newValue = $stock->total_value - $totalCost;

                    $stock->update([
                        'quantity' => $newQty,
                        'total_value' => $newValue,
                    ]);

                    $balanceAfter = $newQty;
                } else {
                    continue; // Skip if insufficient stock
                }
            }

            // Create stock ledger entry
            StockLedger::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $adjustment->warehouse_id,
                'type' => $adjustment->type === 'increase' ? 'in' : 'out',
                'reference_type' => 'adjustment',
                'reference_id' => $adjustment->id,
                'reference_number' => $adjustment->adjustment_number,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost > 0 ? $item->unit_cost : ($stock ? $stock->average_cost : 0),
                'total_cost' => $item->quantity * ($item->unit_cost > 0 ? $item->unit_cost : ($stock ? $stock->average_cost : 0)),
                'balance_after' => $balanceAfter,
                'notes' => $adjustment->reason . ($item->notes ? ' - ' . $item->notes : ''),
                'created_by' => auth()->id(),
                'transaction_date' => $adjustment->adjustment_date,
            ]);
        }

        $adjustment->update([
            'status' => 'completed',
        ]);

        $adjustment->load(['warehouse', 'createdBy', 'approvedBy', 'items.product']);
        
        return response()->json($adjustment);
    }

    public function destroy(Adjustment $adjustment)
    {
        if ($adjustment->status !== 'draft') {
            return response()->json(['message' => 'Only draft adjustments can be deleted'], 422);
        }

        $adjustment->delete();
        
        return response()->json(['message' => 'Adjustment deleted successfully']);
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

