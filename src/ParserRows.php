<?php

namespace App;

class ParserRows
{
    public function parseArray(array $sourceLinesArray): array
    {
        $logRows = [];
        foreach ($sourceLinesArray as $sourceLine) {
            $logRow = $this->parseLine($sourceLine);
            $logRows[] = $logRow;
        }

        return $logRows;
    }

    public function parseFile(string $fileName): array
    {
        $logFile = fopen($fileName, 'r+t');
        $logRows = [];
        while (!feof($logFile)) {
            $sourceLine = fgets($logFile);
            $sourceLine = trim($sourceLine);
            if (!$sourceLine) {
                continue;
            }
            $logRow = $this->parseLine($sourceLine);
            $logRows[] = $logRow;
        }

        return $logRows;
    }

    public function parseLine(string $sourceLine): EntityLogRow
    {
        $allocatedMessageMatches = [];
        $allocatedMessagePattern = '/(\w* \d{2} \d{2}:\d{2}:\d{2}) (\S*) (.*?)\[(\d*)]: (.*)/s';
        $allocatedMessageResult = preg_match($allocatedMessagePattern, $sourceLine, $allocatedMessageMatches);

        if (!$allocatedMessageResult) {
            return new EntityLogRow(
                id: 0,
                dateTime: date('Y-m-d 00:00:00'),
                hostName: '',
                module: '',
                procId: 0,
                queueId: '',
                errorLevel: 'incorrect_line',
                rowText: $sourceLine,
            );
        }

        $message = $allocatedMessageMatches[5];

        $dateTimeRow = $allocatedMessageMatches[1];
        $hostName = $allocatedMessageMatches[2];
        $module = $allocatedMessageMatches[3];
        $procId = $allocatedMessageMatches[4];
        $queueId = '';
        $errorLevel = '';
        $rowText = $message;

        $queueErrorMatches = [];
        $queueErrorPattern = '/(\w*): (panic|fatal|error|warning|statistics|reject): (.*)/s';
        $queueErrorResult = preg_match($queueErrorPattern, $message, $queueErrorMatches);
        $result = $queueErrorResult;

        if ($result) {
            $queueId = $queueErrorMatches[1];
            $errorLevel = $queueErrorMatches[2];
            $rowText = $queueErrorMatches[3];
        }

        if (!$result) {
            $errorLevelMatches = [];
            $errorLevelPattern = '/(panic|fatal|error|warning|statistics|reject): (.*)/s';
            $errorLevelResult = preg_match($errorLevelPattern, $message, $errorLevelMatches);
            $result = $errorLevelResult;
            if ($result) {
                $errorLevel = $errorLevelMatches[1];
                $rowText = $errorLevelMatches[2];
            }
        }

        if (!$result) {
            $queueIdMatches = [];
            $queueIdPattern = '/(\w*): (.*)/s';
            $queueIdResult = preg_match($queueIdPattern, $message, $queueIdMatches);
            $result = $queueIdResult;
            if ($result) {
                $queueId = $queueIdMatches[1];
                $rowText = $queueIdMatches[2];
            }
        }

        $logRow = new EntityLogRow(
            id: 0,
            dateTime: $this->parseDateTime($dateTimeRow),
            hostName: $hostName,
            module: $module,
            procId: $procId,
            queueId: $queueId,
            errorLevel: $errorLevel,
            rowText: $rowText
        );

        return $logRow;
    }

    private function parseDateTime(string $dateTimeRaw): string
    {
        $sourceDateFormat = 'M d H:i:s';
        $dateTimeObject = \DateTime::createFromFormat($sourceDateFormat, $dateTimeRaw, new \DateTimeZone('Europe/Moscow'));
        $result = date('Y-m-d H:i:s', $dateTimeObject->getTimestamp());

        return $result;
    }
}
