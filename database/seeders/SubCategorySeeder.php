<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Electronics Subcategories
        $electronics = Category::where('slug', 'electronics')->first();
        if ($electronics) {
            SubCategory::updateOrCreate(
                ['slug' => 'laptops'],
                [
                    'name' => 'Laptops',
                    'category_id' => $electronics->id,
                    'order' => 1,
                    'description' => 'Laptop computers and accessories',
                    'is_active' => true,
                ]
            );
            
            SubCategory::updateOrCreate(
                ['slug' => 'accessories'],
                [
                    'name' => 'Accessories',
                    'category_id' => $electronics->id,
                    'order' => 2,
                    'description' => 'Computer accessories and peripherals',
                    'is_active' => true,
                ]
            );

            SubCategory::updateOrCreate(
                ['slug' => 'mobile-phones'],
                [
                    'name' => 'Mobile Phones',
                    'category_id' => $electronics->id,
                    'order' => 3,
                    'description' => 'Smartphones and mobile devices',
                    'is_active' => true,
                ]
            );

            SubCategory::updateOrCreate(
                ['slug' => 'tablets'],
                [
                    'name' => 'Tablets',
                    'category_id' => $electronics->id,
                    'order' => 4,
                    'description' => 'Tablets and e-readers',
                    'is_active' => true,
                ]
            );
        }
        
        // Clothing Subcategories
        $clothing = Category::where('slug', 'clothing')->first();
        if ($clothing) {
            SubCategory::updateOrCreate(
                ['slug' => 'mens-clothing'],
                [
                    'name' => 'Men\'s Clothing',
                    'category_id' => $clothing->id,
                    'order' => 1,
                    'description' => 'Clothing for men',
                    'is_active' => true,
                ]
            );
            
            SubCategory::updateOrCreate(
                ['slug' => 'womens-clothing'],
                [
                    'name' => 'Women\'s Clothing',
                    'category_id' => $clothing->id,
                    'order' => 2,
                    'description' => 'Clothing for women',
                    'is_active' => true,
                ]
            );

            SubCategory::updateOrCreate(
                ['slug' => 'kids-clothing'],
                [
                    'name' => 'Kids Clothing',
                    'category_id' => $clothing->id,
                    'order' => 3,
                    'description' => 'Clothing for children',
                    'is_active' => true,
                ]
            );
        }

        // Food & Beverages Subcategories
        $foodBeverages = Category::where('slug', 'food-beverages')->first();
        if ($foodBeverages) {
            SubCategory::updateOrCreate(
                ['slug' => 'beverages'],
                [
                    'name' => 'Beverages',
                    'category_id' => $foodBeverages->id,
                    'order' => 1,
                    'description' => 'Drinks and beverages',
                    'is_active' => true,
                ]
            );

            SubCategory::updateOrCreate(
                ['slug' => 'snacks'],
                [
                    'name' => 'Snacks',
                    'category_id' => $foodBeverages->id,
                    'order' => 2,
                    'description' => 'Snacks and quick bites',
                    'is_active' => true,
                ]
            );

            SubCategory::updateOrCreate(
                ['slug' => 'packaged-food'],
                [
                    'name' => 'Packaged Food',
                    'category_id' => $foodBeverages->id,
                    'order' => 3,
                    'description' => 'Pre-packaged food items',
                    'is_active' => true,
                ]
            );
        }

        // Office Supplies Subcategories
        $officeSupplies = Category::where('slug', 'office-supplies')->first();
        if ($officeSupplies) {
            SubCategory::updateOrCreate(
                ['slug' => 'stationery'],
                [
                    'name' => 'Stationery',
                    'category_id' => $officeSupplies->id,
                    'order' => 1,
                    'description' => 'Pens, pencils, and writing supplies',
                    'is_active' => true,
                ]
            );

            SubCategory::updateOrCreate(
                ['slug' => 'paper-products'],
                [
                    'name' => 'Paper Products',
                    'category_id' => $officeSupplies->id,
                    'order' => 2,
                    'description' => 'Paper, notebooks, and documents',
                    'is_active' => true,
                ]
            );
        }

        // Furniture Subcategories
        $furniture = Category::where('slug', 'furniture')->first();
        if ($furniture) {
            SubCategory::updateOrCreate(
                ['slug' => 'office-furniture'],
                [
                    'name' => 'Office Furniture',
                    'category_id' => $furniture->id,
                    'order' => 1,
                    'description' => 'Desks, chairs, and office furniture',
                    'is_active' => true,
                ]
            );

            SubCategory::updateOrCreate(
                ['slug' => 'home-furniture'],
                [
                    'name' => 'Home Furniture',
                    'category_id' => $furniture->id,
                    'order' => 2,
                    'description' => 'Furniture for home use',
                    'is_active' => true,
                ]
            );
        }

        // Tools & Hardware Subcategories
        $tools = Category::where('slug', 'tools-hardware')->first();
        if ($tools) {
            SubCategory::updateOrCreate(
                ['slug' => 'hand-tools'],
                [
                    'name' => 'Hand Tools',
                    'category_id' => $tools->id,
                    'order' => 1,
                    'description' => 'Manual hand tools',
                    'is_active' => true,
                ]
            );

            SubCategory::updateOrCreate(
                ['slug' => 'power-tools'],
                [
                    'name' => 'Power Tools',
                    'category_id' => $tools->id,
                    'order' => 2,
                    'description' => 'Electric and battery-powered tools',
                    'is_active' => true,
                ]
            );

            SubCategory::updateOrCreate(
                ['slug' => 'hardware'],
                [
                    'name' => 'Hardware',
                    'category_id' => $tools->id,
                    'order' => 3,
                    'description' => 'Screws, bolts, and hardware items',
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Subcategories seeded successfully!');
    }
}

