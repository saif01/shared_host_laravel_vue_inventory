<?php

namespace App\Http\Controllers\Api\users;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Get all permissions, optionally grouped by group
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        // Filter by group if provided
        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        // Search by name or slug
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting (only for flat view)
        $sortBy = $request->get('sort_by', 'group');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = ['id', 'name', 'slug', 'group', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'group';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        // Get grouped or flat list
        if ($request->has('grouped') && $request->grouped) {
            // For grouped view, we still return all permissions grouped (no pagination)
            $permissions = $query->withCount('roles')->orderBy('group')->orderBy('name')->get();
            $grouped = $permissions->groupBy('group');
            return response()->json($grouped);
        }

        // Apply sorting for flat view
        $query->orderBy($sortBy, $sortDirection);
        
        if ($sortBy !== 'name' && $sortBy !== 'group') {
            $query->orderBy('group', 'asc')->orderBy('name', 'asc');
        } elseif ($sortBy === 'group') {
            $query->orderBy('name', 'asc');
        }

        // Paginate results for flat view
        $perPage = $request->get('per_page', 10);
        $permissions = $query->withCount('roles')->paginate($perPage);
        
        return response()->json($permissions);
    }

    /**
     * Create a new permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:permissions',
            'group' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Generate slug from name if not provided
        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            
            // Ensure slug is unique by appending number if needed
            $counter = 1;
            while (Permission::where('slug', $slug)->exists() && $counter < 100) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $permission = Permission::create($validated);
        
        return response()->json($permission, 201);
    }

    /**
     * Get a specific permission
     */
    public function show($id)
    {
        $permission = Permission::withCount('roles')->findOrFail($id);
        return response()->json($permission);
    }

    /**
     * Update a permission
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('permissions')->ignore($permission->id)],
            'group' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Handle slug: if provided and empty, generate from name; if provided and not empty, ensure unique
        if (isset($validated['slug']) && empty($validated['slug'])) {
            // Slug was provided but empty - generate from name
            if (isset($validated['name'])) {
                $baseSlug = Str::slug($validated['name']);
            } else {
                $baseSlug = Str::slug($permission->name);
            }
            $slug = $baseSlug;
            
            // Ensure slug is unique by appending number if needed
            $counter = 1;
            while (Permission::where('slug', $slug)->where('id', '!=', $permission->id)->exists() && $counter < 100) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        } elseif (isset($validated['slug']) && !empty($validated['slug'])) {
            // If slug is provided and not empty, ensure it's unique
            $slug = $validated['slug'];
            $originalSlug = $slug;
            $counter = 1;
            while (Permission::where('slug', $slug)->where('id', '!=', $permission->id)->exists() && $counter < 100) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $permission->update($validated);
        
        return response()->json($permission);
    }

    /**
     * Delete a permission
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete permission that is assigned to roles. Please remove it from all roles first.'
            ], 403);
        }

        $permission->delete();
        
        return response()->json(['message' => 'Permission deleted successfully']);
    }

    /**
     * Get all available permission groups with permission counts
     */
    public function groups()
    {
        $groups = Permission::select('group')
            ->selectRaw('COUNT(*) as permissions_count')
            ->groupBy('group')
            ->orderBy('group')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->group,
                    'permissions_count' => $item->permissions_count
                ];
            });
        
        return response()->json($groups);
    }

    /**
     * Rename a permission group
     */
    public function renameGroup(Request $request)
    {
        $validated = $request->validate([
            'old_name' => 'required|string|max:255',
            'new_name' => 'required|string|max:255',
        ]);

        $oldName = $validated['old_name'];
        $newName = $validated['new_name'];

        // Check if new name already exists
        if (Permission::where('group', $newName)->exists() && $oldName !== $newName) {
            return response()->json([
                'message' => 'A group with this name already exists.'
            ], 422);
        }

        // Update all permissions with the old group name
        $updated = Permission::where('group', $oldName)
            ->update(['group' => $newName]);

        return response()->json([
            'message' => 'Group renamed successfully',
            'updated_count' => $updated
        ]);
    }

    /**
     * Delete a permission group (only if it has no permissions)
     */
    public function deleteGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $groupName = $validated['name'];

        // Check if group has any permissions
        $permissionCount = Permission::where('group', $groupName)->count();

        if ($permissionCount > 0) {
            return response()->json([
                'message' => 'Cannot delete group that contains permissions. Please remove or reassign all permissions first.'
            ], 403);
        }

        // Since groups are just strings in the group column, and there are no permissions,
        // there's nothing to delete. Just return success.
        return response()->json([
            'message' => 'Group deleted successfully'
        ]);
    }
}
