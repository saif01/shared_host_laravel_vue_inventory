<?php

namespace App\Http\Controllers\Api\products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Unit;
use App\Support\MediaPath;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subCategory', 'unit']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by sub-category
        if ($request->has('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by tracking options
        if ($request->has('track_serial')) {
            $query->where('track_serial', $request->track_serial);
        }
        if ($request->has('track_batch')) {
            $query->where('track_batch', $request->track_batch);
        }
        if ($request->has('track_imei')) {
            $query->where('track_imei', $request->track_imei);
        }

        // Search by name, SKU, barcode, brand, manufacturer or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = [
            'id', 'name', 'sku', 'barcode', 'brand', 'manufacturer',
            'cost_price', 'selling_price', 'tax_rate', 'discount_percentage',
            'minimum_stock_level', 'is_active', 'created_at', 'updated_at'
        ];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $products = $query->paginate($request->get('per_page', 10));

        $products->getCollection()->transform(function ($product) {
            return $this->transformProductImage($product);
        });
        
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Basic Info
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products',
            'barcode' => 'nullable|string|max:255|unique:products',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string',
            
            // Additional Info
            'manufacturer' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
            'warranty_period' => 'nullable|integer|min:0',
            
            // Physical Dimensions
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|max:50',
            'dimensions_length' => 'nullable|numeric|min:0',
            'dimensions_width' => 'nullable|numeric|min:0',
            'dimensions_height' => 'nullable|numeric|min:0',
            'dimensions_unit' => 'nullable|string|max:50',
            
            // Image
            'image' => 'nullable|string|max:255',
            
            // Pricing
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_taxable' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            
            // Stock Settings
            'minimum_stock_level' => 'nullable|integer|min:0',
            'low_stock_alert' => 'nullable|boolean',
            'expiry_alert' => 'nullable|boolean',
            'expiry_alert_days' => 'nullable|integer|min:1|max:365',
            
            // Tracking Options
            'track_serial' => 'nullable|boolean',
            'track_batch' => 'nullable|boolean',
            'track_imei' => 'nullable|boolean',
            'has_variations' => 'nullable|boolean',
            
            // Status
            'is_active' => 'nullable|boolean',
        ]);

        // Normalize image path
        if (!empty($validated['image'])) {
            $validated['image'] = MediaPath::normalize($validated['image']);
        }

        // Set defaults for boolean fields
        $booleanFields = [
            'track_serial', 'track_batch', 'track_imei', 'has_variations',
            'is_taxable', 'low_stock_alert', 'expiry_alert', 'is_active'
        ];
        
        foreach ($booleanFields as $field) {
            if (!isset($validated[$field])) {
                $validated[$field] = in_array($field, ['is_taxable', 'low_stock_alert', 'is_active']) ? true : false;
            }
        }

        // Set numeric defaults
        if (!isset($validated['minimum_stock_level'])) {
            $validated['minimum_stock_level'] = 0;
        }
        if (!isset($validated['tax_rate'])) {
            $validated['tax_rate'] = 0;
        }
        if (!isset($validated['discount_percentage'])) {
            $validated['discount_percentage'] = 0;
        }
        if (!isset($validated['expiry_alert_days'])) {
            $validated['expiry_alert_days'] = 30;
        }

        $product = Product::create($validated);
        $product->load(['category', 'subCategory', 'unit']);
        
        return response()->json($this->transformProductImage($product), 201);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'subCategory', 'unit', 'images', 'barcodes', 'variations']);
        return response()->json($this->transformProductImage($product));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            // Basic Info
            'name' => 'sometimes|required|string|max:255',
            'sku' => ['sometimes', 'required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('products')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('products')->ignore($product->id)],
            'category_id' => 'sometimes|required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'unit_id' => 'sometimes|required|exists:units,id',
            'description' => 'nullable|string',
            
            // Additional Info
            'manufacturer' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'specifications' => 'nullable|string',
            'warranty_period' => 'nullable|integer|min:0',
            
            // Physical Dimensions
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|max:50',
            'dimensions_length' => 'nullable|numeric|min:0',
            'dimensions_width' => 'nullable|numeric|min:0',
            'dimensions_height' => 'nullable|numeric|min:0',
            'dimensions_unit' => 'nullable|string|max:50',
            
            // Image
            'image' => 'nullable|string|max:255',
            
            // Pricing
            'cost_price' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_taxable' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            
            // Stock Settings
            'minimum_stock_level' => 'nullable|integer|min:0',
            'low_stock_alert' => 'nullable|boolean',
            'expiry_alert' => 'nullable|boolean',
            'expiry_alert_days' => 'nullable|integer|min:1|max:365',
            
            // Tracking Options
            'track_serial' => 'nullable|boolean',
            'track_batch' => 'nullable|boolean',
            'track_imei' => 'nullable|boolean',
            'has_variations' => 'nullable|boolean',
            
            // Status
            'is_active' => 'nullable|boolean',
        ]);

        // Normalize image path
        if (array_key_exists('image', $validated)) {
            $validated['image'] = MediaPath::normalize($validated['image']);
        }

        $product->update($validated);
        $product->load(['category', 'subCategory', 'unit']);
        
        return response()->json($this->transformProductImage($product));
    }

    public function destroy(Product $product)
    {
        // Check if product has stock or transactions
        $hasStock = $product->stocks()->where('quantity', '>', 0)->exists();
        $hasTransactions = $product->stockLedgers()->exists();

        if ($hasStock || $hasTransactions) {
            return response()->json([
                'message' => 'Cannot delete product with stock or transaction history'
            ], 422);
        }

        $product->delete();
        
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function categories()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name,
                ];
            });

        return response()->json([
            'categories' => $categories
        ]);
    }

    public function units()
    {
        $units = Unit::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($unit) {
                return [
                    'value' => $unit->id,
                    'label' => $unit->name . ' (' . $unit->code . ')',
                ];
            });

        return response()->json([
            'units' => $units
        ]);
    }

    public function subCategories(Request $request)
    {
        $query = SubCategory::where('is_active', true);
        
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $subCategories = $query->orderBy('order')->orderBy('name')
            ->get()
            ->map(function ($subCategory) {
                return [
                    'value' => $subCategory->id,
                    'label' => $subCategory->name,
                    'category_id' => $subCategory->category_id,
                ];
            });

        return response()->json([
            'sub_categories' => $subCategories
        ]);
    }

    private function transformProductImage(Product $product): Product
    {
        $product->image = MediaPath::url($product->image);
        return $product;
    }
}

