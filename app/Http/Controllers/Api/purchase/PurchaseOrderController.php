<?php

namespace App\Http\Controllers\Api\purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'purchaseRequest', 'createdBy', 'items.product']);

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

        // Search by PO number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'order_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'po_number', 'order_date', 'expected_delivery_date', 'status', 'total_amount', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'order_date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $orders = $query->paginate($request->get('per_page', 10));
        
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Calculate totals
        $subtotal = 0;
        $totalTax = 0;
        $totalDiscount = 0;

        foreach ($validated['items'] as $item) {
            $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
            $subtotal += $itemTotal;
            $totalTax += $item['tax'] ?? 0;
            $totalDiscount += $item['discount'] ?? 0;
        }

        $totalAmount = $subtotal + $totalTax;

        // Generate PO number
        $poNumber = 'PO-' . strtoupper(Str::random(6));
        while (PurchaseOrder::where('po_number', $poNumber)->exists()) {
            $poNumber = 'PO-' . strtoupper(Str::random(6));
        }

        $purchaseOrder = PurchaseOrder::create([
            'po_number' => $poNumber,
            'purchase_request_id' => $validated['purchase_request_id'] ?? null,
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'order_date' => $validated['order_date'],
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'status' => 'draft',
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'discount_amount' => $totalDiscount,
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Create PO items
        foreach ($validated['items'] as $item) {
            $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0) + ($item['tax'] ?? 0);
            $purchaseOrder->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'tax' => $item['tax'] ?? 0,
                'total' => $itemTotal,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $purchaseOrder->load(['supplier', 'warehouse', 'purchaseRequest', 'createdBy', 'items.product']);
        
        return response()->json($purchaseOrder, 201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'purchaseRequest', 'createdBy', 'items.product', 'grns']);
        return response()->json($purchaseOrder);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json(['message' => 'Only draft orders can be updated'], 422);
        }

        $validated = $request->validate([
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'order_date' => 'sometimes|required|date',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Update items if provided
        if (isset($validated['items'])) {
            $purchaseOrder->items()->delete();
            
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($validated['items'] as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $subtotal += $itemTotal;
                $totalTax += $item['tax'] ?? 0;
                $totalDiscount += $item['discount'] ?? 0;

                $itemTotalWithTax = $itemTotal + ($item['tax'] ?? 0);
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotalWithTax,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $purchaseOrder->subtotal = $subtotal;
            $purchaseOrder->tax_amount = $totalTax;
            $purchaseOrder->discount_amount = $totalDiscount;
            $purchaseOrder->total_amount = $subtotal + $totalTax;
        }

        if (isset($validated['supplier_id'])) {
            $purchaseOrder->supplier_id = $validated['supplier_id'];
        }
        if (isset($validated['warehouse_id'])) {
            $purchaseOrder->warehouse_id = $validated['warehouse_id'];
        }
        if (isset($validated['order_date'])) {
            $purchaseOrder->order_date = $validated['order_date'];
        }
        if (isset($validated['expected_delivery_date'])) {
            $purchaseOrder->expected_delivery_date = $validated['expected_delivery_date'];
        }
        if (isset($validated['notes'])) {
            $purchaseOrder->notes = $validated['notes'];
        }

        $purchaseOrder->save();
        $purchaseOrder->load(['supplier', 'warehouse', 'purchaseRequest', 'createdBy', 'items.product']);
        
        return response()->json($purchaseOrder);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json(['message' => 'Only draft orders can be deleted'], 422);
        }

        $purchaseOrder->delete();
        
        return response()->json(['message' => 'Purchase order deleted successfully']);
    }

    public function suppliers()
    {
        $suppliers = \App\Models\Supplier::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($supplier) {
                return [
                    'value' => $supplier->id,
                    'label' => $supplier->name . ($supplier->company_name ? ' - ' . $supplier->company_name : ''),
                ];
            });

        return response()->json([
            'suppliers' => $suppliers
        ]);
    }
}

