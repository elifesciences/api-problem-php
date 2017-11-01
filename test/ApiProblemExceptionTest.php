<?php

namespace test\eLife\ApiProblem;

use Crell\ApiProblem\ApiProblem;
use eLife\ApiProblem\ApiProblemException;
use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;

final class ApiProblemExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_exception()
    {
        $exception = new ApiProblemException(new ApiProblem());

        $this->assertInstanceOf(Throwable::class, $exception);
    }

    /**
     * @test
     */
    public function it_has_a_message()
    {
        $exception = new ApiProblemException(new ApiProblem('foo'));

        $this->assertSame('foo', $exception->getMessage());
    }

    /**
     * @test
     */
    public function it_has_an_api_problem()
    {
        $exception = new ApiProblemException($apiProblem = new ApiProblem());

        $this->assertSame($apiProblem, $exception->getApiProblem());
    }

    /**
     * @test
     */
    public function it_can_have_a_previous_exception()
    {
        $with = new ApiProblemException(new ApiProblem(), $previous = new Exception());
        $withOut = new ApiProblemException(new ApiProblem());

        $this->assertSame($previous, $with->getPrevious());
        $this->assertNull($withOut->getPrevious());
    }
}
