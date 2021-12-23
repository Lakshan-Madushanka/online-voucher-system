<?php


namespace App\Repository\Eloquent;


use App\Models\User;
use App\Repository\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function regularVoucherpurchases($key, array $columns = ['*'])
    {
        $results = DB::table('users')
            ->join('purchases', 'users.id', '=', 'purchases.user_id')
            ->join('vouchers', 'purchases.voucher_id', '=', 'vouchers.id')
            ->join('purchase_details as details', 'details.purchase_id', '=',
                'purchases.id')
            ->where('users.id', $key)
            ->orderByDesc('purchases.id')
            ->select($columns)
            ->get();

        return $results;
    }

    public function cashVoucherpurchases($key, array $columns = ['*'])
    {
        $results = User::query()
            ->join('purchases', 'users.id', '=', 'purchases.user_id')
            ->join('cash_vouchers', 'purchases.cash_voucher_id', '=', 'cash_vouchers.id')
            ->join('purchase_details as details', 'details.purchase_id', '=',
                'purchases.id')
            ->where('users.id', $key)
            ->orderByDesc('purchases.id')
            ->select($columns)
            ->get();

        return $results;
    }
}