<?php


namespace App\Services;


use App\Models\Voucher;
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

        if($voucher) {
            $status = $voucher->status;
        }

        if ($request->filled('status')) {
            if($request->status !== 'pending') {
                if (UserService::checkRole('isSuperAdmin')) {
                    $status = $request->status;
                }else {
                    throw new AuthorizationException(__('auth.insufficientGrant'));
                }
            }
        }

        return $status;
    }
}