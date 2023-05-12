<?php

namespace App\RestApi;

class EntityResponseGetLastArchive
{
    public string $lastArchiveFileName;

    public function __construct(
        public EntityResponse $entityResponse,
    ) {
        $this->lastArchiveFileName = $this->entityResponse->payload;
    }
}
