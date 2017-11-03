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
use Traversable;

final class ApiProblemProviderTest extends WebTestCase
{
    /** @var Application */
    protected $app;

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
