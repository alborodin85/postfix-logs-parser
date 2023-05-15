<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
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
$addedArchives = [];
foreach ($allArchives as $archiveName) {
    if ($archiveName <= $lastArchiveFileName) {
        continue;
    }
    $sourceLinesArray = gzfile($archiveName);
    $logRows = $rowParser->parseArray($sourceLinesArray);

    $chunkedLogRowsArray = array_chunk($logRows, (int)$_ENV['ADD_RECORDS_CHUNK_SIZE']*100);
    foreach ($chunkedLogRowsArray as $chunk) {
        usleep(100000);
        $entityRequestAddRecords = new \App\RestApi\EntityRequestAddRecords($chunk);
        $serviceRestApiRequest->post($_ENV['ENDPOINT_ADD_ARCHIVE_LOG_ROWS'], $entityRequestAddRecords->toArFields());
    }

    $queuesArray = $composerByQueues->buildQueues($logRows);
    $mailsArray = $parserQueuePayload->parseQueuesArray($queuesArray);
    $allMailsArray = array_merge($allMailsArray, $mailsArray);
    $addedArchives[] = $archiveName;
}

$chunkedMailsArray = array_chunk($allMailsArray, (int)$_ENV['ADD_RECORDS_CHUNK_SIZE']);
foreach ($chunkedMailsArray as $chunk) {
    usleep(100000);
    $entityRequestAddRecords = new \App\RestApi\EntityRequestAddRecords($chunk);
    $serviceRestApiRequest->post($_ENV['ENDPOINT_ADD_ARCHIVE_EMAILS'], $entityRequestAddRecords->toArFields());
}

$requestData = [
    'archivesNames' => $addedArchives,
];
$serviceRestApiRequest->post($_ENV['ENDPOINT_ADD_ARCHIVE_NAMES'], $requestData);

//var_export($allMailsArray);
echo "\n";

echo 'end';
