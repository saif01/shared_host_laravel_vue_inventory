<?php

namespace App\Http\Controllers\Api\stock;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\Stock;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $query = Transfer::with(['fromWarehouse', 'toWarehouse', 'requestedBy', 'approvedBy', 'receivedBy', 'items.product']);

        // Filter by warehouse
        if ($request->has('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->from_warehouse_id);
        }
        if ($request->has('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->to_warehouse_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by transfer number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'transfer_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'transfer_number', 'transfer_date', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'transfer_date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $transfers = $query->paginate($request->get('per_page', 10));
        
        return response()->json($transfers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.serial_numbers' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
        ]);

        // Generate transfer number
        $transferNumber = 'TRF-' . strtoupper(Str::random(6));
        while (Transfer::where('transfer_number', $transferNumber)->exists()) {
            $transferNumber = 'TRF-' . strtoupper(Str::random(6));
        }

        $transfer = Transfer::create([
            'transfer_number' => $transferNumber,
            'from_warehouse_id' => $validated['from_warehouse_id'],
            'to_warehouse_id' => $validated['to_warehouse_id'],
            'transfer_date' => $validated['transfer_date'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'requested_by' => auth()->id(),
        ]);

        // Create transfer items
        foreach ($validated['items'] as $item) {
            $transfer->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'serial_numbers' => $item['serial_numbers'] ?? null,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $transfer->load(['fromWarehouse', 'toWarehouse', 'requestedBy', 'items.product']);
        
        return response()->json($transfer, 201);
    }

    public function show(Transfer $transfer)
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'requestedBy', 'approvedBy', 'receivedBy', 'items.product']);
        return response()->json($transfer);
    }

    public function update(Request $request, Transfer $transfer)
    {
        // Only allow updating pending transfers
        if ($transfer->status !== 'pending') {
            return response()->json(['message' => 'Only pending transfers can be updated'], 422);
        }

        $validated = $request->validate([
            'transfer_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.serial_numbers' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
        ]);

        if (isset($validated['transfer_date'])) {
            $transfer->transfer_date = $validated['transfer_date'];
        }
        if (isset($validated['notes'])) {
            $transfer->notes = $validated['notes'];
        }

        // Update items if provided
        if (isset($validated['items'])) {
            $transfer->items()->delete();
            foreach ($validated['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'serial_numbers' => $item['serial_numbers'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        }

        $transfer->save();
        $transfer->load(['fromWarehouse', 'toWarehouse', 'requestedBy', 'items.product']);
        
        return response()->json($transfer);
    }

    public function approve(Request $request, Transfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return response()->json(['message' => 'Only pending transfers can be approved'], 422);
        }

        // Check stock availability
        foreach ($transfer->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $transfer->from_warehouse_id)
                ->first();
            
            if (!$stock || $stock->quantity < $item->quantity) {
                return response()->json([
                    'message' => "Insufficient stock for product: {$item->product->name}"
                ], 422);
            }
        }

        $transfer->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json($transfer);
    }

    public function receive(Request $request, Transfer $transfer)
    {
        if ($transfer->status !== 'approved') {
            return response()->json(['message' => 'Only approved transfers can be received'], 422);
        }

        // Process stock movement
        foreach ($transfer->items as $item) {
            // Stock Out from source warehouse
            $fromStock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $transfer->from_warehouse_id)
                ->first();
            
            if ($fromStock && $fromStock->quantity >= $item->quantity) {
                $oldQty = $fromStock->quantity;
                $newQty = $oldQty - $item->quantity;
                $unitCost = $fromStock->average_cost;
                $totalCost = $item->quantity * $unitCost;
                $newValue = $fromStock->total_value - $totalCost;

                $fromStock->update([
                    'quantity' => $newQty,
                    'total_value' => $newValue,
                ]);

                // Create stock ledger entry for transfer out
                StockLedger::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'type' => 'out',
                    'reference_type' => 'transfer_out',
                    'reference_id' => $transfer->id,
                    'reference_number' => $transfer->transfer_number,
                    'quantity' => $item->quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'balance_after' => $newQty,
                    'created_by' => auth()->id(),
                    'transaction_date' => $transfer->transfer_date,
                ]);
            }

            // Stock In to destination warehouse
            $toStock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $transfer->to_warehouse_id)
                ->first();
            
            if ($toStock) {
                $oldQty = $toStock->quantity;
                $newQty = $oldQty + $item->quantity;
                $oldValue = $toStock->total_value;
                $unitCost = $fromStock->average_cost;
                $newValue = $oldValue + ($item->quantity * $unitCost);
                $avgCost = $newValue / $newQty;

                $toStock->update([
                    'quantity' => $newQty,
                    'average_cost' => $avgCost,
                    'total_value' => $newValue,
                ]);
            } else {
                $unitCost = $fromStock->average_cost;
                Stock::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'quantity' => $item->quantity,
                    'average_cost' => $unitCost,
                    'total_value' => $item->quantity * $unitCost,
                ]);
            }

            // Create stock ledger entry for transfer in
            StockLedger::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $transfer->to_warehouse_id,
                'type' => 'in',
                'reference_type' => 'transfer_in',
                'reference_id' => $transfer->id,
                'reference_number' => $transfer->transfer_number,
                'quantity' => $item->quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $item->quantity * $unitCost,
                'balance_after' => $newQty ?? $item->quantity,
                'created_by' => auth()->id(),
                'transaction_date' => $transfer->transfer_date,
            ]);
        }

        $transfer->update([
            'status' => 'completed',
            'received_by' => auth()->id(),
            'received_at' => now(),
        ]);

        $transfer->load(['fromWarehouse', 'toWarehouse', 'requestedBy', 'approvedBy', 'receivedBy', 'items.product']);
        
        return response()->json($transfer);
    }

    public function destroy(Transfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return response()->json(['message' => 'Only pending transfers can be deleted'], 422);
        }

        $transfer->delete();
        
        return response()->json(['message' => 'Transfer deleted successfully']);
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

