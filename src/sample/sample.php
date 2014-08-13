<?php

$this->LoadJsPlugin("upload/uploadify", "uify");
$this->uify->configure($field_name = 'teste', $album = "", $usuario = "", $folder = '');
$this->uify->draw();

?>
