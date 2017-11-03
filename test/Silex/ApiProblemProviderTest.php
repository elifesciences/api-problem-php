<?php

namespace test\eLife\ApiProblem\Silex;

use Crell\ApiProblem\ApiProblem;
use eLife\ApiProblem\ApiProblemFactory;
use eLife\ApiProblem\ApiProblemHandler;
use eLife\ApiProblem\Silex\ApiProblemProvider;
use Silex\Application;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ApiProblemProviderTest extends WebTestCase
{
    /** @var Application */
    protected $app;

    /**
     * @test
     */
    public function it_creates_services()
    {
        $this->assertArrayHasKey('api_problem.factory', $this->app);
        $this->assertInstanceOf(ApiProblemFactory::class, $this->app['api_problem.factory']);
        $this->assertArrayHasKey('api_problem.handler', $this->app);
        $this->assertInstanceOf(ApiProblemHandler::class, $this->app['api_problem.handler']);
    }

    /**
     * @test
     */
    public function it_handles_errors()
    {
        $client = $this->createClient();

        $client->request('GET', '/error');
        $response = $client->getResponse();

        $expected = [
            'type' => 'about:blank',
            'title' => 'problem',
        ];

        $this->assertSame(418, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $response->getContent());
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

    final public function createApplication() : Application
    {
        $app = new Application();
        $app->register(new ApiProblemProvider());

        $app['debug'] = true;
        unset($app['exception_handler']);

        $app->get('/error', function () {
            throw new HttpException(Response::HTTP_I_AM_A_TEAPOT, 'problem');
        });

        $app->get('/view', function () {
            $apiProblem = new ApiProblem('problem');
            $apiProblem->setStatus(Response::HTTP_I_AM_A_TEAPOT);

            return $apiProblem;
        });

        $app->boot();
        $app->flush();

        return $app;
    }
}
