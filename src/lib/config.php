<?php 

/*opçções padrão*/
define("UPLOADER_PADRAO"    , "uploadify");
define("RESIZER_PADRAO"     , "phpthumb");

/*diretorios*/
define('DIR_DEFAULT_UPLOAD'         ,'upload');
define('DIR_UPLOAD'                 , DIR_IMAGENS . DIR_DEFAULT_UPLOAD);
define('URL_UPLOAD'                 , URL_IMAGENS . DIR_DEFAULT_UPLOAD);

/*configurações da imagem*/
define('UPLOAD_IMAGE_EFEITO'        , "ResizePercent");
define('UPLOAD_IMAGE_EXTENSIONS'    , "pjpeg|jpeg|png|gif|bmp");
define('UPLOAD_IMAGE_SIZE'          , 1006883);//arquivos de 1mb
define('UPLOAD_RESIZE'              , "");
define('UPLOAD_IMAGE_THUMB_EFEITO'  , "ResizePercent");

//tamanhos das imagens
define('UPLOAD_IMAGE_WIDTH'         , 800);
define('UPLOAD_IMAGE_HEIGHT'        , 600);
define('UPLOAD_IMAGE_SUFIX'         , "");
define('UPLOAD_IMAGE_MIN_WIDTH'     , 60);
define('UPLOAD_IMAGE_MIN_HEIGHT'    , 60);
define('UPLOAD_IMAGE_MIN_SUFIX'     , "_min");
define('UPLOAD_IMAGE_MEDIUM_WIDTH'  , 160);
define('UPLOAD_IMAGE_MEDIUM_HEIGHT' , 160);
define('UPLOAD_IMAGE_MEDIUM_SUFIX'  , "_medium");
define('UPLOAD_IMAGE_MAX_WIDTH'     , 320);
define('UPLOAD_IMAGE_MAX_HEIGHT'    , 240);
define('UPLOAD_IMAGE_MAX_SUFIX'     , "_max");


?>
