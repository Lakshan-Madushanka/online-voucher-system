<?php


namespace App\Repository\Eloquent;


use App\Repository\PurchaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class PurchaseRepository extends BaseRepository implements PurchaseRepositoryInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}