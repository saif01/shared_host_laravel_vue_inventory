<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Parent category relationship
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Subcategories relationship
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    // Get all descendants recursively
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    // Check if category is a parent (has children)
    public function isParent()
    {
        return $this->children()->count() > 0;
    }

    // Get category hierarchy path (breadcrumb)
    public function getHierarchyPath($separator = ' > ')
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode($separator, $path);
    }

    // Scope to get only root categories (no parent)
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope to get only subcategories (has parent)
    public function scopeSubcategories($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
