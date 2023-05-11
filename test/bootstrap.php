<?php

$dotenv = Dotenv\Dotenv::createImmutable('./');
$dotenv->load();

function dump(mixed $data)
{
    var_export($data);
    echo "\n";
    ob_flush();
}
