<?php

namespace App\Http\Requests\CashVoucher;

use App\Rules\CashVoucherPriceRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CashVoucherStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
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
                Rule::unique('cash_vouchers'),
            ],
        ];
    }
}
