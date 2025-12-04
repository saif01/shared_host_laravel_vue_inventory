<?php

namespace App\Http\Controllers\Api\products;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\MediaPath;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent');

        // Filter by parent (root categories or subcategories)
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'order');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = ['id', 'name', 'slug', 'order', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'order';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        $query->orderBy($sortBy, $sortDirection);

        $categories = $query->paginate($request->get('per_page', 10));

        $categories->getCollection()->transform(function ($category) {
            $category = $this->transformCategoryImage($category);
            $category->hierarchy_path = $category->getHierarchyPath();
            $category->has_children = $category->isParent();
            $category->children_count = $category->children()->count();
            return $category;
        });
        
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Prevent circular reference
        if (isset($validated['parent_id'])) {
            $parent = Category::find($validated['parent_id']);
            if ($parent && $parent->parent_id) {
                // Check if it would create a deep nesting (more than 2 levels)
                $depth = 1;
                $currentParent = $parent;
                while ($currentParent->parent_id && $depth < 10) {
                    $currentParent = $currentParent->parent;
                    $depth++;
                }
                if ($depth >= 2) {
                    return response()->json([
                        'message' => 'Maximum category depth is 2 levels (Parent > Child)'
                    ], 422);
                }
            }
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        if (!empty($validated['image'])) {
            $validated['image'] = MediaPath::normalize($validated['image']);
        }

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        if (!isset($validated['order'])) {
            // Auto-assign order based on existing categories at same level
            $validated['order'] = Category::where('parent_id', $validated['parent_id'] ?? null)->max('order') + 1;
        }

        $category = Category::create($validated);
        $category->load('parent');
        
        $category = $this->transformCategoryImage($category);
        $category->hierarchy_path = $category->getHierarchyPath();
        
        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        $category->load('parent', 'children');
        $category = $this->transformCategoryImage($category);
        $category->hierarchy_path = $category->getHierarchyPath();
        $category->has_children = $category->isParent();
        $category->children_count = $category->children()->count();
        
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id', \Illuminate\Validation\Rule::notIn([$category->id])],
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            // Cannot set parent to itself
            if ($validated['parent_id'] == $category->id) {
                return response()->json([
                    'message' => 'Category cannot be its own parent'
                ], 422);
            }

            // Cannot set parent to one of its children
            $childrenIds = $category->children()->pluck('id')->toArray();
            if (in_array($validated['parent_id'], $childrenIds)) {
                return response()->json([
                    'message' => 'Cannot set parent to one of its subcategories'
                ], 422);
            }

            // Check depth limit
            $parent = Category::find($validated['parent_id']);
            if ($parent && $parent->parent_id) {
                return response()->json([
                    'message' => 'Maximum category depth is 2 levels (Parent > Child)'
                ], 422);
            }
        }

        // Generate slug if not provided but name changed
        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        if (array_key_exists('image', $validated)) {
            $validated['image'] = MediaPath::normalize($validated['image']);
        }

        $category->update($validated);
        $category->load('parent');
        
        $category = $this->transformCategoryImage($category);
        $category->hierarchy_path = $category->getHierarchyPath();
        
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated products'
            ], 422);
        }

        // Check if category has subcategories
        if ($category->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with subcategories. Please delete or reassign subcategories first.'
            ], 422);
        }

        $category->delete();
        
        return response()->json(['message' => 'Category deleted successfully']);
    }

    /**
     * Get hierarchical category tree
     */
    public function tree(Request $request)
    {
        $activeOnly = $request->get('active_only', true);
        
        $query = Category::with('children')->whereNull('parent_id');
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        $categories = $query->orderBy('order')->get();
        
        return response()->json([
            'categories' => $categories->map(function ($category) {
                return $this->buildCategoryTree($category);
            })
        ]);
    }

    /**
     * Get parent categories for dropdown
     */
    public function parents(Request $request)
    {
        $excludeId = $request->get('exclude_id'); // Exclude specific category (for edit)
        
        $query = Category::whereNull('parent_id')->where('is_active', true);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $parents = $query->orderBy('name')->get();
        
        return response()->json([
            'parents' => $parents->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name,
                    'slug' => $category->slug,
                ];
            })
        ]);
    }

    private function buildCategoryTree(Category $category)
    {
        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image' => MediaPath::url($category->image),
            'order' => $category->order,
            'is_active' => $category->is_active,
            'children_count' => $category->children->count(),
        ];
        
        if ($category->children->count() > 0) {
            $data['children'] = $category->children->map(function ($child) {
                return $this->buildCategoryTree($child);
            });
        }
        
        return $data;
    }

    private function transformCategoryImage(Category $category): Category
    {
        $category->image = MediaPath::url($category->image);
        return $category;
    }
}

