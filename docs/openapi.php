<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\Generator;

$openapi = Generator::scan([
    __DIR__ . '/../src',
]);

header('content-type: application/json');
echo $openapi->toJson();