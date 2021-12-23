<?php


namespace App\Services;


use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Nette\Schema\ValidationException;

class VoucherService
{
    public function setStatusAttribute(
        Request $request,
        Voucher $voucher = null
    ) {
        $status = Voucher::STATUS['PENDING'];

        if ($voucher) {
            $status = $voucher->status;
        }

        if ($request->filled('status')) {
            if ($request->status !== 'pending') {
                if (UserService::checkRole('isSuperAdmin')) {
                    $status = $request->status;
                } else {
                    throw new AuthorizationException(__('auth.insufficientGrant'));
                }
            }
        }

        return $status;
    }

    public function prepareBriefDetails($vouchers): array
    {
        $voucherBriefs = [];
        $voucherids = $vouchers->pluck('id')->unique()->sort();

        $voucherids->each(function ($id) use (&$voucherBriefs) {
            array_push($voucherBriefs, [
                'voucher_id'        => $id,
                'no_of_purchases'   => 0,
                'cost'              => 0,
                'last_purchased_at' => null,
            ]);
        });

        $vouchers->each(function ($voucher) use (&$voucherBriefs, $voucherids) {
            $length = count($voucherBriefs);
            for ($i = 0; $i < $length; $i++) {
                if ($voucherBriefs[$i]['voucher_id'] === $voucher['id']) {
                    $voucherBriefs[$i]['no_of_purchases'] += $voucher['quantity'];
                    $lastPurchasedAt = $voucherBriefs[$i]['last_purchased_at'];
                    $voucherBriefs[$i]['cost'] += $voucher['quantity'] * $voucher['price'];

                    if (!$lastPurchasedAt) {
                        $voucherBriefs[$i]['last_purchased_at']
                            = $voucher['purchased_at'];
                    } else {
                        if (Carbon::parse($voucher['purchased_at'])
                            ->greaterThan($lastPurchasedAt)) {
                            $voucherBriefs[$i]['last_purchased_at']
                                = $voucher['purchased_at'];
                        }
                    }
                }
            }
        });

        $totalPurchases = 0;
        $totalCost = 0;
        $lastPurchasedAt = null;
        $lastPurchased = null;

        foreach ($voucherBriefs as $brief) {
            $totalPurchases += $brief['no_of_purchases'];
            $totalCost += $brief['cost'];

            if (!$lastPurchasedAt) {
                $lastPurchasedAt = $brief['last_purchased_at'];
                $lastPurchased = $brief['voucher_id'];
            } else {
                if (Carbon::parse($brief['last_purchased_at'])
                    ->greaterThan($lastPurchasedAt)) {
                    $lastPurchasedAt = $brief['last_purchased_at'];
                    $lastPurchased = $brief['voucher_id'];
                }
            }
        }


        $overall = [
            'total_purchases'           => $totalPurchases,
            'last_purchased_at'         => $lastPurchasedAt,
            'last_purchased_voucher_id' => $lastPurchased,
            'total_cost'                => number_format($totalCost,2, '.', ','),
        ];

        $length = count($voucherBriefs);
        for ($i = 0; $i < $length; $i++) {
            $cost = $voucherBriefs[$i]['cost'];
            $voucherBriefs[$i]['cost'] = number_format($cost,2, '.', ',');
        };

        return ['brief' => $voucherBriefs, 'overall' => $overall];
    }
}