<?php

require_once 'init.php';
$fotos = $uimodel->load($_GET['album']);
$uimodel->draw($fotos, $_GET['album']);

?>
