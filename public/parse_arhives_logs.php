<?php

require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

$logFolderPath = $_ENV['LOG_FOLDER_PATH'];

$serviceRestApiRequest = new \App\RestApi\ServiceRestApiRequest(
    $_ENV['WEB_PATH_SCHEMA'], $_ENV['WEB_PATH_DOMAIN'], $_ENV['ENDPOINT_API_TOKEN']
);
$getLastArchiveResponse = $serviceRestApiRequest->post($_ENV['ENDPOINT_GET_LAST_ARCHIVE'], []);

if ($getLastArchiveResponse->errorCode) {
    if (!$_ENV['IS_TESTING']) {
        die('не удалось получить название последнего архива');
    }
}

$payloadGetLastArchive = new \App\RestApi\EntityResponseGetLastArchive($getLastArchiveResponse);
$lastArchiveFileName = $payloadGetLastArchive->lastArchiveFileName;
if ($_ENV['IS_TESTING']) {
    $lastArchiveFileName = '';
}

$allArchives = glob($_ENV['ARCHIVE_LOG_PATTERN']);

$rowParser = new \App\ParserRows();
$composerByQueues = new \App\ComposerByQueues();
$parserQueuePayload = new \App\ParserQueuePayload();

$allMailsArray = [];
foreach ($allArchives as $archiveName) {
    if ($archiveName <= $lastArchiveFileName) {
        continue;
    }
    $sourceLinesArray = gzfile($archiveName);
    $logRows = $rowParser->parseArray($sourceLinesArray);
    $queuesArray = $composerByQueues->buildQueues($logRows);
    $mailsArray = $parserQueuePayload->parseQueuesArray($queuesArray);
    $allMailsArray = array_merge($allMailsArray, $mailsArray);
}

$chunkedMailsArray = array_chunk($allMailsArray, (int)$_ENV['ADD_RECORDS_CHUNK_SIZE']);
foreach ($chunkedMailsArray as $chunk) {
    $entityRequestAddRecords = new \App\RestApi\EntityRequestAddRecords($chunk);
    $serviceRestApiRequest->post($_ENV['ENDPOINT_ADD_ARCHIVE_RECORDS'], $entityRequestAddRecords->toArFields());
}

var_export($allMailsArray);
echo "\n";

echo 'end';
