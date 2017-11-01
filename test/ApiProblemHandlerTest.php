<?php

namespace test\eLife\ApiProblem;

use Crell\ApiProblem\ApiProblem;
use eLife\ApiProblem\ApiProblemHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Traversable;

final class ApiProblemHandlerTest extends TestCase
{
    /**
     * @test
     * @dataProvider apiProblemProvider
     */
    public function it_handles_api_problems(ApiProblem $apiProblem, array $expected, int $expectedStatus = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $handler = new ApiProblemHandler();

        $response = $handler->handle($apiProblem);
        $response->headers->remove('Date');

        $this->assertSame($expectedStatus, $response->getStatusCode());
        $this->assertSame([
            'cache-control' => ['no-cache, private'],
            'content-type' => ['application/problem+json'],
        ], $response->headers->all());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $response->getContent());
    }

    public function apiProblemProvider() : Traversable
    {
        yield 'empty' => [
            new ApiProblem(),
            [
                'type' => 'about:blank',
            ],
        ];
        yield 'with title' => [
            new ApiProblem('foo'),
            [
                'type' => 'about:blank',
                'title' => 'foo',
            ],
        ];
        yield 'with type' => [
            new ApiProblem('', 'type'),
            [
                'type' => 'type',
            ],
        ];
        yield 'with status' => [
            (new ApiProblem())->setStatus(Response::HTTP_I_AM_A_TEAPOT),
            [
                'type' => 'about:blank',
            ],
            Response::HTTP_I_AM_A_TEAPOT,
        ];

        $apiProblem = new ApiProblem();
        $apiProblem['foo'] = 'bar';

        yield 'with extra' => [
            $apiProblem,
            [
                'type' => 'about:blank',
                'foo' => 'bar',
            ],
        ];
    }
}
