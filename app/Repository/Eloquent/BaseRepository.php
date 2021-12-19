<?php


namespace App\Repository\Eloquent;


use App\Repository\EloquentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements EloquentRepositoryInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find(
        $key,
        array $columns = ['*'],
        array $relations = []
    ): ?Model {
        $model = $this->model->select($columns)->with($relations)->findOrFail($key);

        return $model;
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model): Model
    {
        $model->save();

        return $model->fresh();
    }

    public function all(array $columns = ['*'], array $relations = [])
    {
        return $this->model->select($columns)->with($relations)->get();
    }

    public function directAll(array $columns = ['*'])
    {
       return  $this->model->toBase()->get($columns);
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function deleteMany(array $ids): int
    {
        return $this->model->destroy($ids);
    }
}