<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserRegularVoucherResuorce;
use App\Models\User;
use App\Repository\UserRepositoryInterface;
use App\Services\VoucherService;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class UserRegularVoucherController extends Controller
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
            'vouchers.id', 'vouchers.image', 'vouchers.price',
            'vouchers.validity', 'purchases.created_at',
            'vouchers.terms', 'vouchers.id', 'details.quantity',
            'details.type', 'details.receiver_id',
        ];

        $results = $this->userRepo->regularVoucherpurchases($id, $columns);

        if ($results->count() > 0) {
            $user = [
                'id'    => $results[0]->userId,
                'name'  => $results[0]->name,
                'email' => $results[0]->email,
            ];

            $vouchers = collect(UserRegularVoucherResuorce::collection($results));

            $briefs  = $this->voucherService->prepareBriefDetails($vouchers);

            $results  = collect(['user' => $user, 'vouchers' => $vouchers, 'overall_purchases' => $briefs]);
        };

        return $this->showMany($results);
    }
}
