<?php
if(file_exists("../../../init.php")) require_once "../../../init.php";
require_once 'Resizer.php';
require_once 'uploadImageModel.php';
require_once 'config.php';
require_once 'UploaderHelper.php';
require_once 'UploaderImagesHelper.php';

function simple_upload(){
    $img    = UploaderImagesHelper::getInstanceOf();
    if(!$img->Upload('/redactor/', $_FILES)){
        die(json_encode(array('error' => $img->getErrorMessage())));
    }
    $images = $img->getUploadedImages();
    $out = array();
    foreach($images as $img){
        $out[]['filelink'] = URL_IMAGENS . "/$img";
    }
    if(count($out) == 1) {$out = $out[0];}
    die(json_encode($out));
}
if(!empty($_FILES)){simple_upload();}
?>
<form action="image.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="files"/>
    <input type="submit" value="Enviar"/>
</form>