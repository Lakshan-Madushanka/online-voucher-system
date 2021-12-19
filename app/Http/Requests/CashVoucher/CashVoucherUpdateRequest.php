<?php

namespace App\Http\Requests\CashVoucher;

use App\Rules\CashVoucherPriceRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashVoucherUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'price' => [
                'required',
                'integer',
                new CashVoucherPriceRule(),
                Rule::unique('cash_vouchers')
            ],
        ];
    }
}
