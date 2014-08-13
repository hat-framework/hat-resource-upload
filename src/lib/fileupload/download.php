<?php

if(file_exists("../../../../init.php")) require_once "../../../../init.php";
require_once "../../lib/config.php";

require('../../uploadResource.php');
$arr['folder'] = @$_GET['folder'];
$upload_handler = new UploadResource();
$upload_handler->setOptions($arr);


$file = @$_GET['file'];
$upload_handler->download($file);

?>

