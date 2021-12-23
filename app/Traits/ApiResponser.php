<?php


namespace App\Traits;


use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

trait ApiResponser
{
    public function showOne(
        $model,
        int $statusCode = 200,
        string $status = 'ok',
        string $statusMsg = 'Query was successful'
    ) {
        return response()->json(
            [
                'status' => $status,
                'status_message' => $statusMsg,
                'data' => $model,
            ],
            $statusCode);
    }

    public function showMany(
        $collection,
        int $statusCode = 200,
        string $status = 'ok',
        string $statusMsg = 'Query was successful'
    ) {
        if($collection instanceof Collection) {
            if(!$collection->isEmpty()) {
                $collection = $this->sortCollection( $collection);
                $collection = $this->paginateCollection($collection);
            }
        }

        return response()->json(
            [
                'status' => $status,
                'status_message' => $statusMsg,
                'data' => $collection,
            ],
            $statusCode);
    }

    public function showError(
        array $data,
        int $statusCode = 500,
        string $status = 'error',
        string $statusMsg = 'Error occured !'
    ) {
        return response()->json(
            [
                'status' => $status,
                'status_message' => $statusMsg,
                'errors' => $data,
            ],
            $statusCode);
    }

    public function showMessage(
        array $data,
        int $statusCode = 200,
        string $status = 'ok',
        string $statusMsg = 'Query was successful !'
    ) {
        $json =  [
            'status' => $status,
            'status_message' => $statusMsg,
            'data' => $data,
        ];

        if(empty($data)) {
            unset($json['data']);
        }

        return response()->json(
            $json,
            $statusCode);
    }

    public function sortCollection(Collection $collection)
    {

        if(!\request()->filled('sort_by')) {
            return $collection;
        }

       $queryString =  request()->validate([
          'sort_by' => ['array'],
          'sort_by.0' => [Rule::in('desc', 'asc')],
        ]);

        $sortByOrder = 'desc';
        $sortById = 'id';

       if(in_array('sort_by', $queryString)) {
           $values = $queryString['sort_by'];
           print_r($values);
           $sortByOrder = $values[0];
           if(count($values) > 1) {
               $sortById = $values[1];
           }
       }

        $collection = $collection->sortBy([
            [$sortById, $sortByOrder]
        ]);

       return $collection;
    }

    public function paginateCollection(Collection $collection)
    {
        if (\request()->query('paginate') !== 'true') {
            return $collection;
        }
        request()->validate([
            'perPage' => ['integer', 'min:5'],
            'page'    => ['integer', 'min:1'],
        ]);
        $requestedPerPage = \request()->has('perPage');

        $perPage = $requestedPerPage ? \request()->query('perPage') : 10;

        $requestedPage = request()->query('page');

        $currentPage = $requestedPage ? $requestedPage : 1;

        $results = $collection->slice($perPage * ($currentPage - 1),
            $perPage)->values();

        $paginatedData = [
            'current_page'   => $currentPage,
            'num_of_pages'   => ceil($collection->count() / $perPage),
            'num_of_resutls' => $collection->count(),
        ];

        $paginated = collect(['details' => $results, 'meta' => $paginatedData]);

        return $paginated;
    }
}