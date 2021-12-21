<?php


namespace App\Repository\Eloquent;


use App\Repository\PurchaseDetailRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetailRepository extends BaseRepository implements PurchaseDetailRepositoryInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}