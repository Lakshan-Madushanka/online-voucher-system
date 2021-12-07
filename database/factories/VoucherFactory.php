<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'image' => $this->faker->imageUrl(),
            'price' => $this->faker->randomFloat(2, 1000, 200000),
            'terms' => $this->faker->text(200),
            'validity' => now()->subMonth(6),
        ];
    }
}
