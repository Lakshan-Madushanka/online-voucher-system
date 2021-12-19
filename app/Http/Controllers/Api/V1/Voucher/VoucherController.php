<?php

namespace App\Http\Controllers\Api\V1\Voucher;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteManyByIdRequest;
use App\Http\Requests\SearchPriceRangeRequest;
use App\Http\Requests\SearchStatusRequest;
use App\Http\Requests\Voucher\VoucherStoreRequest;
use App\Http\Requests\Voucher\VoucherUpdateRequest;
use App\Models\Voucher;
use App\Repository\VoucherRepositoryInterface;
use App\Services\FileService;
use App\Services\VoucherService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class VoucherController extends Controller
{
    use ApiResponser;

    private $voucherService;
    private $voucherRepo;
    private $fileService;

    public function __construct(
        VoucherService $voucherService,
        VoucherRepositoryInterface $voucherRepo,
        FileService $fileService
    ) {
        $this->voucherService = $voucherService;
        $this->voucherRepo    = $voucherRepo;
        $this->fileService    = $fileService;

        $this->middleware('auth:sanctum')->except(['show', 'index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allVouchers
            = $this->voucherRepo->getByStatus(['status1' => Voucher::STATUS['APPROVED']]);

        return $this->showMany($allVouchers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(VoucherStoreRequest $request)
    {
        $response = Gate::inspect('isAdministrative');
        abort_if(!$response->allowed(), 403, $response->message());

        $inputs = $request->validated();
        $image  = $inputs['image'];

        $inputs['status'] = $this->voucherService->setStatusAttribute($request);

        $path = $this->fileService
            ->storeFileWithOriginalName($request, $image, Voucher::storagePath);


        $inputs['image'] = $path;

        $voucher = $this->voucherRepo->create($inputs);

        return $this->showOne($voucher, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Voucher $voucher)
    {
        $this->authorize('show', $voucher);

        return $this->showOne($voucher);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(VoucherUpdateRequest $request, Voucher $voucher)
    {
        $response = Gate::inspect('isAdministrative');
        abort_if(!$response->allowed(), 403, $response->message());

        $inputs = $request->validated();

        $inputs['status'] = $this->voucherService
            ->setStatusAttribute($request, $voucher);

        if (array_key_exists('image', $inputs)) {
            $image = $inputs['image'];
            if ($image) {
                $inputs['image'] = $this->fileService
                    ->updateFileWithOriginalName($voucher, $request, $image,
                        $this->fileService, Voucher::storagePath);
            }
        }

        $newVoucher = $voucher->fill($inputs);

        if (!$newVoucher->isDirty('price')
            && !$newVoucher->isDirty('terms')
            && !$newVoucher->isDirty('validity')
            && !$newVoucher->isDirty('status')
            && !isset($image)) {
            return $this->showError(['error' => __('validation.isDirty')],
                Response::HTTP_BAD_REQUEST, Response::$statusTexts[400]);
        }

        $updatedVoucher = $this->voucherRepo->update($newVoucher);

        return $this->showOne($updatedVoucher, 201,
            Response::$statusTexts[201]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Voucher $voucher)
    {
        return $this->deleteById($voucher, $this->voucherRepo);

    }

    public function destroyMany(DeleteManyByIdRequest $request)
    {
       return $this->deleteMany($request, $this->voucherRepo);
    }

    public function searchStatus(SearchStatusRequest $request)
    {
        $response = Gate::inspect('isAdministrative');
        abort_if(!$response->allowed(), 403, $response->message());

        $inputs = $request->validated();

        $results = $this->voucherRepo->getByStatus($inputs);

        return $this->showMany($results);
    }

    public function searchPriceRange(SearchPriceRangeRequest $request)
    {
        $response = Gate::inspect('isAdministrative');
        abort_if(!$response->allowed(), 403, $response->message());

        $inputs  = $request->validated();

        $results = $this->voucherRepo->getByPriceRange($inputs['min'],
            $inputs['max']);

        return $this->showMany($results);
    }

}
