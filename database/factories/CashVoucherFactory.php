<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CashVoucherFactory extends Factory
{
    private $value = 0;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $this->value += 500;

        return [
            'price' => $this->value,
        ];
    }
}
