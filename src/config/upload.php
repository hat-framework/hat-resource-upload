<?php

/*opçções padrão*/
if(!defined('UPLOADER_PADRAO'))           {define("UPLOADER_PADRAO"            , "uploadify");}
if(!defined('RESIZER_PADRAO'))            {define("RESIZER_PADRAO"             , "phpthumb");}

/*diretorios*/
if(!defined('DIR_DEFAULT_UPLOAD'))        {define('DIR_DEFAULT_UPLOAD'         ,'upload');}
if(!defined('DIR_UPLOAD'))                {define('DIR_UPLOAD'                 , DIR_IMAGENS . DIR_DEFAULT_UPLOAD);}
if(!defined('URL_UPLOAD'))                {define('URL_UPLOAD'                 , URL_IMAGENS . DIR_DEFAULT_UPLOAD);}
if(!defined('UPLOAD_BLOCKED_EXTENSIONS')) {define('UPLOAD_BLOCKED_EXTENSIONS'  , "phtml|php|pl|py|jsp|asp|htm|shtml|sh|cgi");}

/*configurações da imagem*/
if(!defined('UPLOAD_IMAGE_EFEITO'))       {define('UPLOAD_IMAGE_EFEITO'        , "ResizePercent");}
if(!defined('UPLOAD_IMAGE_EXTENSIONS'))   {define('UPLOAD_IMAGE_EXTENSIONS'    , "pjpeg|jpeg|png|gif|bmp");}
if(!defined('UPLOAD_IMAGE_SIZE'))         {define('UPLOAD_IMAGE_SIZE'          , 2013766);}//arquivos de 2mb
if(!defined('UPLOAD_RESIZE'))             {define('UPLOAD_RESIZE'              , "");}
if(!defined('UPLOAD_IMAGE_THUMB_EFEITO')) {define('UPLOAD_IMAGE_THUMB_EFEITO'  , "ResizePercent");}

//tamanhos das imagens
if(!defined('UPLOAD_IMAGE_WIDTH'))        {define('UPLOAD_IMAGE_WIDTH'         , 1336);}
if(!defined('UPLOAD_IMAGE_HEIGHT'))       {define('UPLOAD_IMAGE_HEIGHT'        , 1336);}
if(!defined('UPLOAD_IMAGE_SUFIX'))        {define('UPLOAD_IMAGE_SUFIX'         , "");}
if(!defined('UPLOAD_IMAGE_MIN_WIDTH'))    {define('UPLOAD_IMAGE_MIN_WIDTH'     , 60);}
if(!defined('UPLOAD_IMAGE_MIN_HEIGHT'))   {define('UPLOAD_IMAGE_MIN_HEIGHT'    , 60);}
if(!defined('UPLOAD_IMAGE_MIN_SUFIX'))    {define('UPLOAD_IMAGE_MIN_SUFIX'     , "_min");}
if(!defined('UPLOAD_IMAGE_MEDIUM_WIDTH')) {define('UPLOAD_IMAGE_MEDIUM_WIDTH'  , 160);}
if(!defined('UPLOAD_IMAGE_MEDIUM_HEIGHT')){define('UPLOAD_IMAGE_MEDIUM_HEIGHT' , 160);}
if(!defined('UPLOAD_IMAGE_MEDIUM_SUFIX')) {define('UPLOAD_IMAGE_MEDIUM_SUFIX'  , "_medium");}
if(!defined('UPLOAD_IMAGE_MAX_WIDTH'))    {define('UPLOAD_IMAGE_MAX_WIDTH'     , 320);}
if(!defined('UPLOAD_IMAGE_MAX_HEIGHT'))   {define('UPLOAD_IMAGE_MAX_HEIGHT'    , 240);}
if(!defined('UPLOAD_IMAGE_MAX_SUFIX'))    {define('UPLOAD_IMAGE_MAX_SUFIX'     , "_max");}
