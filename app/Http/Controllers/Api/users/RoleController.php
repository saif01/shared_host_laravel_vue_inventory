<?php

namespace App\Http\Controllers\Api\users;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->active);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'order');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = ['id', 'name', 'slug', 'is_active', 'order', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'order';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        $query->orderBy($sortBy, $sortDirection);
        
        if ($sortBy !== 'name' && $sortBy !== 'order') {
            $query->orderBy('name', 'asc');
        }

        // Paginate results
        $perPage = $request->get('per_page', 10);
        $roles = $query->with('permissions')->paginate($perPage);
        
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'slug' => 'nullable|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'integer',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Generate slug from name if not provided
        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            
            // Ensure slug is unique by appending number if needed
            $counter = 1;
            while (Role::where('slug', $slug)->exists() && $counter < 100) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $role = Role::create($validated);

        // Attach permissions if provided
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        $role->load('permissions');
        
        return response()->json($role, 201);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        // For system roles, only allow permission updates
        if ($role->is_system) {
            // Check if only permissions are being updated
            $hasOtherFields = $request->has('name') || $request->has('slug') || 
                             $request->has('description') || $request->has('is_active') || 
                             $request->has('order');
            
            if ($hasOtherFields) {
                return response()->json([
                    'message' => 'System roles cannot have their name, slug, description, status, or order modified. Only permissions can be updated.'
                ], 403);
            }
            
            // Only update permissions for system roles
            if ($request->has('permissions')) {
                $validated = $request->validate([
                    'permissions' => 'required|array',
                    'permissions.*' => 'exists:permissions,id',
                ]);
                
                $role->permissions()->sync($validated['permissions']);
                $role->load('permissions');
                
                return response()->json($role);
            }
            
            return response()->json($role);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'integer',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Handle slug: if provided and empty, generate from name; if provided and not empty, ensure unique
        if (isset($validated['slug']) && empty($validated['slug'])) {
            // Slug was provided but empty - generate from name
            if (isset($validated['name'])) {
                $baseSlug = Str::slug($validated['name']);
            } else {
                $baseSlug = Str::slug($role->name);
            }
            $slug = $baseSlug;
            
            // Ensure slug is unique by appending number if needed
            $counter = 1;
            while (Role::where('slug', $slug)->where('id', '!=', $role->id)->exists() && $counter < 100) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        } elseif (isset($validated['slug']) && !empty($validated['slug'])) {
            // If slug is provided and not empty, ensure it's unique
            $slug = $validated['slug'];
            $originalSlug = $slug;
            $counter = 1;
            while (Role::where('slug', $slug)->where('id', '!=', $role->id)->exists() && $counter < 100) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        } elseif (isset($validated['name']) && !isset($validated['slug'])) {
            // Name changed but slug not provided - regenerate slug only if name changed significantly
            // For update, keep existing slug unless explicitly changed
        }

        $role->update($validated);

        // Update permissions if provided
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        $role->load('permissions');
        
        return response()->json($role);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent deleting system roles
        if ($role->is_system) {
            return response()->json(['message' => 'System roles cannot be deleted'], 403);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Cannot delete role with assigned users'], 403);
        }

        $role->delete();
        
        return response()->json(['message' => 'Role deleted successfully']);
    }

    // Get all permissions grouped
    public function permissions()
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();
        $grouped = $permissions->groupBy('group');
        
        return response()->json($grouped);
    }

    // Sync role permissions
    public function syncPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        // Allow permission updates for all roles, including system roles
        // (System role properties like name/slug are protected in the update method)

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($validated['permissions']);
        $role->load('permissions');

        return response()->json($role);
    }
}
