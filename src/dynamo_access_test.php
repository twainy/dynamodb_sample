<?php
require_once dirname(__FILE__).'/../vendor/autoload.php';
\Aws\DynamoDb\DynamoDbClient::factory(
    [
        'profile' => 'default',
        'region' => 'ap-northeast-1',
    ]
);
