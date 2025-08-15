<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Amirhossein',
            'phone_number' => '09356014932',
            'password' => '123456',
            'is_admin' => True
        ]);
        User::factory()->count(5)->create();
        Category::factory()->create([
            'name' => 'Shoes',
        ]);
        $category = Category::where('name', 'Shoes')->first();
        Product::create([
            'name' => 'sneaker 1',
            'description' => 'A cool sneaker for everyday wear.',
            'price' => 150,
            'stock' => 10,
            'category_id' => $category ? $category->id : null,
        ]);
        $this->call([
        ProductSeeder::class,
        ]);
    }
}
