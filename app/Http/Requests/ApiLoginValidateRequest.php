<?php

namespace App\Http\Requests;

use App\Rules\ApiSecretValidateRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class ApiLoginValidateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', Password::defaults()],
            'app_id' => ['required','max:50', 'exists:allowed_apps,app_id'],
            'secret' => ['required','max:1000', new ApiSecretValidateRule()]

        ];
    }

}
