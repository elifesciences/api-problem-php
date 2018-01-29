<?php

namespace test\eLife\ApiProblem\Silex;

use Crell\ApiProblem\ApiProblem;
use eLife\ApiProblem\ApiProblemFactory;
use eLife\ApiProblem\ApiProblemHandler;
use eLife\ApiProblem\Silex\ApiProblemProvider;
use Exception;
use Silex\Application;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Traversable;

final class ApiProblemProviderTest extends WebTestCase
{
    /** @var Application */
    protected $app;
    /** @var Exception|null */
    private $exception;

    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function it_creates_services(string $id, string $class)
    {
        $this->assertArrayHasKey($id, $this->app);
        $this->assertInstanceOf($class, $this->app[$id]);
    }

    public function serviceProvider() : Traversable
    {
        $services = [
            'api_problem.factory' => ApiProblemFactory::class,
            'api_problem.handler' => ApiProblemHandler::class,
        ];

        foreach ($services as $id => $type) {
            yield $id => [$id, $type];
        }
    }

    /**
     * @test
     * @dataProvider exceptionProvider
     */
    public function it_handles_http_exceptions(bool $includeExceptionDetails)
    {
        if (!$includeExceptionDetails) {
            $this->createApplication(false);
        }

        $client = $this->createClient();

        $client->request('GET', '/error');
        $response = $client->getResponse();

        if ($includeExceptionDetails) {
            $expected = [
                'exception' => [
                    'message' => 'problem',
                    'class' => get_class($this->exception),
                    'file' => $this->exception->getFile(),
                    'line' => $this->exception->getLine(),
                    'stacktrace' => $this->exception->getTrace(),
                ],
                'title' => 'problem',
                'type' => 'about:blank',
            ];
        } else {
            $expected = [
                'type' => 'about:blank',
                'title' => 'problem',
            ];
        }

        $this->assertSame(418, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $response->getContent());
    }

    /**
     * @test
     * @dataProvider exceptionProvider
     */
    public function it_handles_exceptions(bool $includeExceptionDetails)
    {
        if (!$includeExceptionDetails) {
            $this->createApplication(false);
        }

        $client = $this->createClient();

        $client->request('GET', '/exception');
        $response = $client->getResponse();

        if ($includeExceptionDetails) {
            $expected = [
                'type' => 'about:blank',
                'title' => 'Error',
                'exception' => [
                    'message' => 'an exception',
                    'class' => get_class($this->exception),
                    'file' => $this->exception->getFile(),
                    'line' => $this->exception->getLine(),
                    'stacktrace' => $this->exception->getTrace(),
                ],
            ];
        } else {
            $expected = [
                'type' => 'about:blank',
                'title' => 'Error',
            ];
        }

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $response->getContent());
    }

    public function exceptionProvider() : Traversable
    {
        yield 'Exception with details' => [true];
        yield 'Exception without details' => [false];
    }

    /**
     * @test
     */
    public function it_turns_api_problems_into_responses()
    {
        $client = $this->createClient();

        $client->request('GET', '/view');
        $response = $client->getResponse();

        $expected = [
            'type' => 'about:blank',
            'title' => 'problem',
        ];

        $this->assertSame(418, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $response->getContent());
    }

    public function createApplication(bool $includeExceptionDetails = true) : Application
    {
        $app = new Application();
        $app->register(new ApiProblemProvider());

        $app['debug'] = true;
        unset($app['exception_handler']);

        if (!$includeExceptionDetails) {
            $app['api_problem.factory.include_exception_details'] = false;
        }

        $app->get('/error', function () {
            throw $this->exception = new HttpException(Response::HTTP_I_AM_A_TEAPOT, 'problem');
        });

        $app->get('/exception', function () {
            throw $this->exception = new Exception('an exception');
        });

        $app->get('/view', function () {
            $apiProblem = new ApiProblem('problem');
            $apiProblem->setStatus(Response::HTTP_I_AM_A_TEAPOT);

            return $apiProblem;
        });

        $app->boot();
        $app->flush();

        return $this->app = $app;
    }
}
