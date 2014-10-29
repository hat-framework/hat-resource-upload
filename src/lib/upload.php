<?php
function debugaki($dados){
    $msg['response']   = is_array($dados)?serialize($dados):$dados;
    $msg['status'] = 0;
    echo json_encode($msg);
    die();
}

    define("UPLOADING", true);
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/init.php')) require_once $_SERVER['DOCUMENT_ROOT'].'/init.php';
    require_once 'Resizer.php';
    require_once 'uploadImageModel.php';
    require_once 'config.php';
    require_once 'UploaderHelper.php';
    require_once 'UploaderImagesHelper.php';

    $msgs  = array();
    $model = new uploadImageModel();    
    if(!$model->upload()){
        $msgs['response'] = $model->getErrorMessage();
        $msgs['status']   = "0";
    }
    else{
        $msgs['img']      = $model->draw("", '', false);
        $msgs['response'] = $model->getSuccessMessage();
        $msgs['status']   = "1";
    }
    echo json_encode($msgs);