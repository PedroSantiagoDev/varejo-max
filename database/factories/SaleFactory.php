<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'client_id' => Client::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'sale_date' => $this->faker->dateTimeThisYear(),
        ];
    }
}