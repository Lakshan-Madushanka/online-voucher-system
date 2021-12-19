<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashVoucherPriceRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(!is_int($value)) {
            return false;
        }
        return $value%500 === 0 and $value !== 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.cashVoucherPrice');
    }
}
