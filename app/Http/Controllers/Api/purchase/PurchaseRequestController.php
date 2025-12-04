<?php

namespace App\Http\Controllers\Api\purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['warehouse', 'requestedBy', 'approvedBy']);

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by PR number or notes
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'request_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = ['id', 'pr_number', 'request_date', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'request_date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $requests = $query->paginate($request->get('per_page', 10));
        
        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'request_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Generate PR number
        $prNumber = 'PR-' . strtoupper(Str::random(6));
        while (PurchaseRequest::where('pr_number', $prNumber)->exists()) {
            $prNumber = 'PR-' . strtoupper(Str::random(6));
        }

        $purchaseRequest = PurchaseRequest::create([
            'pr_number' => $prNumber,
            'warehouse_id' => $validated['warehouse_id'],
            'request_date' => $validated['request_date'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'requested_by' => auth()->id(),
        ]);

        $purchaseRequest->load(['warehouse', 'requestedBy']);
        
        return response()->json($purchaseRequest, 201);
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['warehouse', 'requestedBy', 'approvedBy', 'purchaseOrders']);
        return response()->json($purchaseRequest);
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be updated'], 422);
        }

        $validated = $request->validate([
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'request_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ]);

        $purchaseRequest->update($validated);
        $purchaseRequest->load(['warehouse', 'requestedBy', 'approvedBy']);
        
        return response()->json($purchaseRequest);
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be approved'], 422);
        }

        $purchaseRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $purchaseRequest->load(['warehouse', 'requestedBy', 'approvedBy']);
        
        return response()->json($purchaseRequest);
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be rejected'], 422);
        }

        $purchaseRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $purchaseRequest->load(['warehouse', 'requestedBy', 'approvedBy']);
        
        return response()->json($purchaseRequest);
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be deleted'], 422);
        }

        $purchaseRequest->delete();
        
        return response()->json(['message' => 'Purchase request deleted successfully']);
    }
}

