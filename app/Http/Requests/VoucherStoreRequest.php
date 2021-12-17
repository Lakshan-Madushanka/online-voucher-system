<?php

namespace App\Http\Requests;

use App\Models\Voucher;
use App\Rules\UploadedFileNameLength;
use App\Rules\UploadedFileNameExists;
use App\Rules\VoucherValidityRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VoucherStoreRequest extends FormRequest
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
                'required', 'image', 'max:3072', new UploadedFileNameLength(),
                new UploadedFileNameExists(Voucher::storagePath),
            ],
              'price' => ['required', 'numeric', 'max: 1000000'],
              'terms' => ['required', 'string', 'max: 1000'],
              'validity' => ['required', 'date', new VoucherValidityRule()]
        ];
    }
}
