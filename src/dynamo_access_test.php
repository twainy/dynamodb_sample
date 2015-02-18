<?php
require_once dirname(__FILE__).'/../vendor/autoload.php';
$start_time = microtime();
$print_elapsed_time = function ($label_name) use ($start_time){
    static $before_time;
    $microtime = microtime();
    $total_elapsed_time  = $microtime - $start_time;
    $command_elapsed_time = $microtime - $before_time;
    $before_time = $microtime;
    print "【${label_name}】 total: ${total_elapsed_time} msec. command: ${command_elapsed_time} msec. <br>".PHP_EOL;
};
$client = \Aws\DynamoDb\DynamoDbClient::factory(
    [
        'profile' => 'default',
        'region' => 'ap-northeast-1',
    ]
);
/*
$client->createTable(
    [
        'TableName' => "example_names",
        'AttributeDefinitions' => [
            [
                'AttributeName' => 'id',
                'AttributeType' => 'N',
            ],
        ],
        'KeySchema' => [
            [
                'AttributeName' => 'id',
                'KeyType'       => 'HASH',
            ]
        ],
        'ProvisionedThroughput' => [
            'ReadCapacityUnits' => 1,
            'WriteCapacityUnits' => 2,
        ]

    ]
);
$print_elapsed_time("create table");
$client->waitUntilTableExists(
    [
        'TableName' => "example_names",
    ]
);
$print_elapsed_time("wait create table");
$client->updateTable(
    [
        'TableName' => "example_names",
        'ProvisionedThroughput' => [
            'ReadCapacityUnits' => 1,
            'WriteCapacityUnits' => 1,
        ]
    ]
);
$print_elapsed_time("update table");
$client->waitUntilTableExists(
    [
        'TableName' => "example_names",
    ]
);
$print_elapsed_time("wait update table");
*/
$result = $client->describeTable(['TableName'=>"example_names"]);
$print_elapsed_time(sprintf("describe table item count:[%d]",$result['Table']['ItemCount']));
$result = $client->listTables();
$list_table_name = $result->getAll()['TableNames'];
$print_elapsed_time(sprintf("list table_name:[%s]",implode(",",$list_table_name)));
foreach (['Jonathan','Joseph','Jotaro','Josuke','Giorno'] as $num=>$name ) {
    $client->putItem([
        'TableName' => 'example_names',
        'Item' => [
            'id' => ['N'=>$num+1],
            'name' => ['S'=>$name],
        ]
    ]);
}
$print_elapsed_time("put 4 item");
$result = $client->getItem(
    [
        'ConsistentRead' => false,
        'TableName' => 'example_names',
        'Key' => [
            'id' => ['N'=>3], // Jotaro
        ]
    ]);
$print_elapsed_time(sprintf("get item [%s]",$result['Item']['name']['S']));
$result = $client->batchGetItem(
    [
        'TableName' => 'example_names',
        'RequestItems' => [
            'example_names' => [
                'Keys' => [
                    [
                        'id' => ['N'=>3,], // Jotaro
                    ],
                    [
                        'id' => ['N'=>4,], // Josuke
                    ]
                ],
                'ConsistentRead' => false,
            ]
        ]
    ]);
$name_list = array_map(function($item){return $item['name']['S'];},$result->getPath("Responses/example_names"));
$print_elapsed_time(sprintf("get batch item [%s]",implode(",",$name_list)));
$iterator = $client->getIterator('Query',
    [
        'TableName' => 'example_names',
        'KeyConditions' => [
            'id' => [
                'AttributeValueList'=> [
                    ['N' => 3],
                ],
                'ComparisonOperator' => 'EQ'
            ],
        ],
    ]
);
$name_list = [];
foreach ($iterator as $item) {
    $name_list[] = $item['name']['S'];
}
$print_elapsed_time(sprintf("query item [%s]",implode(",",$name_list)));


