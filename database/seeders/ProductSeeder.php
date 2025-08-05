<?php

namespace Database\Seeders;
use App\Models\Category;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::factory()->count(5)->create();

        foreach ($categories as $category) {
            Product::factory()->count(10)->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
