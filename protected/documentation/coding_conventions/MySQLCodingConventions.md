# MySQL Coding conventions

Interactions with the database happen through two mechanisms.

Data is inserted and updated via Yii Models, this allows data to be validated before being inserted. Models are stored in /protected/models

If data in just one table is being modified then it is done through a static function in its model class. If more than one table is being modified then there are classes in /protected/models/multi

Data is usually selected via inline SQL (For both speed and ease of reading/editing complex joins). In order to facilitate this it is important to observe the coding conventions below as inline sql can become very messy.


### Inline SQL

'''
$sql = "
    SELECT
         table_1.column1
        ,table_1.column2
        ,table_2.column3
    FROM
        table_1
        INNER JOIN table_2 ON table_1.column1 = table_2.column_1
    WHERE
        condition_1 = :param1
        AND another_contion = :param2";
$connection = Yii::app()->db;
$command = $connection->createCommand($sql);
$command->bindValue(":param1", $some_value, PDO::PARAM_INT);
$command->bindValue(":param2", $another_value, PDO::PARAM_STR);
$rows = $command->queryAll();
'''

Note the following:

* The whole statement is a single multiline string.
* The SELECT is on a new line and that it is indented 4 spaces from the $sql statement.
* SELECT columns are indented four spaces and have preceeding commas (enables easy commenting out of lines)
* The first column is indented an extra space to allow for the commas on the following rows.
* FROM, WHERE etc are indented to the same level as the SELECT.
* tables, joins, WHERE conditions etc are indented four spaces.
* Each table is on its own line.
* parameters are always bound rather than concatenated. This is for both security and readability.

