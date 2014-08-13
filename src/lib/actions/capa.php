<?php

require_once 'init.php';
$status           = $uimodel->setCapa($_GET['id']);
$json             = $uimodel->getMessages();
$json['status']   = $status;
$json['response'] = ($json['status'] == 0)? $uimodel->getErrorMessage(): $uimodel->getSuccessMessage();

echo json_encode($json);

?>
