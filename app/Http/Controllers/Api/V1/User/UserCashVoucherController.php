<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCashVoucherResuorce;
use App\Http\Resources\UserRegularVoucherResuorce;
use App\Models\User;
use App\Repository\UserRepositoryInterface;
use App\Services\VoucherService;
use App\Traits\ApiResponser;

class UserCashVoucherController extends Controller
{
    use ApiResponser;

    private $userRepo;
    private $voucherService;

    public function __construct(UserRepositoryInterface $userRepo, VoucherService $voucherService)
    {
        $this->userRepo = $userRepo;
        $this->voucherService = $voucherService;
    }

    public function index($id)
    {
        $this->authorize('show', $this->userRepo->find($id));

        $columns = [
            'users.id as userId', 'users.name', 'users.email',
            'cash_vouchers.id', 'cash_vouchers.value',
            'details.quantity',
            'details.type', 'details.receiver_id',
        ];

        $results = $this->userRepo->cashVoucherpurchases($id, $columns);

        if($results->count() > 0) {
            $user = [
                'id'    => $results[0]->userId,
                'name'  => $results[0]->name,
                'email' => $results[0]->email,
            ];

            $vouchers     = UserCashVoucherResuorce::collection($results);

            $briefs  = $this->voucherService->prepareBriefDetails($vouchers);

            $results = collect(['user' => $user, 'vouchers' => $vouchers, 'overall_purchases' => $briefs]);
        }

        return $this->showMany($results);
    }

    public function prepareBriefDetails()
    {

    }
}
