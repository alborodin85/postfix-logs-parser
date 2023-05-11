<?php

require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

$logFolderPath = $_ENV['LOG_FOLDER_PATH'];
