<?php

namespace App\Http\Controllers\Api\V1\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherPurchase\PurchaseStoreRequest;
use App\Repository\PurchaseDetailRepositoryInterface;
use App\Repository\PurchaseRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;

class VoucherPurchaseController extends Controller
{
    use ApiResponser;

    private $userRepo;
    private $purchaseRepo;
    private $purchaseDetailRepo;

    public function __construct(
        UserRepositoryInterface $userRepo,
        PurchaseRepositoryInterface $purchaseRepo,
        PurchaseDetailRepositoryInterface $purchaseDetailRepo
    ) {
        $this->middleware('auth:sanctum')->only(['store']);

        $this->userRepo           = $userRepo;
        $this->purchaseRepo       = $purchaseRepo;
        $this->purchaseDetailRepo = $purchaseDetailRepo;
    }

    public function store(PurchaseStoreRequest $request)
    {
        $payload = $request->validated();

        $regularVoucherPurchases = 0;
        $cashVoucherPurchases    = 0;

        if (array_key_exists('regularVouchers', $payload)) {
            $regularVoucherPurchases
                = $this->storeVouchers($payload['regularVouchers']);
        }
        if (array_key_exists('cashVouchers', $payload)) {
            $cashVoucherPurchases
                = $this->storeVouchers($payload['cashVouchers']);
        }

        $totalPurchases = $regularVoucherPurchases + $cashVoucherPurchases;

        return $this->showMessage([
            'total_purchases'           => $totalPurchases,
            'cash_voucher_purchases'    => $cashVoucherPurchases,
            'regular_voucher_purchases' => $regularVoucherPurchases,
        ]);
    }

    public function storeVouchers(array $payload)
    {
        $noOfPurchases = 0;

        foreach ($payload as $value) {
            DB::transaction(function () use ($value, &$noOfPurchases) {
                $this->userRepo->attach($value['user_Id'], 'vouchers',
                    [$value['voucher_Id']]);

                $purchasedDetails = $this->preparePurchaseDetails($value);

                $this->purchaseDetailRepo->create($purchasedDetails);

                $noOfPurchases++;
            });
        }

        return $noOfPurchases;
    }

    public function preparePurchaseDetails(array $value): array
    {
        $type        = $value['type'];
        $receiver_id = null;
        $purchase_id = $this->purchaseRepo->getOldestRecord(['id'])->id;

        if ($type === 'presented') {
            $receiver_id = $value['receiver_id'];
        }

        $purchasedDetails = [
            'quantity'    => $value['quantity'],
            'type'        => $type,
            'receiver_id' => $receiver_id,
            'purchase_id' => $purchase_id,
        ];

        return $purchasedDetails;
    }
}
