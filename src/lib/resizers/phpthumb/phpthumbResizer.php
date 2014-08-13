<?php

use classes\Classes\Object;
class phpthumbResizer extends classes\Classes\Object implements Resizer{
    
    public function __construct() {
        $this->LoadFile(dirname(__FILE__) ."/files/ThumbLib.inc.php");
    }
    
    public function SaveImage($imagem, $diretorio, $config, $extension){
        try{
            //cria a nova imagem, salva os novos efeitos e faz o upload da mesma no diretorio
            $this->thumb = PhpThumbFactory::create($imagem['tmp_name']);
            //$this->thumb->adaptiveResize($config['width'], $config['height']);
            $this->thumb->resize($config['width'], $config['height']);
            //$this->thumb->resizePercent("50");
            $this->thumb->save($diretorio, $extension);
            return true;
        }
        catch (Exception $e){
            $this->setErrorMessage("Erro na execução do script: " . $e->getMessage(), $e->getCode());
            return false;
        }
    }
    
    private function LoadConfiguration(){
        
        $this->config['efeito']     = array("adaptiveResize");

        //configuracoes default dos efeitos
        $this->config['createReflection']['param1'] = '40';
        $this->config['createReflection']['param2'] = '40';
        $this->config['createReflection']['param3'] = '80';
        $this->config['createReflection']['param4'] = true;
        $this->config['createReflection']['param5'] = '#a4a4a4';

        //efeito resize
        @$this->config['resize']['param1'] = UPLOAD_IMAGE_HEIGHT;
        @$this->config['resize']['param2'] = UPLOAD_IMAGE_WIDTH;

        //efeito adptive resize
        $this->config['adaptiveResize']['param1'] = UPLOAD_IMAGE_WIDTH;
        $this->config['adaptiveResize']['param2'] = UPLOAD_IMAGE_HEIGHT;

        //efeito resize percent
        $this->config['resizePercent']['param1'] = 50;

        //efeito rotate image
        $this->config['rotateImage']['param1'] = 'CW';

        //efeito rotate image n degrees
        $this->config['rotateImageNDegrees']['param1'] = '180';
    }
}

?>