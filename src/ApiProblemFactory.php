<?php

namespace eLife\ApiProblem;

use Crell\ApiProblem\ApiProblem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiProblemFactory
{
    private $includeExceptionDetails;

    public function __construct(bool $includeExceptionDetails = true)
    {
        $this->includeExceptionDetails = $includeExceptionDetails;
    }

    public function create(Throwable $e) : ApiProblem
    {
        if ($e instanceof ApiProblemException) {
            return $e->getApiProblem();
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $e->getMessage();
        } else {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = 'Error';
        }

        if ($this->includeExceptionDetails) {
            $extra['exception'] = [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'stacktrace' => $e->getTrace(),
            ];
        }

        $apiProblem = new ApiProblem($message);
        $apiProblem->setStatus($status);
        foreach ($extra ?? [] as $key => $value) {
            $apiProblem[$key] = $value;
        }

        return $apiProblem;
    }
}
