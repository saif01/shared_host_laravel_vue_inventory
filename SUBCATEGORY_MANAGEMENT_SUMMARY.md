# Subcategory Management Implementation

## Overview
Implemented comprehensive hierarchical category management with parent-child relationships, supporting up to 2 levels deep (Parent > Child).

---

## 📁 Files Updated

### 1. **Category Model** (`app/Models/Category.php`)

#### New Fields Added:
- `parent_id` - Foreign key to parent category
- `order` - Display order for sorting

#### New Relationships:
```php
- parent() - BelongsTo Category (parent relationship)
- children() - HasMany Category (subcategories)
- descendants() - Recursive children with all descendants
```

#### New Methods:
```php
- isParent() - Check if category has children
- getHierarchyPath($separator) - Get breadcrumb path (e.g., "Electronics > Laptops")
- scopeRoots() - Query only root categories
- scopeSubcategories() - Query only subcategories
```

---

### 2. **Category Controller** (`app/Http/Controllers/Api/products/CategoryController.php`)

#### Enhanced Methods:

**index()** - Updated to:
- Load parent relationship
- Filter by parent_id (root or subcategories)
- Add hierarchy_path, has_children, children_count to response
- Sort by 'order' by default
- Support order field in sorting

**store()** - Updated to:
- Validate parent_id field
- Prevent circular references
- Limit depth to 2 levels maximum
- Auto-assign order based on existing categories at same level
- Validate parent cannot be itself

**update()** - Updated to:
- Validate parent_id changes
- Prevent category from being its own parent
- Prevent setting parent to one of its children (circular reference)
- Enforce 2-level depth limit
- Update order field

**show()** - Updated to:
- Load parent and children relationships
- Return hierarchy_path and children counts

**destroy()** - Updated to:
- Check if category has subcategories before deletion
- Prevent deletion of categories with children

#### New Methods Added:

**tree()** - Get hierarchical category tree
- Returns nested structure with all children
- Optional active_only filter
- Used for displaying category trees

**parents()** - Get parent categories for dropdown
- Returns only root categories
- Optional exclude_id parameter (for editing)
- Used in UI dropdowns

**buildCategoryTree()** - Private helper
- Recursively builds category tree structure
- Includes children counts

---

### 3. **Category Routes** (`routes/api.php`)

Added new routes:
```php
GET /api/v1/categories/tree - Get hierarchical tree
GET /api/v1/categories/parents - Get parent categories list
```

---

### 4. **Admin Categories Component** (`resources/js/components/admin/products/AdminCategories.vue`)

#### Major Enhancements:

**Table Columns Updated:**
1. **Name** - Shows folder icon for parent, tag icon for child
2. **Hierarchy** - Displays full breadcrumb path
3. **Slug** - Category slug
4. **Image** - Category image
5. **Type** - Shows "Parent" or "Subcategory" chip with children count
6. **Order** - Display order number
7. **Status** - Active/Inactive
8. **Actions** - Edit/Delete buttons

**New Filters:**
- **Filter by Type**: Root Categories Only / Subcategories Only
- **Filter by Status**: Active / Inactive
- **Search**: By name, slug

**Dialog Enhancements:**
- **Parent Category Dropdown**: Select parent category or leave empty for root
- **Display Order**: Set custom sort order
- **Visual Indicators**: Show folder icon for parents, warning if has children
- **Validation**: Cannot set circular references

**Features:**
- Auto-load parent categories for dropdown
- Exclude current category from parent dropdown (prevent self-parent)
- Show warning if editing category with children
- Real-time slug generation
- Hierarchical path display in table

---

## 🎯 Features Implemented

### Category Hierarchy
✅ Support for 2-level hierarchy (Parent > Child)
✅ Prevent circular references
✅ Display full breadcrumb path
✅ Visual indicators (folder/tag icons)

### Validation & Safety
✅ Maximum 2-level depth enforcement
✅ Cannot set category as its own parent
✅ Cannot set parent to one of its children
✅ Cannot delete category with children
✅ Cannot delete category with products

### UI/UX
✅ Parent category dropdown
✅ Type filter (root/subcategories)
✅ Hierarchy path display
✅ Children count badges
✅ Visual folder/tag icons
✅ Order management
✅ Warning for categories with children

### API Features
✅ Get category tree (hierarchical)
✅ Get parent categories (for dropdown)
✅ Filter by parent_id
✅ Sort by custom order

---

## 📊 Database Schema

### Categories Table Structure
```sql
- id (bigint, primary key)
- parent_id (bigint, nullable, foreign key to categories.id)
- name (varchar)
- slug (varchar, unique)
- description (text, nullable)
- image (varchar, nullable)
- order (int, default: 0)
- is_active (boolean, default: true)
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, nullable)
```

---

## 🎨 UI Examples

### Category Types Display
```
📁 Electronics (Parent) - 3 children
  └─ 🏷️ Laptops (Subcategory)
  └─ 🏷️ Accessories (Subcategory)
  └─ 🏷️ Mobile Phones (Subcategory)

📁 Clothing (Parent) - 2 children
  └─ 🏷️ Men's Clothing (Subcategory)
  └─ 🏷️ Women's Clothing (Subcategory)

🏷️ Office Supplies (Root Category)
```

### Hierarchy Path Examples
```
Electronics > Laptops
Clothing > Men's Clothing
Office Supplies (root)
```

---

## 🔄 Usage Examples

### Creating a Parent Category
```javascript
POST /api/v1/categories
{
  "name": "Electronics",
  "parent_id": null,
  "description": "Electronic products",
  "order": 1,
  "is_active": true
}
```

### Creating a Subcategory
```javascript
POST /api/v1/categories
{
  "name": "Laptops",
  "parent_id": 1, // Electronics
  "description": "Laptop computers",
  "order": 1,
  "is_active": true
}
```

### Get Category Tree
```javascript
GET /api/v1/categories/tree?active_only=true

Response:
{
  "categories": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "children_count": 2,
      "children": [
        {
          "id": 2,
          "name": "Laptops",
          "slug": "laptops",
          "children_count": 0
        }
      ]
    }
  ]
}
```

### Get Parent Categories (for dropdown)
```javascript
GET /api/v1/categories/parents?exclude_id=5

Response:
{
  "parents": [
    {
      "value": 1,
      "label": "Electronics",
      "slug": "electronics"
    },
    {
      "value": 2,
      "label": "Clothing",
      "slug": "clothing"
    }
  ]
}
```

---

## ✅ Validation Rules

### Creating/Updating Categories
1. **Maximum Depth**: 2 levels only (Parent > Child)
2. **No Circular References**: Category cannot be its own parent
3. **No Child-to-Parent**: Cannot set parent to one of its own children
4. **Unique Slugs**: Auto-generated and guaranteed unique
5. **Deletion Protection**: 
   - Cannot delete if has products
   - Cannot delete if has subcategories

---

## 🚀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/categories` | List all categories with pagination |
| GET | `/api/v1/categories/tree` | Get hierarchical tree structure |
| GET | `/api/v1/categories/parents` | Get parent categories for dropdown |
| GET | `/api/v1/categories/{id}` | Get single category with relationships |
| POST | `/api/v1/categories` | Create new category |
| PUT | `/api/v1/categories/{id}` | Update category |
| DELETE | `/api/v1/categories/{id}` | Delete category |

### Query Parameters

**index()**:
- `parent_id` - Filter by parent (use 'null' for root categories)
- `is_active` - Filter by status (true/false)
- `search` - Search by name, slug, description
- `sort_by` - Sort field (default: 'order')
- `sort_direction` - Sort direction (asc/desc)
- `per_page` - Items per page

**tree()**:
- `active_only` - Show only active categories (default: true)

**parents()**:
- `exclude_id` - Exclude specific category ID

---

## 🎯 Use Cases

### 1. E-Commerce Store
```
Electronics
  ├── Laptops
  ├── Accessories
  └── Mobile Phones

Clothing
  ├── Men's Clothing
  └── Women's Clothing
```

### 2. Multi-Category Inventory
```
Food & Beverages
  ├── Groceries
  └── Drinks

Office Supplies
  └── (no subcategories)
```

### 3. Service-Based Categories
```
Services
  ├── Consulting
  └── Training

Products
  ├── Software
  └── Hardware
```

---

## 🔧 Technical Details

### Preventing Circular References
The system prevents:
1. Category being its own parent
2. Parent being set to one of its children
3. More than 2 levels of nesting

### Order Management
- Auto-assigns order when creating new category
- Orders are relative to the same level (parent siblings)
- Can be manually adjusted via order field

### Performance Optimization
- Eager loading of parent relationship
- Indexed parent_id column
- Efficient queries for tree structure

---

## 📚 Integration with Products

Products can now be assigned to:
- **Category**: Main category (required)
- **Sub-category**: Optional sub-category (new field)

This provides more granular product organization:
```javascript
{
  "name": "MacBook Pro",
  "category_id": 1, // Electronics
  "sub_category_id": 2, // Laptops
}
```

---

## 🎨 Visual Indicators

### Icons
- 📁 `mdi-folder` - Parent category with children
- 🏷️ `mdi-tag-outline` - Subcategory or root with no children

### Chips
- **Parent** (Primary color) - Root category
- **Subcategory** (Info color) - Has parent
- **Order** (Grey) - Display order number
- **Active/Inactive** (Success/Error) - Status

---

## ✅ Testing Checklist

- [x] Create root category
- [x] Create subcategory under parent
- [x] Try to create 3-level hierarchy (should fail)
- [x] Try to set category as its own parent (should fail)
- [x] Try to set parent to one of its children (should fail)
- [x] Edit category and change parent
- [x] Delete category with products (should fail)
- [x] Delete category with children (should fail)
- [x] Get category tree
- [x] Get parent categories for dropdown
- [x] Filter by root/subcategories
- [x] Sort by order
- [x] Display hierarchy path in UI

---

## 📝 Notes

1. **Depth Limit**: System enforces maximum 2 levels (Parent > Child)
2. **Auto Slug**: Slugs are automatically generated from category name
3. **Order Field**: Can be used to customize display order
4. **Soft Deletes**: Categories are soft-deleted for data integrity
5. **Relationships**: Parent-child relationships are bidirectional

---

## 🔜 Future Enhancements

Consider adding:
- [ ] Drag-and-drop reordering
- [ ] Bulk category import
- [ ] Category templates
- [ ] Custom fields per category
- [ ] Category-specific product attributes
- [ ] Analytics per category hierarchy

---

**Last Updated**: December 4, 2025
**Status**: Production Ready
**Compatibility**: Laravel 11 + Vue 3 + Vuetify 3

---

## 🎉 Summary

The subcategory management system is now fully implemented with:
- **Hierarchical structure** (2 levels)
- **Comprehensive validation**
- **User-friendly UI**
- **Complete API**
- **Safety checks** (circular references, depth limit)
- **Visual indicators**

All changes are backward compatible and ready for production use!

