<?php

namespace App\RestApi;

class EntityResponse
{
    public function __construct(
        public mixed $payload,
        public int $errorCode,
        public int $httpCode,
        public string $errorModule,
        public string $errorText,
    ) {
        //
    }
}
