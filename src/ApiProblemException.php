<?php

namespace eLife\ApiProblem;

use Crell\ApiProblem\ApiProblem;
use Exception;
use Throwable;

class ApiProblemException extends Exception
{
    private $apiProblem;

    public function __construct(ApiProblem $apiProblem, Throwable $previous = null)
    {
        parent::__construct($apiProblem->getTitle(), 0, $previous);

        $this->apiProblem = $apiProblem;
    }

    final public function getApiProblem() : ApiProblem
    {
        return $this->apiProblem;
    }
}
