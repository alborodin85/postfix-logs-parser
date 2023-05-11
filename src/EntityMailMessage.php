<?php

namespace App;

class EntityMailMessage
{
    public function __construct(
        public int $id,
        public string $dateTime,
        public string $queueId,
        public string $from,
        public string $to,
        public string $subject,
        public string $statusText,
        public int $statusCode,
        public string $statusName,
        public string $nonDeliveryNotificationId,
    ) {
        //
    }
}
