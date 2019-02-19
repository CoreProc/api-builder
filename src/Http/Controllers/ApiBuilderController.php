<?php

namespace CoreProc\ApiBuilder\Http\Controllers;

use CoreProc\ApiBuilder\Builders\HttpQueryToSqlQueryBuilder;
use CoreProc\ApiBuilder\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Manager;

abstract class ApiBuilderController
{
    protected $response;

    /**
     * The underlying model resource instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public static $model;

    public static $transformer;

    protected $allowedParams = [];

    protected $dates = [];

    public function __construct()
    {
        $fractal = new Manager();

        if (isset($_GET['include'])) {
            $fractal->parseIncludes($_GET['include']);
        }

        $this->response = new ApiResponse($fractal);
    }

    /**
     * Get the underlying model instance for the resource.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model()
    {
        return static::$model;
    }

    /**
     * Get a fresh instance of the model represented by the resource.
     *
     * @return mixed
     */
    public static function newModel()
    {
        return new static::$model;
    }

    protected function transformer()
    {
        return static::$transformer;
    }

    protected static function newTransformer()
    {
        return new static::$transformer;
    }

    private static function indexQuery($request, $query)
    {
        return $query;
    }

    protected function getAllowedParams()
    {
        return $this->allowedParams;
    }

    private function getDates()
    {
        return $this->dates;
    }

    protected function creationRulesFor()
    {
        return [];
    }

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    public static function authorizedToViewAny(Request $request)
    {
        return true;
    }

    /**
     * Determine if the current user can create new resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return true;
    }

    public function index(Request $request)
    {
        if (! static::authorizedToViewAny($request)) {
            return $this->response->errorUnauthorized();
        }

        // Build the query
        $query = self::indexQuery($request, app($this->model())->query());

        $query = (new HttpQueryToSqlQueryBuilder($query,
            $request->all(),
            $this->getAllowedParams(),
            $this->getDates()))->query;

        return $this->response->withPaginator($query->paginate($request->get('per_page', 15)),
            static::newTransformer(), null, $this->addToMeta());
    }

    public function store(Request $request)
    {
        if (! static::authorizedToCreate($request)) {
            return $this->response->errorUnauthorized();
        }

        $validator = Validator::make($request->all(), $this->creationRulesFor());

        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();

            // Try to get the first error message
            $message = 'The given data was invalid';
            if (! empty(reset($errors)[0])) {
                $message = reset($errors)[0];
            }

            return $this->response->errorValidation($errors, $message);
        }

        $newModel = static::newModel()->create($request->all());

        return $this->response->withItem($newModel, static::newTransformer());
    }

    /**
     * Override this method to add meta variables in the response from index.
     *
     * @return array
     */
    public function addToMeta()
    {
        return [];
    }
}
