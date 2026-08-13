<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory corresponding to the model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'shop_id' => Shop::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . time(),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(10000, 500000),
            'cost_price' => fake()->numberBetween(5000, 250000),
            'stock' => fake()->numberBetween(10, 100),
            'status' => ProductStatus::APPROVED,
            'weight' => fake()->numberBetween(100, 5000),
        ];
    }
}
