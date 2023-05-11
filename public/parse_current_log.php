<?php

require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

$currentLogPath = $_ENV['CURRENT_LOG_FILE'];

$rowParser = new \App\ParserRows();

$logFile = fopen($currentLogPath, 'r+t');
$logRows = [];
while (!feof($logFile)) {
    $sourceLine = fgets($logFile);
    $sourceLine = trim($sourceLine);
    if (!$sourceLine) {
        continue;
    }
    $logRow = $rowParser->parse($sourceLine);
    $logRows[] = $logRow;
}

$composerByQueues = new \App\ComposerByQueues();
$queuesArray = $composerByQueues->buildQueues($logRows);

$parserQueuePayload = new \App\ParserQueuePayload();
$mailsArray = [];

foreach ($queuesArray as $queueItem) {
    $mails = $parserQueuePayload->buildMailMessage($queueItem);
    $mailsArray = array_merge($mailsArray, $mails);
}

var_export($mailsArray);
echo "\n";
