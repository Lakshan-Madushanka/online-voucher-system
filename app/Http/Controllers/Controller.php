<?php

namespace App\Http\Controllers;

use App\Repository\EloquentRepositoryInterface;
use App\Traits\ApiResponser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponser;

    protected function deleteMany(
        Request $request,
        EloquentRepositoryInterface $repo
    ) {
        $response = Gate::inspect('isAdministrative');
        abort_if(!$response->allowed(), 403, $response->message());

        $ids = $request->validated();

        if ($repo->deleteMany($ids)) {
            return $this->showMessage([], Response::HTTP_OK,
                Response::$statusTexts[Response::HTTP_OK]);
        }
    }

    protected function deleteById(
        Model $model,
        EloquentRepositoryInterface $repo
    ) {
        $response = Gate::inspect('isAdministrative');
        abort_if(!$response->allowed(), 403, $response->message());

        if ($repo->delete($model)) {
            return $this->showMessage([], Response::HTTP_OK,
                Response::$statusTexts[Response::HTTP_OK]);
        };
    }
}
