<?php


namespace App\Repository;


interface VoucherRepositoryInterface extends EloquentRepositoryInterface
{
    public function getByStatus(array $status);
    public function getByPriceRange(int $min, int $max);

}