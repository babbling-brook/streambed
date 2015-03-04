<?php
/**
 *
 * Copyright 2015 Sky Wickenden
 *
 * This file is part of StreamBed.
 * An implementation of the Babbling Brook Protocol.
 *
 * StreamBed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * at your option any later version.
 *
 * StreamBed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with StreamBed.  If not, see <http://www.gnu.org/licenses/>
 */

/**
 * This file contains examples of the PHP coding conventions to follow for developing code for this project.
 *
 * This document defines the coding conventions for php code in the BabblingBrook project.
 * These are enforced through the use of PHP CodeSniffer @see http://pear.php.net/package/PHP_CodeSniffer/
 * and code will not be commited to core without passing these tests.
 *
 * Some general coding rules :
 * <ul>
 *  <li>Line length must be less than 120 chars. For readability it is better to keep
 *      under 80 chars, but no error will be thrown until 120 characters is reached.</li>
 *  <li>The php defined constants false, true and null must allways be in lowercase</li>
 *  <li>User defined constants must allways be in ALL_CAPPS</li>
 *  <li>The short opening PHP tag is dissallowed.</li>
 *  <li>Tabs are dissallowed. All spacing must use spaces rather than tabs.</li>
 *  <li>Doc Comments are produced using PHP Documentor @see http://phpdoc.org</li>
 * </ul>
 *
 * File doc Comment rules :
 * <ul>
 *  <li>A doc comment exists.</li>
 *  <li>There is a blank newline after the short description.</li>
 *  <li>There is a blank newline between the long and short description.</li>
 *  <li>There is a blank newline between the long description and tags.</li>
 *  <li>Check the order of the tags.</li>
 *  <li>Check the indentation of each tag.</li>
 *  <li>Check required and optional tags and the format of their content.</li>
 *  <li>Must have a @license tag</li>
 *  <li>Use of @author tag is discouraged. See repository for author information.</li>
 * </ul>
 *
 * @license BabblingBrook License
 *       1. Using ($var) instead of ($var === true) etc
 *       2. Use of quotations. (perhaps convert all to single first.)
 *       3. Use of @return void in php but not javascript.
 *       4. Use of a space after the first line of the description.
 *       5. a line between @params and @return.
 *       6. multiline arrays need a trailing comma in php. IE doesn't like them in javascript. IE10?
 *       7. default statment in switch statements  is different.
 */

/**
 * An example class
 *
 * Class rules :
 * <ul>
 *  <li>All classes in the project must have unique names.</li>
 *  <li>All classes should be named in TitleCase with no underlines.</li>
 *  <li>Code in a class should be indented 4 spaces.</li>
 *  <li>Code in a class should be indented 4 spaces.</li>
 *  <li>Only one Class or Interface per File.</li>
 *  <li>Filename of the class file should be the same as the class name.</li>
 *  <li>The class keyword should appear at the left hand margin with no spaces  preceeding it.
 *      It should be followed by the class name and then the end of the line.</li>
 *  <li>The opening brace should appear at the start of the line imediatly after the class keyword.</li>
 *  <li>The opening and closing braces shoudl be against the left margin with no spaces.</li>
 *  <li>The class keywords must be lowercase.</li>
 * </ul>
 *
 * Class commenting rules
 * <ul>
 *  <li>A doc comment exists.</li>
 *  <li>There is a blank newline after the short description.</li>
 *  <li>There is a blank newline between the long and short description.</li>
 *  <li>There is a blank newline between the long description and tags.</li>
 *  <li>Check the order of the tags.</li>
 *  <li>Check the indentation of each tag.</li>
 *  <li>Check required and optional tags and the format of their content.</li>
 *  <li>Must have a @package tag</li>
 *  <li>Use of @author tag is discouraged. See repository for author information.</li>
 *  <li>The @license tag should be in the file comment and not here.</li>
 * </ul>
 *
 * @package PHP_Documentation
 */
class PHPCodingConventions
{

    /**
     * Class property documentation.
     *
     * All class properties must:
     * <ul>
     *  <li>Must be documented</li>
     *  <li>The short description ends with a full stop.</li>
     *  <li>There is a blank line after the short description.</li>
     *  <li>The long description is optional.</li>
     *  <li>There is a blank line between the description and the tags.</li>
     *  <li>There is a @var tag and it is the first tag to appear</li>
     *  <li>Check the order, indentation and content of each tag.</li>
     * </ul>
     *
     * @var string
     */
    public $public_property;

    /**
     * Class property naming rules.
     *
     * All properties must :
     * <ul>
     *  <li>All properties should be named in lowercase_underscore.
     *      Digits are allowed, but not for the first character.</li>
     *  <li>No properties should have a preceeding underscore.
     *      This is inline with JSLint and they are not required as
     *      this codebase is not php4 compatible.</li>
     * </ul>
     *
     * Invlaid member examples :
     * <ul>
     *  <li>public $PublicProperty;</li>
     *  <li>public $PUBLICPROPERTY;</li>
     *  <li>private $PrivateProperty;</li>
     *  <li>private $privateProperty;</li>
     *  <li>private $_private_property;</li>
     * </ul>
     *
     * @var string
     */
    private $private_property;

    /**
     * Class constructor functions must always be named __construct().
     */
    public function __construct() {
        echo 'In the constructor';
    }

    /**
     * General commenting rules.
     *
     * Must :
     * <ul>
     *  <li>Do not use pear style comments. (comments that assigned with a hash).</li>
     *  <li>All doc comment asterisks must be one space from the comment indent and
     *      have one space after them before content.</li>
     *  <li>Multiline comments must have asterisks one space from the starting indent.</li>
     * <ul>
     *
     * @return void
     */
    public function generalCommenting() {

        // This is a valid comment
        /* This is a valid comment*/
        /*
         * This is a valid comment
         */

        //  /*
        //      This is not a valid comment
        //   */

    }

    /**
     * Commenting rules for functions.
     *
     * Must :
     * <ul>
     *  <li>A comment exists</li>
     *  <li>There is a blank newline after the short description</li>
     *  <li>There is a blank newline between the long and short description</li>
     *  <li>The long description is optional.</li>
     *  <li>There is a blank newline between the long description and tags</li>
     *  <li>Parameter names represent those in the method</li>
     *  <li>Parameter comments are in the correct order</li>
     *  <li>Parameter comments are complete</li>
     *  <li>A type hint is provided for array and custom class</li>
     *  <li>Type hint matches the actual variable/class type</li>
     *  <li>A blank line is present before the first and after the last parameter</li>
     *  <li>A return type exists</li>
     *  <li>Any throw tag must have a comment</li>
     *  <li>The tag order and indentation are correct</li>
     * </ul>
     *
     * @return void
     */
    public function commentingFunctions() {

    }

    /**
     * Function rules.
     *
     * Shared function rules :
     * <ul>
     *  <li>There should zero spaces between the function name and the opening parenthesis.</li>
     *  <li>There should be no space between the opening parenthesis and the first paramater.</li>
     *  <li>Each paramater should be followed by a comma and then a space, unless it is the last paramater.</li>
     *  <li>There should be no duplicate arguments.</li>
     *  <li>Arguments with a default value must appear at the end of the list.</li>
     *  <li>There should be no spaces between arguments and their default paramaters, only an equals sign.</li>
     *  <li>The closeing brace should be in line with the function definition.</li>
     *  <li>There should be no duplicate arguments.</li>
     *  <li>Function names should be written in camelCase.</li>
     *  <li>Code inside the funtion should be indented with four spaces.</li>
     *  <li>All paramaters should be used in the funciton body - unless the function is an empty interface.</li>
     *  <li>Function keywords should be in lowercase.</li>
     * </ul>
     *
     * A public function/method with single line paramaters :
     * <ul>
     *  <li>There should be no space between the opening parenthesis and the first paramater.</li>
     *  <li>The closing parenthesis should imediately follow the last paramater with no white space.
     *    be on a new line and be followed by one space and the opening bracket,
     *    nothing else on the line.</li>
     *  <li>Each paramater should be followed by a comma and then a space,
     *      unless it is the last paramater.</li>
     * </ul>
     *
     * @param string $param1 Some parameter.
     * @param string $param2 Some parameter.
     * @param string $param3 Some parameter.
     *
     * @return void
     */
    public function publicFunction($param1, $param2, $param3) {
        echo 'In the public function ' . $param1 . ' ' . $param2 . ' ' . $param3;
    }

    /**
     * A static function/method with multiline paramaters and default paramaters.
     *
     * Must have :
     * <ul>
     *  <li>The first paramater should be declared on the same line as the function declaration.</li>
     *  <li>Multiple paramaters should appear on one line.</li>
     *  <li>The closing parenthesis should be indented to the same level as the funciton definition. It should
     *      be on a new line and be followed by one space and the opening bracket, with nothing else on the line.</li>
     *  <li>There should be a blank line after the opening brace and the first line of code.</li>
     * <ul>
     *
     * @param integer $param1 Comment for param1.
     * @param integer $param2 Comment for param2.
     * @param integer $param3 Comment for param3.
     * @param integer $param4 Comment for param4.
     * @param integer $param5 Comment for param5.
     * @param integer $param6 Comment for param6.
     * @param integer $param7 Comment for param7.
     *
     * @return void
     */
    public static function staticFunction($param1, $param2, $param3, $param4, $param5=22,
        $param6=4, $param7=6
    ) {

        echo 'In the static function ' . $param1 . ' ' . $param2 . ' ' . $param3 . ' ' . $param4 . ' ' . $param5;
        echo 'In the static function ' . $param6 . ' ' . $param7;
    }

    /**
     * Inline control structures are not allowed.
     *
     * This applies to if, else, foreach, while, do, switch, for, try and catch statements.
     * <ul>
     *  <li>Always use braces.</li>
     *  <li>There should be one space between the if keyword and the opening parentheses.</li>
     *  <li>There should be one space between the closing parentheses and the opening
     *      brace.</li>
     *  <li>The conditional code should be on it's own line(s).</li>
     *  <li>The conditional code should be indented 4 spaces.</li>
     *  <li>The closing brace should be on a new line and indented to the same level
     *      as the conditional keyword.</li>
     *  <li>When making an 'else if' condition there should always be a space between
     *      the else and the if. This maintains a similar coding style to JavaScript.</li>
     *  <li>Always include a space after a semicolon in a for statement.</li>
     *  <li>Never include a space before a semicolon in a for statement.</li>
     *  <li>No space after the opening parentheses or before the closing parentheses in a
     *      for or foreach statement.</li>
     *  <li>The contitional keyword should be lowercase.</li>
     *  <li>In a switch statement, the break keyword must be followed by a single blank line.</li>
     *  <li>In a switch statement, each case must include a break statement.</li>
     *  <li>In a switch statement, the case and default keywords must be indented the
     *      same amount as the case keyword.</li>
     *  <li>In a switch statement, there should be no space bwteen a case condition
     *      and the colon.</li>
     *  <li>In a switch statement, the conditonal code should be indented four spaces from
     *      the case keyword.</li>
     *  <li>In a switch statement, there must be a default conidtion. It can just
     *      contain a comment explaining why no default is needed.</li>
     *  <li>In a switch statment, a defualt case is not required. This is inline with JSLint.</li>
     *
     * @return void
     */
    private function conditionalStructures() {

        // Always uses braces
        if (true === true) {
            $something = 1;
        } else {
            $something_else = 2;
        }

        // Correct spaces in a for statement.
        for ($i; $i < 10; $i++) {
            $something = $i;
        }

        $an_array = array('one', 'two', 'three');
        foreach ($an_array as $row) {
            $something = $row;
        }

        switch ($something) {
        case 'one':
            $something_else = 'one';
            break;

        case 'two':
            $something_else = 'one';
            break;

        default:
            $something_else = 'default';
            break;
        }



        // Don't do this. Conditional code must be on a new line.
        //if (true === true) $something = 1;

        // Also don't do this. Conditional code must be surroounded by braces.
        // if(true === true)
        //     $something = 1;

        // Don't do this.  Closing brace not on a new line.
        //if (true === true) {
        //    $something = 1;
        //}

        // Don't do this. Conditional code not correctly indented.
        //if (true === true) {
        //    $something = 1;
        //}

        // Don't do this. No space between keyword and opening parentheses.
        //if (true === true) {
        //    $something = 1;
        //}

        // Don't do this. No space between closing parentheses and the opening brace.
        //if (true === true){
        //   $something = 1;
        //}

        // Don't do this. Statement on the same line as the if.
        //if (true === true) { $something = 1; }

        // Don't do this. No space between else and if.
        //if (true === true) {
        //    $something = 1;
        //} elseif (false === false) {
        //    $something_else = 2;
        //}

        // Don't do this. Incorrect spaces in a for statement.
        //for ( $i;$i < 10 ; $i++ ) {
        //    $something = $i;
        //}

        // Don't do this. Uppercase keywords.
        //IF (true === true) {
        //    $something = 1;
        //}

        // Don't do this. Condtional code should be indented four spaces from the case statment.
        //switch ($something) {
        //    case 'one':
        //        $something_else = 'one';
        //        break;
        //}
    }

    /**
     * General formatting rules.
     *
     * Must :
     * <ul>
     *  <li>1. Only one statment per line.</li>
     *  <li>Do not include any space after an inline cast.</li>
     *  <li>Multipline assignment, must either have the equals sign on the second
     *      line with each subsequent line having a concatenator in line with the
     *      equals sign. Or they must open on the first line and continue onto
     *      subsequent lines without closing until the final line. In the second
     *      instance all text must be aligned to the right of the equals sign with
     *      white space padding to the left of it on all lines.</li>
     *
     * @return void
     */
    public function formatting() {

        // One statment per line.
        $something = 'one';

        // Don't do this.
        //$something = 'one'; $something_else = 'two';

        // No space after an inline cast.
        $something = '3';
        $something_else = (int)$something;

        // Don't do this. Space after the cast.
        //$something = '3';
        //$something_else = (int) $something;

        // A multiline string must be indented four spaces from the start of the first line.
        // Strings use single quotes unless there is good reason not to.
        $multiline1 = 'A string spanning multiple lines is ok.
            Although it should be noted that the additional space will appear inside the string.
            In some situations such as SQL code this is acceptable.';

        $multiline3 = '
            If the first line is empty. Then the string may be indented four spaces from the start of
            opening line. This allows for more spcace.
            It can be indented an arbitrary extra amount';

        // Don't do this. The text is not indented four spaces.
        //$multiline4 = '
        //   If the first line is empty. Then the string may be indented four spaces from the start of
        //  opening line. This allows for more space.';

        // Multiline SQL statements  must take this format.
        // Any multiline string that starts with SELECT, UPDATE or INSERT  is considered a SQL statment.
        // If you have a normal string that starts lke this then use concatenation method below.
        // Select statements :
        // 1. The SELECT keyword must be indented 4 spaces.
        // 2. The selected paramaters must be indented 4 spaces each, except for the first which is indented 5.
        // 3. Each paramater except the first must be preceeded by a comma.
        // 4. FROM, WHERE, GROUP BY, HAVING and LIMIT must appear on a new line - also indented 4 spaces.
        // 5. Each line after the FROM keyword until the WHERE, GROUP BY or LIMIT keywords must be
        //    indented at least four spaces.
        // 6. Each ON statment must be indented at least 8 spaces.
        // 7. Each AND, OR kwyeord in a table selection must be indented at least 12 spaces.
        // 8. Nested SELECTS in table selections must be indented at least 16 spaces.
        // 9. Each WHERE condition must be indented at least four spaces.
        // 10. Any extra GROUP BY, HAVING or LIMIT lines must be indented at least 4 spaces.
        // 11. Any Nested statements  must be enclosed in parentheses.
        //     Nested statements  are not currently checked.
        // 12. To do something not covered by this specification, and it is interfering, enclose it in parentheses
        //     and it will be ignored.
        // 13. Unlike with other php code, MySql strings use double qoutes.
        $multiline_sql = "
            SELECT
                 param1
                ,param2 AS something_else
                ,param3
            FROM
                table1
                LEFT JOIN table2 ON table1.col1 = table2.col1
                INNER JOIN table3
                    ON table2.col2 = table3.col1
                        AND table1.col3 =
                            (SELECT
                                  col4
                             FROM table5
                             WHERE col6 = 2)
            WHERE
                table1.col5 = 'something'
                AND table2.col6 = 'something else'
            GROUP BY ...
            HAVING ...
            LIMIT 1
            PROCEDURE
            INTO OUTFILE
            FOR UPDATE";

        // Updates follow a similar pattern to SELECTS
        $multiline_sql = "
            UPDATE
                table1
                LEFT JOIN table2 ON table1.col1 = table2.col1
                INNER JOIN table3
                    ON table2.col2 = table3.col1
                        AND table1.col3 =
                            (SELECT
                                  col4
                             FROM table5
                             WHERE col6 = 2)
            SET
                 col1 = 'something'
                ,col2 = 'something else'
            WHERE
                col13 = 'something'
                AND col4 = 'something'
            ORDER BY
            LIMIT 1";

        // INSERTS have two formats.
        // The first format has :
        // 1. INSERT indented four spaces.
        // 2. The opening parentheses of the insert columns also indented four spaces.
        // 3. The columns then following the same rules as select params.
        // 4. The ") VALUES (" being indented four spaces.
        // 5. The values then following the same rules as select params.
        // 6. The closing parentheses being indented four spaces.
        $multiline_sql = "
            INSERT INTO table1
            (
                 col1
                ,col2
                ,col3
            ) VALUES (
                 'value1'
                ,'value2'
                ,'value3'
            )";

        // The second INSERT format :
        // 1. The column selection as before.
        // 2. A SELECT statment that follows the same rules as above.
        $multiline_sql = "
            INSERT INTO table1
            (
                 col1
                ,col2
                ,col3
            )
            SELECT
            FROM table1";

        $multiline6 =
              'If white space is important in a multiline string then use this format : '
            . 'The equals sign is indented four spaces on the second line, '
            . 'and each subsequent line has a line concatenator inline with the equals sign.';

        // Don't do this.  Equals sign must be indented four spaces.
        //$multiline4
        //= 'This is the first line of a concatenated multi line string.'
        //. 'The concatenator must be inline with equals sing.';

        $nested_array = array(
            array(
                'A multiline string inside an array'
                    . 'The first line should be indented six spaces from the start of the array keyword. '
                    . 'Subsequent lines should have a contatenator indented four spaces from the array keyword.'
            ),
        );

    }

    /**
     * Array format.
     *
     * Must :
     * <ul>
     *  <li>Empty arrays should have no space between the parentheses.</li>
     *  <li>There should be one space between the key and the =></li>
     *  <li>There should be one space after the =></li>
     *  <li>Commas should be at the end of the line</li>
     *  <li>5. There should be one space between the closing parenthesis and the opening brace.</li>
     *  <li>All lines - including the last, must have a trailing comma with no spaces between
     *      the value and the comma.</li>
     * </ul>
     *
     * @return void
     */
    public function arrays() {

        $single_line_array = array('one', 'two', 'three');

        $multiline_array = array(
            'something',
            array(
                'key' => 'one',
                'key2' => 'three',
                'key3' => array(
                    'deeply' => 'nested',
                    'level3' => '',
                    'arrays with very long keys or content can be spread over two lines.' =>
                        'The double arrow is on teh first line. The value is on the second, indented four spaces.'
                            . 'Additional lines are conncatenated on the extra line with an extra four spaces indent'
                            . 'from the first row.',
                    array(
                        'deeper' => 'here',
                    )
                ),
            ),
        );

        // fetching data from an array.
        // 1. There should be no spaces between the square brackets and the index.
        $multiline_array[1]['key2'];

    }

    /**
     * Operator rules.
     *
     * Must :
     * <ul>
     *  <li>Allways use === instead of $value or !$value</li>
     * </ul>
     *
     * @return void
     */
    public function operators() {

        // Do this instead of if($value)
        $value = true;
        if ($value === true) {
            $something = 1;
        }

        // Do this instead of if(!$value)
        $value = true;
        if ($value === false) {
            $something = 1;
        }
    }

    /**
     * Function call rules.
     *
     * Funciton calls must
     * <ul>
     *  <li>Have no space between the function name and the opening parenthesis.</li>
     *  <li>Have no space after the closing parentesis.</li>
     * </ul>
     *
     * If the call is on a single line :
     * <ul>
     *  <li>Each paramater must be followed by a comma with no white space.</li>
     *  <li>Each comma must be followed by a single space.</li>
     *  <li>The first paramater must be precceded by the opening paranthesis without a space.</li>
     *  <li>The last parmater must be followed by a closing paranthesis without a space.</li>
     * </ul>
     *
     * If the call is on multiple lines :
     * <ul>
     *  <li>Each paramater must be on its own line and indented four spaces from the function call.</li>
     *  <li>Each paramater must be followed by a comma with no white space except for the last paramater.</li>
     *  <li>The closing parenthesis should be on the same indent level as the start of the line that makes the
     *      function call and it should be on a line by itself.</li>
     * </ul>
     *
     * @return void
     */
    public function functionCalls() {

        // Single line example.
        ConventionClass::staticFunction(400, 43);

        // Multiline example.
        $conventionObject = new ConventionClass;
        $some_var = $conventionObject->publicFunction(
            23,
            235
        );

        // Single and multiline exxamples nested in an array.
        $some_array = array(
            array(
                'some string',
                $conventionObject->publicFunction(400, 43),
                $conventionObject->publicFunction(
                    400,
                    43
                ),
            ),
        );
    }

    /**
     * Don't do this.  Underscore in function rather than camel case.
     */
    //public static function fail_CamelCaseFunction() {
    //    echo 'failed camelcase function name.';
    //}

    /**
     * Don't do this.  Title case function rather than camel case.
     */
    //public static function FailCamelCaseFunction() {
    //    echo 'failed camelcase function name.';
    //}

}

?>
<div>
    <?php
    $indent_starts = 'at the same position as the open php tag.'
    ?>
</div>