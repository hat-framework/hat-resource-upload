<?php

require_once 'init.php';
$foto['img']     = $uimodel->getUrl($_GET['id'], "");
$foto['img_max'] = $uimodel->getUrl($_GET['id'], "max");
echo json_encode($foto);

?>
