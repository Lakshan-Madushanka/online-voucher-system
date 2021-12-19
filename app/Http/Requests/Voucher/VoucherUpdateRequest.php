<?php

namespace App\Http\Requests\Voucher;

use App\Rules\UploadedFileNameLength;
use App\Rules\VoucherValidityRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VoucherUpdateRequest extends FormRequest
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
            'image' => [
                'sometimes', 'required', 'image', 'max:3072',
                new UploadedFileNameLength(),
                // new UploadedFileNameExists(Voucher::storagePath),
            ],
            'price' => ['sometimes', 'required', 'numeric', 'max: 1000000'],
            'terms' => ['sometimes', 'required', 'string', 'max: 1000'],
            'validity' => [
                'sometimes', 'required', 'date', new VoucherValidityRule(),
            ],
        ];
    }
}
