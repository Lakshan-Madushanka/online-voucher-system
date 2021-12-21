<?php

namespace App\Http\Requests\VoucherPurchase;

use App\Repository\CashVoucherRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Repository\VoucherRepositoryInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseStoreRequest extends FormRequest
{
    private $userRepo;
    private $voucherRepo;
    private $cashVoucherRepo;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(
        UserRepositoryInterface $useRepo,
        VoucherRepositoryInterface $voucherRepo,
        CashVoucherRepositoryInterface $cashVoucherRepo
    ) {
        $this->userRepo    = $useRepo;
        $this->voucherRepo = $voucherRepo;
        $this->cashVoucherRepo = $cashVoucherRepo;

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
            'regularVouchers'               => [
                'required_without:cashVouchers', 'array',
            ],
            'regularVouchers.*.voucher_Id'  => [
                'required', 'integer', function ($attribute, $value, $fail) {
                    $voucher = $this->voucherRepo->findWithoutFail($value);
                    if (!$voucher) {
                        $fail("Invalid $attribute");
                    }
                },
            ],
            'regularVouchers.*.user_Id'     => [
                'required', 'integer', Rule::in([Auth::id()]),
            ],
            'regularVouchers.*.quantity'    => ['required', 'integer'],
            'regularVouchers.*.type'        => [
                'required', Rule::in(['direct', 'presented']),
            ],
            'regularVouchers.*.receiver_id' => [
                'required_if:regularVouchers.*.type,presented',
                'integer',
                'different:regularVouchers.*.user_Id',
                function ($attribute, $value, $fail) {
                    $user = $this->userRepo->findWithoutFail($value);
                    if (!$user) {
                        $fail("Invalid $attribute");
                    }
                },
            ],

            'cashVouchers'               => [
                'required_without:regularVouchers', 'array',
            ],
            'cashVouchers.*.voucher_Id'  => ['required', 'integer', function ($attribute, $value, $fail) {
                $voucher = $this->cashVoucherRepo->findWithoutFail($value);
                if (!$voucher) {
                    $fail("Invalid $attribute");
                }
            },],
            'cashVouchers.*.user_Id'     => [
                'required', 'integer', Rule::in(Auth::id()),
            ],
            'cashVouchers.*.quantity'    => ['required', 'integer'],
            'cashVouchers.*.type'        => [
                'required', Rule::in(['direct', 'presented']),
            ],
            'cashVouchers.*.receiver_id' => [
                'integer', 'required_if:cashVouchers.*.type,presented',
                function ($attribute, $value, $fail) {
                    $user = $this->userRepo->findWithoutFail($value);
                    if (!$user) {
                        $fail("Invalid $attribute");
                    }
                },
            ],

        ];
    }

    public function messages()
    {
        return [
            'regularVouchers.required_without' => __('validation.voucherRequiredWithout'),
            'cashVouchers.required_without'    => __('validation.voucherRequiredWithout'),

        ];
    }
}
