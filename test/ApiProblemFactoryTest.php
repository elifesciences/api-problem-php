<?php

namespace test\eLife\ApiProblem;

use Crell\ApiProblem\ApiProblem;
use eLife\ApiProblem\ApiProblemException;
use eLife\ApiProblem\ApiProblemFactory;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Traversable;

final class ApiProblemFactoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider apiProblemProvider
     */
    public function it_creates_an_api_problem(Throwable $exception, ApiProblem $expected, bool $includeExceptionDetails)
    {
        $factory = new ApiProblemFactory($includeExceptionDetails);

        $this->assertEquals($expected, $factory->create($exception));
    }

    public function apiProblemProvider() : Traversable
    {
        $apiProblem = new ApiProblem('foo');

        yield 'ApiProblemException' => [
            new ApiProblemException($apiProblem),
            $apiProblem,
            true,
        ];

        $exception = new HttpException(Response::HTTP_I_AM_A_TEAPOT, 'message');
        $apiProblem = new ApiProblem('message');
        $apiProblem->setStatus(Response::HTTP_I_AM_A_TEAPOT);
        $apiProblem['exception'] = 'message';
        $apiProblem['stacktrace'] = $exception->getTraceAsString();

        yield 'HttpException with details' => [
            $exception,
            $apiProblem,
            true,
        ];

        $exception = new HttpException(Response::HTTP_I_AM_A_TEAPOT, 'message');
        $apiProblem = new ApiProblem('message');
        $apiProblem->setStatus(Response::HTTP_I_AM_A_TEAPOT);

        yield 'HttpException without details' => [
            $exception,
            $apiProblem,
            false,
        ];

        $exception = new Exception('message');
        $apiProblem = new ApiProblem('Error');
        $apiProblem->setStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $apiProblem['exception'] = 'message';
        $apiProblem['stacktrace'] = $exception->getTraceAsString();

        yield 'Exception with details' => [
            $exception,
            $apiProblem,
            true,
        ];

        $exception = new Exception('message');
        $apiProblem = new ApiProblem('Error');
        $apiProblem->setStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        yield 'Exception without details' => [
            $exception,
            $apiProblem,
            false,
        ];
    }
}
