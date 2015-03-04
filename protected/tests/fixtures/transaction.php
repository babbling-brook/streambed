<?php

return array(
    'test_transaction' => array(
        'transaction_id' => 1,
        'date_created' => date('Y-m-d H:i:s', time() - (2 * 24 * 60 * 60)), // Less that a week ago
        'user_id' => 1,
        'type' => 'test',
        'role' => 1,
        'with' => 'http://cobaltcascade.localhost/test2',
        'my_value' => 1,
        'with_value' => 2,
        'origin' => 'http://givesalt.localhost/',
        'guid' => '21ec2020-3aea-1069-a2dd-08002b30309d',
        'current' => 1,
        'notified' => 0,
    ),
    'test_transaction2' => array(
        'transaction_id' => 2,
        'date_created' => '2000-01-01 22:59:58',
        'user_id' => 1,
        'type' => 'test',
        'role' => 1,
        'with' => 'http://cobaltcascade.localhost/test2',
        'my_value' => 1,
        'with_value' => 2,
        'origin' => 'http://givesalt.localhost/',
        'guid' => '21ec2020-3aea-1069-a2dd-08002b303175',
        'current' => 1,
        'notified' => 1,
    ),
    // This transaction is a duplicate of above for testing UpdateCurrent
    'test_transaction2b' => array(
        'transaction_id' => 3,
        'date_created' => '2000-01-01 22:59:55',
        'user_id' => 1,
        'type' => 'test',
        'role' => 1,
        'with' => 'http://cobaltcascade.localhost/test2',
        'my_value' => 4,
        'with_value' => 5,
        'origin' => 'http://givesalt.localhost/',
        'guid' => '21ec2020-3aea-1069-a2dd-08002b303175',
        'current' => 1,
        'notified' => 1,
    ),
    'test_transaction3' => array(
        'transaction_id' => 4,
        'date_created' => date('Y-m-d H:i:s', time() - (8 * 24 * 60 * 60)), // More than a week ago
        'user_id' => 1,
        'type' => 'test',
        'role' => 1,
        'with' => 'http://cobaltcascade.localhost/test2',
        'my_value' => 1,
        'with_value' => 2,
        'origin' => 'http://givesalt.localhost/',
        'guid' => '21ec2020-3aea-1069-a2dd-08002b303734',
        'current' => 1,
        'notified' => 0,
    ),

);

?>
