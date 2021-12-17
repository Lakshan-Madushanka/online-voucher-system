<?php

namespace App\Rules;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Route;

class VoucherValidityRule implements Rule
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
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $voucher = Route::current()->parameter('voucher');

        if ($voucher) {
            if(empty($value)) {
                return true;
            }
            return $this->updateValidate($voucher, $value);
        } else {
            return $this->storeValidate($value);
        }
    }

    public function storeValidate($value)
    {
        $sixMonthPeriod = now()->subMinute()->diffInMonths($value);
        $yearsOfperiod  = now()->subMinute()->diffInYears($value);

        if ($sixMonthPeriod === 6 || $yearsOfperiod === 1
            || $yearsOfperiod === 2) {
            return true;
        }

        return false;
    }

    public function updateValidate(Voucher $voucher, $value)
    {
        $createdDate = $voucher->created_at;

        $monthDiff = $createdDate->diffInMonths($value);
        $yearDiff  = $createdDate->diffInYears($value);

        if ($monthDiff === 6 || $yearDiff === 1 || $yearDiff === 2) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.voucherValidity');
    }
}
