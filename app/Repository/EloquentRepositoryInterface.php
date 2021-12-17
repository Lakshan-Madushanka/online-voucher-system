<?php


namespace App\Repository;


use Illuminate\Database\Eloquent\Model;

interface EloquentRepositoryInterface
{
    public function all(
        array $columns = ['*'],
        array $relations = []
    );

    public function directAll(array $columns = ['*']);

    public function find(
        $key,
        array $columns = ['*'],
        array $relations = []
    ): ?Model;

    public function create(array $data): Model;

    public function update(Model $model): Model;
}