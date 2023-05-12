<?php

require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

$currentLogPath = $_ENV['CURRENT_LOG_FILE'];

$rowParser = new \App\ParserRows();
$logRows = $rowParser->parseFile($currentLogPath);

$composerByQueues = new \App\ComposerByQueues();
$queuesArray = $composerByQueues->buildQueues($logRows);

$parserQueuePayload = new \App\ParserQueuePayload();
$mailsArray = $parserQueuePayload->parseQueuesArray($queuesArray);

$serviceRestApiRequest = new \App\RestApi\ServiceRestApiRequest(
    $_ENV['WEB_PATH_SCHEMA'], $_ENV['WEB_PATH_DOMAIN'], $_ENV['ENDPOINT_API_TOKEN']
);
$serviceRestApiRequest->post($_ENV['ENDPOINT_CLEAR_CURRENT_RECORDS'], []);

$chunkedMailsArray = array_chunk($mailsArray, (int)$_ENV['ADD_RECORDS_CHUNK_SIZE']);
foreach ($chunkedMailsArray as $chunk) {
    $entityRequestAddRecords = new \App\RestApi\EntityRequestAddRecords($chunk);
    $serviceRestApiRequest->post($_ENV['ENDPOINT_ADD_CURRENT_RECORDS'], $entityRequestAddRecords->toArFields());
}

var_export($mailsArray);
echo "\n";
