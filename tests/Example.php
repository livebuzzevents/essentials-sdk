<?php

namespace Tests;

use Buzz\EssentialsSdk\SdkObject;

/**
 * Class Example
 *
 * @property string $name
 * @property int $age
 * @property array $interests
 * @property-read int $orders
 * @property-write string $note
 * @property Example[] $parent
 */
class Example extends SdkObject {}
