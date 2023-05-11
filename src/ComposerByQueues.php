<?php

namespace App;

class ComposerByQueues
{
    /**
     * @param EntityLogRow[] $logRows
     * @return EntityQueueItem[]
     */
    public function buildQueues(array $logRows): array
    {
        $queueRows = [];
        foreach ($logRows as $logRow) {
            if ($logRow->queueId) {
                $queueRows[$logRow->queueId][] = $logRow;
            }
        }

        /** @var EntityLogRow[] $queueSet */
        $queueItems = [];
        foreach ($queueRows as $queueId => $queueSet) {
            $dateTime = $queueSet[0]->dateTime;
            $payload = array_reduce($queueSet, fn(string $carry, EntityLogRow $logRow) => $carry . "\n" . $logRow->rowText, '');
            $payload = trim($payload);
            $queueItem = new EntityQueueItem(0, $dateTime, $queueId, $payload);
            $queueItems[] = $queueItem;
        }

        return $queueItems;
    }
}
