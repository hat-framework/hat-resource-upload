<?php

require_once '../../../../init.php';
$obj  = new Object();
$html = $obj->LoadResource('html', "html");
$html->start();

$uploader = $obj->LoadJsPlugin('upload/blueimp', 'blu');
$uploader->drawForm("test", array('folder' => 'test'));

$html->flush();

?>