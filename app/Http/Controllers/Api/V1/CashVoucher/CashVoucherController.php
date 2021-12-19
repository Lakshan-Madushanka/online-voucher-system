<?php

namespace App\Http\Controllers\Api\V1\CashVoucher;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashVoucher\CashVoucherStoreRequest;
use App\Http\Requests\CashVoucher\CashVoucherUpdateRequest;
use App\Http\Requests\DeleteManyByIdRequest;
use App\Http\Requests\SearchPriceRangeRequest;
use App\Http\Requests\SearchStatusRequest;
use App\Models\CashVoucher;
use App\Repository\CashVoucherRepositoryInterface;
use App\Repository\EloquentRepositoryInterface;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CashVoucherController extends Controller
{
    use ApiResponser;

    private $cashVoucherRepo;

    public function __construct(CashVoucherRepositoryInterface $cashVoucherRepo)
    {
        $this->cashVoucherRepo = $cashVoucherRepo;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vouchers = $this->cashVoucherRepo->all();

        return  $this->showMany($vouchers);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CashVoucherStoreRequest $request)
    {
        $response = Gate::inspect('isAdministrative');
        abort_unless($response->allowed(), Response::HTTP_FORBIDDEN,
            $response->message());

        $inputs = $request->validated();

        $cashVoucher = $this->cashVoucherRepo->create($inputs);

        $this->showOne($cashVoucher, Response::HTTP_CREATED,
            Response::$statusTexts[Response::HTTP_CREATED]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CashVoucher  $cashVoucher
     *
     * @return \Illuminate\Http\Response
     */
    public function show(CashVoucher $cashVoucher)
    {
        return $this->showOne($cashVoucher);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CashVoucher  $cashVoucher
     *
     * @return \Illuminate\Http\Response
     */
    public function update(
        CashVoucherUpdateRequest $request,
        CashVoucher $cashVoucher
    ) {
        $response = Gate::inspect('isAdministrative');
        abort_unless($response->allowed(), Response::HTTP_FORBIDDEN,
            $response->message());

        $inputs = $request->validated();

        $newVoucher = $cashVoucher->fill($inputs);

        if ($newVoucher->isDirty()) {
            return $this->showError(['error' => __('validation.isDirty')],
                Response::HTTP_BAD_REQUEST, Response::$statusTexts[400]);
        }

        $updatedVoucher = $this->cashVoucherRepo->update();

        abort_unless($updatedVoucher->wasChanged(), 500,
            Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]);

        return $this->showOne($updatedVoucher, 201,
            Response::$statusTexts[201]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CashVoucher  $cashVoucher
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(CashVoucher $cashVoucher)
    {
        $response = Gate::inspect('isAdministrative');
        abort_unless($response->allowed(), Response::HTTP_FORBIDDEN,
            $response->message());

        if($this->deleteById($cashVoucher, $this->cashVoucherRepo)){
            return $this->showMessage([], Response::HTTP_OK,
                Response::$statusTexts[Response::HTTP_OK]);
        };
    }

    public function destroyMany(DeleteManyByIdRequest $request)
    {
        return $this->deleteMany($request, $this->cashVoucherRepo);

    }

    public function searchPriceRange(SearchPriceRangeRequest $request)
    {
        $response = Gate::inspect('isAdministrative');
        abort_if(!$response->allowed(), 403, $response->message());

        $inputs  = $request->validated();

        $results = $this->cashVoucherRepo->getByPriceRange($inputs['min'],
            $inputs['max']);

        return $this->showMany($results);

    }
}
