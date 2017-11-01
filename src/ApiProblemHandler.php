<?php

namespace eLife\ApiProblem;

use Crell\ApiProblem\ApiProblem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiProblemHandler
{
    public function handle(ApiProblem $apiProblem) : Response
    {
        $json = $apiProblem->asArray();
        unset($json['status']);

        return new JsonResponse(
            $json,
            $apiProblem->getStatus() ? $apiProblem->getStatus() : Response::HTTP_INTERNAL_SERVER_ERROR,
            [
                'Cache-Control' => 'no-cache, private',
                'Content-Type' => 'application/problem+json',
            ]
        );
    }
}
