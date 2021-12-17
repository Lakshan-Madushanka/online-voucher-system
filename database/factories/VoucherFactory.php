<?php

namespace Database\Factories;

use App\Models\Voucher;
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
            'validity' => $this->faker->randomElement([
                now()->addMonth(6),
                now()->addYear(1),
                now()->addYears(2),
            ]),
            'status' => $this->faker->randomElement(Voucher::STATUS),
        ];
    }
}
