<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$this->layout='blank';
$cs = Yii::app()->getClientScript();

 // Creates an empty console object. Removing console messages.
$cs->registerCssFile(Yii::app()->baseUrl . '/js/resources/jasmine/lib/jasmine-2.2.0/jasmine.css');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/jasmine/lib/jasmine-2.2.0/jasmine.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/jasmine/lib/jasmine-2.2.0/jasmine-html.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/jasmine/lib/jasmine-2.2.0/boot.js');


//$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/jasmine/src/Player.js');
//$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/jasmine/src/Song.js');
//$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/jasmine/spec/PlayerSpec.js');
//$cs->registerScriptFile(Yii::app()->baseUrl . '/js/resources/jasmine/spec/SpecHelper.js');

//$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Client/Tests/Jasmine/Stream.js');

$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Library.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Test.js');
$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Shared/Backbone/Model.js');

$cs->registerScriptFile(Yii::app()->baseUrl . '/js/Client/Tests/Jasmine/Shared/Backbone/Model.js');
?>
<html>
    <head>
        <?php // including this manually as it is blocked by controler.php if included in a registerScriptFile(). ?>
        <script type="text/javascript" src="/js/resources/jquery.js"></script>
        <script type="text/javascript" src="/js/resources/underscore.js"></script>
        <script type="text/javascript" src="/js/resources/backbone.js"></script>
        <script type="text/javascript">
            window.BabblingBrook = {};
            BabblingBrook.Backbone = {};
            BabblingBrook.Client = {};
            BabblingBrook.Client.BackboneModel = {};
        </script>
    </head>
    <body>
        <h1>Running all JavaScript Tests</h1>
    </body>
</html>
