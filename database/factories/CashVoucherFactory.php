<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CashVoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => $this->faker->randomFloat(2, 1000, 200000),
        ];
    }
}
