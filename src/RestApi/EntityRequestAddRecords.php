<?php

namespace App\RestApi;

class EntityRequestAddRecords
{
    public function __construct(
        public array $records,
    ) {
        //
    }

    public function toArFields(): array
    {
        return ['records' => $this->records];
    }
}
