<?php

namespace App;

class EntityQueueItem
{
    public function __construct(
        public int $id,
        public string $dateTime,
        public string $queueId,
        public string $payload
    ) {
        //
    }
}
