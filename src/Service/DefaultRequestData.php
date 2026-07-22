<?php

namespace Buzz\EssentialsSdk\Service;

trait DefaultRequestData
{
    /**
     * @var array
     */
    protected $default_request = [];

    /**
     * @return $this
     */
    public function setDefaultRequest($key, $value)
    {
        $this->default_request[$key] = $value;

        return $this;
    }

    /**
     * Combines request data with default request data
     *
     *
     * @return array
     */
    protected function prepareRequest(array $request = [])
    {
        return $request + $this->default_request;
    }
}
