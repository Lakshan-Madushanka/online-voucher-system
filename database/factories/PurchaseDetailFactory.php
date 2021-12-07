<?php

namespace Database\Factories;

use App\Models\PurchaseDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'quantity' => $this->faker->randomNumber(2),
            'type' => $this->faker->randomElement(PurchaseDetail::type),
            'receiver_id' => User::inRandomOrder()->first()->id
        ];
    }
}
