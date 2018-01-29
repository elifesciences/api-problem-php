<?php

namespace eLife\ApiProblem\Silex;

use Crell\ApiProblem\ApiProblem;
use eLife\ApiProblem\ApiProblemFactory;
use eLife\ApiProblem\ApiProblemHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Throwable;

final class ApiProblemProvider implements BootableProviderInterface, ServiceProviderInterface
{
    public function boot(Application $app)
    {
        $app->view(function (ApiProblem $apiProblem) use ($app) {
            return $app['api_problem.handler']->handle($apiProblem);
        });

        $app->error(function (Throwable $e) use ($app) {
            $apiProblem = $app['api_problem.factory']->create($e);

            return $app['api_problem.handler']->handle($apiProblem);
        }, -100);
    }

    public function register(Container $app)
    {
        $app['api_problem.factory.include_exception_details'] = function () use ($app) {
            return $app['debug'];
        };

        $app['api_problem.factory'] = function () use ($app) {
            return new ApiProblemFactory($app['api_problem.factory.include_exception_details']);
        };

        $app['api_problem.handler'] = function () {
            return new ApiProblemHandler();
        };
    }
}
