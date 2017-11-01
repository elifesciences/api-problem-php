<?php

namespace eLife\ApiProblem;

use Crell\ApiProblem\ApiProblem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiProblemFactory
{
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
            $extra = [
                'exception' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
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
