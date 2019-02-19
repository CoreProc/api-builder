<?php

namespace CoreProc\ApiBuilder\Http\Requests;

use CoreProc\ApiBuilder\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use League\Fractal\Manager;

class ApiFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $response = new ApiResponse(new Manager());
        $errors = $validator->getMessageBag()->toArray();

        // Try to get the first error message
        $message = 'The given data was invalid';
        if (! empty(reset($errors)[0])) {
            $message = reset($errors)[0];
        }

        throw new HttpResponseException($response->errorValidation($errors, $message));
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     */
    protected function failedAuthorization()
    {
        $response = new ApiResponse(new Manager());

        throw new HttpResponseException($response->errorForbidden());
    }
}
