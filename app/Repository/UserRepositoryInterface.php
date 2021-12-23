<?php


namespace App\Repository;


interface UserRepositoryInterface extends EloquentRepositoryInterface
{
    public function regularVoucherpurchases($key, array $columns);
    public function cashVoucherpurchases($key,  array $columns);

}