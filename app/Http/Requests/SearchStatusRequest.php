<?php

namespace App\Http\Requests;

use App\Models\Voucher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SearchStatusRequest extends FormRequest
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
            'status1' => [
                'string',
                Rule::in(Voucher::STATUS['APPROVED']),
                'required_without_all:status2,status3,status4',

            ],
            'status2' => [
                'string',
                Rule::in(Voucher::STATUS['PENDING']),
                'required_without_all:status1,status3,status4',
            ],
            'status3' => [
                'string',
                Rule::in(Voucher::STATUS['REJECTED']),
                'required_without_all:status1,status2,status4',

            ],
            'status4' => [
                'string',
                Rule::in('all'),
                Rule::in('rejected'),
                'required_without_all:status1,status2,status3',
            ],
        ];
    }
}
