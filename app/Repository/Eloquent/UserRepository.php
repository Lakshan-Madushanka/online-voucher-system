<?php


namespace App\Repository\Eloquent;


use App\Repository\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}