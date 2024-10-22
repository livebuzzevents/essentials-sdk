<?php namespace Buzz\EssentialsSdk;

use Buzz\EssentialsSdk\Contracts\Client;
use Buzz\EssentialsSdk\Exceptions\ErrorException;
use Buzz\EssentialsSdk\Exceptions\ResponseException;
use Buzz\EssentialsSdk\Exceptions\ServerException;
use Buzz\EssentialsSdk\Exceptions\ServiceUnavailableException;
use Buzz\EssentialsSdk\Exceptions\UnauthorizedException;
use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;

/**
 * Class Client
 *
 * @package Buzz\EssentialsSdk
 */
class GuzzleClient implements Client
{
    /**
     * @var Guzzle
     */
    protected $guzzle;

    /**
     * @param Guzzle $guzzle
     */
    public function __construct(Guzzle $guzzle = null)
    {
        $this->guzzle = $guzzle ?: new Guzzle();
    }

    /**
     * @param       $verb
     * @param       $url
     * @param array $request
     * @param array $headers
     *
     * @return mixed
     * @throws ErrorException
     * @throws ResponseException
     * @throws ServerException
     * @throws ServiceUnavailableException
     * @throws UnauthorizedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($verb, $url, array $request = [], array $headers = [])
    {
        try {
            $response = $this->guzzle->request(
                $verb,
                $url,
                $this->buildRequest($verb, $request, $headers)
            );

            $contents = $response->getBody()->getContents();

            if (empty($contents)) {
                return $contents;
            }

            $decodedResponse = json_decode($contents, true);

            if ((json_last_error() == JSON_ERROR_NONE)) {
                return $decodedResponse;
            } else {
                throw new ResponseException('Unexpected error! Response not valid JSON:'.$contents);
            }
        } catch (GuzzleClientException $e) {
            $response = $e->getResponse();
            $contents = $response->getBody()->getContents();

            if ($response->getStatusCode() === 400 || $response->getStatusCode() === 422) {
                $responseContent = json_decode($contents, true);

                throw new ErrorException(
                    $responseContent['error'],
                    !empty($responseContent['code']) ? $responseContent['code'] : 0
                );
            } elseif ($response->getStatusCode() === 401) {
                abort(401);
            } elseif ($response->getStatusCode() === 404) {
                abort(404);
            } else {
                throw new ResponseException('Unexpected error! Invalid response code!', 0, $e);
            }
        } catch (GuzzleServerException $e) {
            if ($e->getCode() == 503) {
                abort(503);
            }

            throw new ServerException('Unexpected error! Please contact us for more information!', $e->getCode(), $e);
        } catch (Exception $e) {
            throw new ResponseException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Build on top of the request and sends the required data for rest authorization
     *
     * @param array $request
     * @param array $headers
     *
     * @return array
     */
    protected function buildRequest($verb, array $request = [], array $headers = [])
    {
        $result = ['headers' => array_merge($headers, ['Accept' => 'application/json'])];

        if (in_array($verb, ['post', 'put'])) {
            foreach ($request as $key => $value) {
                if (is_resource($value)) {
                    $request[$key] = base64_encode(file_get_contents($request[$key]));
                }
            }

            $result['json'] = $request;
        } elseif (in_array($verb, ['get', 'delete'])) {
            $result['query'] = $request;
        }

        return $result;
    }
}
