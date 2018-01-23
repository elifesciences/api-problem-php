<?php

namespace test\eLife\ApiProblem;

use Crell\ApiProblem\ApiProblem;
use eLife\ApiProblem\ApiProblemHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Traversable;
use function GuzzleHttp\Psr7\normalize_header;

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

        $this->assertSame($expectedStatus, $response->getStatusCode());
        $this->assertSame($this->sanitiseHeaders([
            'Cache-Control' => ['no-cache, private'],
            'Content-Type' => ['application/problem+json'],
        ]), $this->sanitiseHeaders($response->headers->all()));
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

    private function sanitiseHeaders(array $headers) : array
    {
        $headers = array_change_key_case($headers, CASE_LOWER);

        unset($headers['date']);

        ksort($headers);

        $headers = array_map(function (array $values) {
            return implode(', ', normalize_header($values));
        }, $headers);

        return $headers;
    }
}
