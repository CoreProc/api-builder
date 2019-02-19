<?php

namespace CoreProc\ApiBuilder\Http\Responses;

use EllipseSynergie\ApiResponse\Laravel\Response;

class ApiResponse extends Response
{
    const CODE_EXCEPTION = 'GEN-EXCEPTION';
    const CODE_SUCCESS = 'GEN-SUCCESS';

    /**
     * Generates a Response with a 422 HTTP header and a given message.
     *
     * @param string $message
     * @param $errors
     * @param array $headers
     * @return mixed
     */
    public function errorValidation($errors = null, $message = 'The given data was invalid', array $headers = [])
    {
        return $this->setStatusCode(422)->withArray([
            'error' => [
                'code' => self::CODE_UNPROCESSABLE,
                'http_code' => $this->statusCode,
                'message' => $message,
                'errors' => $errors,
            ],
        ],
            $headers
        );
    }

    public function withSuccess($message = 'Successful request', $statusCode = 200, array $headers = [])
    {
        return $this->setStatusCode($statusCode)
            ->withArray([
                'success' => [
                    'code' => self::CODE_SUCCESS,
                    'http_code' => $statusCode,
                    'message' => $message,
                ],
            ],
                $headers
            );
    }
}
