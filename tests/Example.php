<?php

namespace Tests;

use Buzz\EssentialsSdk\SdkObject;

/**
 * Class Example
 *
 * @property string $name
 * @property integer $age
 * @property array $interests
 * @property-read int $orders
 * @property-write string $note
 * @property \Tests\Example[] $parent
 */
class Example extends SdkObject
{
}
