<?php

namespace Buzz\EssentialsSdk;

use DateTime;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Buzz\EssentialsSdk\Exceptions\ErrorException;
use Buzz\EssentialsSdk\SdkObject\CopiesData;
use Buzz\EssentialsSdk\SdkObject\HandlesDirtyAttributes;
use Buzz\EssentialsSdk\SdkObject\ParsesProperties;
use Buzz\EssentialsSdk\SdkObject\PreparesRequestData;

/**
 * @Annotation
 * Class Base
 *
 * @package Buzz\EssentialsSdk\SdkObject
 */
abstract class SdkObject implements Arrayable, JsonSerializable, Jsonable
{
    use HandlesDirtyAttributes,
        ParsesProperties,
        PreparesRequestData,
        CopiesData;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var array
     */
    protected $expand = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $guard = true;

    /**
     * Guards all protected properties
     */
    public function guard()
    {
        $this->guard = true;
    }

    /**
     * Lets you set guarded properties
     */
    public function unguard()
    {
        $this->guard = false;
    }

    /**
     * Creates new object
     * if data is object or array it clones all fields
     * else it sets the Id
     *
     * @param null|int|array|\StdClass $data
     * @param bool $clean_dirty_attributes
     */
    public function __construct($data = null, $clean_dirty_attributes = false)
    {
        static::parseProperties();

        if ($data) {
            if ($data instanceof SdkObject) {
                $this->copy($data);
            } elseif (is_object($data)) {
                $this->copyFromArray((array)$data);
            } elseif (is_array($data)) {
                $this->copyFromArray($data);
            } else {
                $this->id = $data;
            }

            if ($clean_dirty_attributes) {
                $this->cleanDirtyAttributes();
            }
        }
    }

    /**
     * @param $key
     *
     * @return mixed
     * @throws ErrorException
     */
    public function __get($key)
    {
        if (static::hasProperty($key)) {
            if (!static::isPropertyReadable($key)) {
                throw new ErrorException(sprintf('Property %1$s is write-only!', $key));
            }
        }

        $method = 'get' . str_replace(' ', '', ucwords(str_replace(['_'], ' ', $key))) . 'Property';

        if (method_exists($this, $method)) {
            return $this->{$method}($key);
        }

        return $this->data[$key] ?? null;
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws \Buzz\EssentialsSdk\Exceptions\ErrorException
     */
    public function __set($key, $value)
    {
        if (static::hasProperty($key)) {
            if ($this->guard && !static::isPropertyWritable($key)) {
                throw new ErrorException(sprintf('Property %1$s is read-only!', $key));
            }
        }

        $method = 'set' . str_replace(' ', '', ucwords(str_replace(['_'], ' ', $key))) . 'Property';

        $before = $this->data[$key] ?? null;

        if (!array_key_exists($key, $this->data)) {
            $this->addDirtyAttribute($key);
        }

        if (method_exists($this, $method)) {
            $this->{$method}($value);

            if (!array_key_exists($key, $this->data)) {
                throw new ErrorException($method . ' should set value!');
            }
        } else {
            $this->data[$key] = $value;
        }

        $after = $this->data[$key] ?? null;

        if ($before !== $after) {
            $this->addDirtyAttribute($key);
        }
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @param $key
     */
    public function __unset($key)
    {
        if (array_key_exists($key, $this->data)) {
            $this->addDirtyAttribute($key);
            unset($this->data[$key]);
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @param bool $dirty
     *
     * @return array
     */
    public function toArray($dirty = false): array
    {
        $data = $dirty ? $this->getDirty() : $this->data;

        return $this->convertArrayToJsonSerializable($data);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function convertArrayToJsonSerializable(array $data): array
    {
        $array = [];

        foreach ($data as $name => $value) {
            if (is_iterable($value)) {
                foreach ($value as $key => $single) {
                    if ($single instanceof Arrayable) {
                        $array[$name][$key] = $single->toArray();
                    } else {
                        $array[$name][$key] = $single;
                    }
                }
            } else {
                if ($value instanceof Arrayable) {
                    $array[$name] = $value->toArray();
                } elseif ($value instanceof DateTime) {
                    $array[$name] = $value->format(DateTime::ISO8601);
                } else {
                    $array[$name] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * @param array $expand
     *
     * @return $this
     */
    public function expand(array $expand = [])
    {
        $this->expand = $expand;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpand()
    {
        return $this->expand;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function options(array $options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return \Buzz\EssentialsSdk\Service
     */
    protected function api()
    {
        $service = $this->service();

        if ($this->expand) {
            $service->setDefaultRequest('expand', $this->expand);
        }

        if ($this->options) {
            $service->setDefaultRequest('options', $this->options);
        }

        return $service;
    }

    /**
     * Override this method if using extended service
     *
     * @return \Buzz\EssentialsSdk\Service
     */
    protected function service()
    {
        return new Service();
    }
}
