<?php

namespace Buzz\EssentialsSdk;

use Buzz\EssentialsSdk\Exceptions\ErrorException;
use Buzz\EssentialsSdk\Exceptions\ResponseException;
use Buzz\EssentialsSdk\Exceptions\ServerException;
use Buzz\EssentialsSdk\Exceptions\UnauthorizedException;
use Buzz\EssentialsSdk\Service\DefaultRequestData;
use Buzz\EssentialsSdk\Service\SupportsHeaders;
use GuzzleHttp\Client as Guzzle;

/**
 * Class Service
 */
class Service extends Config
{
    use DefaultRequestData,
        SupportsHeaders;

    /**
     * @return mixed
     */
    final public function get($method, ?array $request = null)
    {
        return $this->call('get', $method, $request);
    }

    /**
     * @return mixed
     *
     * @throws ErrorException
     * @throws ResponseException
     * @throws ServerException
     * @throws UnauthorizedException
     */
    final protected function call($verb, $method, ?array $request = null)
    {
        if (is_null($request)) {
            $request = [];
        }

        $this->prepareHeaders();

        $request = $this->prepareRequest($request);

        $client = new GuzzleClient(new Guzzle([
            'proxy'  => static::getProxy(),
            'verify' => static::verify(),
        ]));

        return $client->request(
            $verb,
            $this->getUrl($method),
            $request,
            $this->getHeaders()
        );
    }

    /**
     * @param  string  $method
     * @return string
     */
    protected function getUrl($method)
    {
        $endpoint = sprintf(
            '%s://%s/',
            static::getProtocol(),
            static::getEndpoint(),
            $method
        );

        return $endpoint;
    }

    /**
     * @return mixed
     */
    final public function post($method, ?array $request = null)
    {
        return $this->call('post', $method, $request);
    }

    /**
     * @return mixed
     */
    final public function put($method, ?array $request = null)
    {
        return $this->call('put', $method, $request);
    }

    /**
     * @return mixed
     */
    final public function patch($method, ?array $request = null)
    {
        return $this->call('patch', $method, $request);
    }

    /**
     * @return mixed
     */
    final public function delete($method, ?array $request = null)
    {
        return $this->call('delete', $method, $request);
    }
}
