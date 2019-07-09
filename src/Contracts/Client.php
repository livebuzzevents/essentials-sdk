<?php

namespace Buzz\EssentialsSdk\Contracts;

interface Client
{
    public function request($verb, $url, array $request = []);
}
