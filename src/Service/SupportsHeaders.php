<?php

namespace Buzz\EssentialsSdk\Service;

trait SupportsHeaders
{
    /**
     * @var array
     */
    protected $headers;

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers ?: [];
    }

    /**
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    public function setHeader(string $header, string $value)
    {
        $this->headers[$header] = $value;
    }

    public function setCustomHeader(string $header, string $value)
    {
        $this->headers[static::$custom_header_prefix.$header] = $value;
    }

    /**
     * Adds all headers
     */
    protected function prepareHeaders()
    {
        $this->setCustomHeader('Version', static::getVersion());
        $this->setHeader('Accept-Language', static::getLanguage());
        $this->setHeader('Authorization', 'Bearer '.static::getApiKey());
    }
}
