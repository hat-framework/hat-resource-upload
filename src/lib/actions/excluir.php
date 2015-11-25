<?php

require_once 'init.php';
require_once '../Resizer.php';
require_once '../uploadImageModel.php';
require_once '../config.php';
require_once '../UploaderHelper.php';
require_once '../UploaderImagesHelper.php';

$id     = filter_input(INPUT_GET, 'id');
$status = false;
if($id != ""){
    $status = $uimodel->apagar($id);
    $json   = $uimodel->getMessages();
    $json['response'] = (isset($json['status']) && $json['status'] == 0)? $uimodel->getErrorMessage(): $uimodel->getSuccessMessage();
}else{
    $json['erro'] = 'O id do arquivo não foi informado!';
}

$json['status']   = $status;
echo json_encode($json);
