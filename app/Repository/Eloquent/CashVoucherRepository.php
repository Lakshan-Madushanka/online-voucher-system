<?php


namespace App\Repository\Eloquent;


use App\Repository\CashVoucherRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CashVoucherRepository extends VoucherRepository implements CashVoucherRepositoryInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}