<?php

namespace App\Http\Controllers\Api\purchase;

use App\Http\Controllers\Controller;
use App\Models\Grn;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GrnController extends Controller
{
    public function index(Request $request)
    {
        $query = Grn::with(['purchaseOrder', 'warehouse', 'receivedBy', 'verifiedBy', 'items.product']);

        // Filter by purchase order
        if ($request->has('purchase_order_id')) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by GRN number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'grn_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'grn_number', 'grn_date', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'grn_date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $grns = $query->paginate($request->get('per_page', 10));
        
        return response()->json($grns);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'grn_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.ordered_quantity' => 'required|integer|min:0',
            'items.*.received_quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.serial_numbers' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($validated['purchase_order_id']);

        // Generate GRN number
        $grnNumber = 'GRN-' . strtoupper(Str::random(6));
        while (Grn::where('grn_number', $grnNumber)->exists()) {
            $grnNumber = 'GRN-' . strtoupper(Str::random(6));
        }

        $grn = Grn::create([
            'grn_number' => $grnNumber,
            'purchase_order_id' => $validated['purchase_order_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'grn_date' => $validated['grn_date'],
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
            'received_by' => auth()->id(),
        ]);

        // Create GRN items
        foreach ($validated['items'] as $item) {
            $grn->items()->create([
                'product_id' => $item['product_id'],
                'ordered_quantity' => $item['ordered_quantity'],
                'received_quantity' => $item['received_quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['received_quantity'] * $item['unit_price'],
                'serial_numbers' => $item['serial_numbers'] ?? null,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $grn->load(['purchaseOrder', 'warehouse', 'receivedBy', 'items.product']);
        
        return response()->json($grn, 201);
    }

    public function show(Grn $grn)
    {
        $grn->load(['purchaseOrder', 'warehouse', 'receivedBy', 'verifiedBy', 'items.product', 'purchases']);
        return response()->json($grn);
    }

    public function update(Request $request, Grn $grn)
    {
        if ($grn->status !== 'draft') {
            return response()->json(['message' => 'Only draft GRNs can be updated'], 422);
        }

        $validated = $request->validate([
            'grn_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.ordered_quantity' => 'required|integer|min:0',
            'items.*.received_quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.serial_numbers' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
        ]);

        if (isset($validated['grn_date'])) {
            $grn->grn_date = $validated['grn_date'];
        }
        if (isset($validated['notes'])) {
            $grn->notes = $validated['notes'];
        }

        // Update items if provided
        if (isset($validated['items'])) {
            $grn->items()->delete();
            foreach ($validated['items'] as $item) {
                $grn->items()->create([
                    'product_id' => $item['product_id'],
                    'ordered_quantity' => $item['ordered_quantity'],
                    'received_quantity' => $item['received_quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['received_quantity'] * $item['unit_price'],
                    'serial_numbers' => $item['serial_numbers'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        }

        $grn->save();
        $grn->load(['purchaseOrder', 'warehouse', 'receivedBy', 'items.product']);
        
        return response()->json($grn);
    }

    public function verify(Request $request, Grn $grn)
    {
        if ($grn->status !== 'draft') {
            return response()->json(['message' => 'Only draft GRNs can be verified'], 422);
        }

        $grn->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $grn->load(['purchaseOrder', 'warehouse', 'receivedBy', 'verifiedBy', 'items.product']);
        
        return response()->json($grn);
    }

    public function destroy(Grn $grn)
    {
        if ($grn->status !== 'draft') {
            return response()->json(['message' => 'Only draft GRNs can be deleted'], 422);
        }

        $grn->delete();
        
        return response()->json(['message' => 'GRN deleted successfully']);
    }
}

