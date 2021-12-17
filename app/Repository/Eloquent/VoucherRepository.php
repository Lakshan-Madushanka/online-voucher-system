<?php


namespace App\Repository\Eloquent;


use App\Repository\VoucherRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class VoucherRepository extends BaseRepository
    implements VoucherRepositoryInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function getByStatus(array $status)
    {
        $statusValues = array_values($status);
        $statusKeys   = array_keys($status);

        if(in_array('state4', $statusKeys) && in_array('all', $statusValues)) {
            return $this->model->all();
        }

        $query = $this->model->where('status', $statusValues[0]);

        unset($status[$statusKeys[0]]);

        if (count($status) === 0) {
            return $query->get();
        }

        foreach ($status as $key => $value) {
            $query->orWhere('status', $value);
        }

        return $query->get();
    }

    public function getByPriceRange(int $min, int $max)
    {
        $results = $this->model->whereBetween('price', [$min, $max])->get();

        return $results;
    }
}