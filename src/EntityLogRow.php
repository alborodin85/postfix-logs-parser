<?php

namespace App;

class EntityLogRow
{
    public function __construct(
        public int $id,
        public string $dateTime,
        public string $hostName,
        public string $module,
        public int $procId,
        public string $queueId,
        public string $errorLevel,
        public string $rowText,
    ) {
        //
    }
}
