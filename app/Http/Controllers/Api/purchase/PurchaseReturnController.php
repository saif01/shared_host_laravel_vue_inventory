<?php

namespace App\Http\Controllers\Api\purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['purchase', 'supplier', 'warehouse', 'createdBy', 'approvedBy', 'items.product']);

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by return number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'return_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'return_number', 'return_date', 'status', 'total_amount', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'return_date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $returns = $query->paginate($request->get('per_page', 10));
        
        return response()->json($returns);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|in:defective,wrong_item,damaged,other',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $purchase = Purchase::findOrFail($validated['purchase_id']);

        // Generate return number
        $returnNumber = 'PRET-' . strtoupper(Str::random(6));
        while (PurchaseReturn::where('return_number', $returnNumber)->exists()) {
            $returnNumber = 'PRET-' . strtoupper(Str::random(6));
        }

        // Calculate total
        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $purchaseReturn = PurchaseReturn::create([
            'return_number' => $returnNumber,
            'purchase_id' => $validated['purchase_id'],
            'supplier_id' => $purchase->supplier_id,
            'warehouse_id' => $purchase->warehouse_id,
            'return_date' => $validated['return_date'],
            'status' => 'draft',
            'reason' => $validated['reason'] ?? 'other',
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Create return items
        foreach ($validated['items'] as $item) {
            $purchaseReturn->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $purchaseReturn->load(['purchase', 'supplier', 'warehouse', 'createdBy', 'items.product']);
        
        return response()->json($purchaseReturn, 201);
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase', 'supplier', 'warehouse', 'createdBy', 'approvedBy', 'items.product']);
        return response()->json($purchaseReturn);
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'draft') {
            return response()->json(['message' => 'Only draft returns can be updated'], 422);
        }

        $validated = $request->validate([
            'return_date' => 'sometimes|required|date',
            'reason' => 'nullable|in:defective,wrong_item,damaged,other',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        if (isset($validated['return_date'])) {
            $purchaseReturn->return_date = $validated['return_date'];
        }
        if (isset($validated['reason'])) {
            $purchaseReturn->reason = $validated['reason'];
        }
        if (isset($validated['notes'])) {
            $purchaseReturn->notes = $validated['notes'];
        }

        // Update items if provided
        if (isset($validated['items'])) {
            $purchaseReturn->items()->delete();
            
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $itemTotal;
                
                $purchaseReturn->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $itemTotal,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $purchaseReturn->total_amount = $totalAmount;
        }

        $purchaseReturn->save();
        $purchaseReturn->load(['purchase', 'supplier', 'warehouse', 'createdBy', 'items.product']);
        
        return response()->json($purchaseReturn);
    }

    public function approve(Request $request, PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'draft') {
            return response()->json(['message' => 'Only draft returns can be approved'], 422);
        }

        // Check stock availability
        foreach ($purchaseReturn->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $purchaseReturn->warehouse_id)
                ->first();
            
            if (!$stock || $stock->quantity < $item->quantity) {
                return response()->json([
                    'message' => "Insufficient stock for product: {$item->product->name}"
                ], 422);
            }
        }

        $purchaseReturn->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $purchaseReturn->load(['purchase', 'supplier', 'warehouse', 'createdBy', 'approvedBy', 'items.product']);
        
        return response()->json($purchaseReturn);
    }

    public function complete(Request $request, PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'approved') {
            return response()->json(['message' => 'Only approved returns can be completed'], 422);
        }

        // Process stock movement (stock out)
        foreach ($purchaseReturn->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $purchaseReturn->warehouse_id)
                ->first();
            
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

                // Create stock ledger entry
                StockLedger::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchaseReturn->warehouse_id,
                    'type' => 'out',
                    'reference_type' => 'purchase_return',
                    'reference_id' => $purchaseReturn->id,
                    'reference_number' => $purchaseReturn->return_number,
                    'quantity' => $item->quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'balance_after' => $newQty,
                    'notes' => $purchaseReturn->reason,
                    'created_by' => auth()->id(),
                    'transaction_date' => $purchaseReturn->return_date,
                ]);
            }
        }

        $purchaseReturn->update([
            'status' => 'completed',
        ]);

        $purchaseReturn->load(['purchase', 'supplier', 'warehouse', 'createdBy', 'approvedBy', 'items.product']);
        
        return response()->json($purchaseReturn);
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'draft') {
            return response()->json(['message' => 'Only draft returns can be deleted'], 422);
        }

        $purchaseReturn->delete();
        
        return response()->json(['message' => 'Purchase return deleted successfully']);
    }
}

