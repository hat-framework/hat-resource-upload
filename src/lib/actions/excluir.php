<?php

require_once 'init.php';
require_once '../Resizer.php';
require_once '../uploadImageModel.php';
require_once '../config.php';
require_once '../UploaderHelper.php';
require_once '../UploaderImagesHelper.php';
    
$status           = $uimodel->apagar($_GET['id']);
$json             = $uimodel->getMessages();
$json['status']   = $status;
$json['response'] = ($json['status'] == 0)? $uimodel->getErrorMessage(): $uimodel->getSuccessMessage();;
echo json_encode($json);

?>
