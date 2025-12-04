<?php

namespace App\Http\Controllers\Api\purchase;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Grn;
use App\Models\Stock;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'warehouse', 'grn', 'createdBy', 'items.product']);

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

        // Search by invoice number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'invoice_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'invoice_number', 'invoice_date', 'due_date', 'status', 'total_amount', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'invoice_date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $purchases = $query->paginate($request->get('per_page', 10));
        
        return response()->json($purchases);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'grn_id' => 'nullable|exists:grns,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        // Calculate totals if not provided
        $subtotal = $validated['subtotal'] ?? 0;
        $totalTax = $validated['tax_amount'] ?? 0;
        $totalDiscount = $validated['discount_amount'] ?? 0;
        $shippingCost = $validated['shipping_cost'] ?? 0;

        if ($subtotal == 0) {
            foreach ($validated['items'] as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $subtotal += $itemTotal;
                $totalTax += $item['tax'] ?? 0;
                $totalDiscount += $item['discount'] ?? 0;
            }
        }

        $totalAmount = $subtotal + $totalTax + $shippingCost - $totalDiscount;

        // Generate invoice number
        $invoiceNumber = 'INV-P-' . strtoupper(Str::random(6));
        while (Purchase::where('invoice_number', $invoiceNumber)->exists()) {
            $invoiceNumber = 'INV-P-' . strtoupper(Str::random(6));
        }

        $purchase = Purchase::create([
            'invoice_number' => $invoiceNumber,
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'grn_id' => $validated['grn_id'] ?? null,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'draft',
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'discount_amount' => $totalDiscount,
            'shipping_cost' => $shippingCost,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'balance_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Create purchase items
        foreach ($validated['items'] as $item) {
            $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0) + ($item['tax'] ?? 0);
            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'tax' => $item['tax'] ?? 0,
                'total' => $itemTotal,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $purchase->load(['supplier', 'warehouse', 'grn', 'createdBy', 'items.product']);
        
        return response()->json($purchase, 201);
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'warehouse', 'grn', 'createdBy', 'items.product', 'purchaseReturns']);
        return response()->json($purchase);
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status !== 'draft') {
            return response()->json(['message' => 'Only draft purchases can be updated'], 422);
        }

        $validated = $request->validate([
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'invoice_date' => 'sometimes|required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        // Update items if provided
        if (isset($validated['items'])) {
            $purchase->items()->delete();
            
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($validated['items'] as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $subtotal += $itemTotal;
                $totalTax += $item['tax'] ?? 0;
                $totalDiscount += $item['discount'] ?? 0;

                $itemTotalWithTax = $itemTotal + ($item['tax'] ?? 0);
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotalWithTax,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $purchase->subtotal = $subtotal;
            $purchase->tax_amount = $totalTax;
            $purchase->discount_amount = $totalDiscount;
            $purchase->total_amount = $subtotal + $totalTax + ($validated['shipping_cost'] ?? $purchase->shipping_cost) - $totalDiscount;
        }

        if (isset($validated['supplier_id'])) {
            $purchase->supplier_id = $validated['supplier_id'];
        }
        if (isset($validated['warehouse_id'])) {
            $purchase->warehouse_id = $validated['warehouse_id'];
        }
        if (isset($validated['invoice_date'])) {
            $purchase->invoice_date = $validated['invoice_date'];
        }
        if (isset($validated['due_date'])) {
            $purchase->due_date = $validated['due_date'];
        }
        if (isset($validated['notes'])) {
            $purchase->notes = $validated['notes'];
        }
        if (isset($validated['shipping_cost'])) {
            $purchase->shipping_cost = $validated['shipping_cost'];
            $purchase->total_amount = $purchase->subtotal + $purchase->tax_amount + $validated['shipping_cost'] - $purchase->discount_amount;
        }

        $purchase->balance_amount = $purchase->total_amount - $purchase->paid_amount;
        $purchase->save();
        $purchase->load(['supplier', 'warehouse', 'grn', 'createdBy', 'items.product']);
        
        return response()->json($purchase);
    }

    public function receive(Request $request, Purchase $purchase)
    {
        if ($purchase->status !== 'draft') {
            return response()->json(['message' => 'Only draft purchases can be received'], 422);
        }

        // Update stock and create ledger entries
        foreach ($purchase->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $purchase->warehouse_id)
                ->first();
            
            if ($stock) {
                $oldQty = $stock->quantity;
                $newQty = $oldQty + $item->quantity;
                $oldValue = $stock->total_value;
                $newValue = $oldValue + ($item->quantity * $item->unit_price);
                $avgCost = $newValue / $newQty;

                $stock->update([
                    'quantity' => $newQty,
                    'average_cost' => $avgCost,
                    'total_value' => $newValue,
                ]);
            } else {
                Stock::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchase->warehouse_id,
                    'quantity' => $item->quantity,
                    'average_cost' => $item->unit_price,
                    'total_value' => $item->quantity * $item->unit_price,
                ]);
            }

            // Create stock ledger entry
            StockLedger::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $purchase->warehouse_id,
                'type' => 'in',
                'reference_type' => 'purchase',
                'reference_id' => $purchase->id,
                'reference_number' => $purchase->invoice_number,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_price,
                'total_cost' => $item->total,
                'balance_after' => $stock ? $stock->quantity + $item->quantity : $item->quantity,
                'created_by' => auth()->id(),
                'transaction_date' => $purchase->invoice_date,
            ]);
        }

        $purchase->update([
            'status' => 'pending',
        ]);

        $purchase->load(['supplier', 'warehouse', 'grn', 'createdBy', 'items.product']);
        
        return response()->json($purchase);
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status !== 'draft') {
            return response()->json(['message' => 'Only draft purchases can be deleted'], 422);
        }

        $purchase->delete();
        
        return response()->json(['message' => 'Purchase deleted successfully']);
    }
}

