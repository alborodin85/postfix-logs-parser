<?php

namespace App\RestApi;

class ServiceAddRecords
{
    public function __construct(
        private readonly ServiceRestApiRequest $serviceRestApiRequest,
        private readonly string $endPoint,
    ) {
        //
    }

    public function addRecords(array $mailsArray, int $chunkSize): void
    {
        $chunkedMailsArray = array_chunk($mailsArray, $chunkSize);
        foreach ($chunkedMailsArray as $chunk) {
            $entityRequestAddRecords = new \App\RestApi\EntityRequestAddRecords($chunk);
            $this->serviceRestApiRequest->post($this->endPoint, $entityRequestAddRecords->toArFields());
        }
    }
}
