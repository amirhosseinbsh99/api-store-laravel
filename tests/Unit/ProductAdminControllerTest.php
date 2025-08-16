<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;


class ProductAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_products()
    {

        $user = User::factory()->create(['is_admin' => true]);
        Sanctum::actingAs($user, ['*']);


        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/admin/products');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'price', 'stock', 'category']
                     ],
                     'links',
                     'meta'
                 ]);
    }

    /** @test */
    public function it_can_create_a_product()
    {
        $category = Category::factory()->create();

        $payload = [
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => $category->id,
            'discount' => 10,
        ];

        $response = $this->postJson('/api/admin/products', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Test Product']);

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    /** @test */
    public function it_can_show_a_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/admin/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $product->id]);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $payload = ['name' => 'Updated Product'];

        $response = $this->putJson("/api/admin/products/{$product->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Product']);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Product']);
    }

    /** @test */
    public function it_can_delete_a_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Product deleted']);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
