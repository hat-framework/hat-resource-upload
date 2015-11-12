<?php

class UploaderImagesHelper extends classes\Classes\Object {

    
        private $resizer = null;
	private $arquivo = NULL;    //arquivo que conterá as fotos ou o array de fotos
        private $names   = NULL;    //guardara os nomes dos arquivos gerados
        private $diretorio;         //nome do diretorio a ser gravado
        private $extension = "png"; //extensao das imagens a serem salvas
        private $img_relative_path; //diretorio relativo a pasta de upload 

        protected function  __construct() {
            $this->configure();
        }
        
        static private $instance = NULL;  
        public static function getInstanceOf(){
            $class_name = __CLASS__;
            if (self::$instance == NULL)
                self::$instance = new $class_name;
            return self::$instance;
        }
        
        private $uploaded = array();

        public function getUploadedImages(){
            return $this->uploaded;
        }

        public function getNames(){
            return $this->names;
        }
        
        public function getPaths(){
            return $this->paths;
        }
        
        public function getExtension(){
            return $this->extension;
        }
        
        public function Upload($diretorio, $arquivos = array(), $config = NULL){

            $this->img_relative_path = DIR_DEFAULT_UPLOAD . "/". $diretorio;
            $diretorio = DIR_UPLOAD.$diretorio; //diretorio das imagens
            getTrueDir($diretorio);
            getTrueDir($this->img_relative_path);

            //inicializa as variaveis e faz os testes de sanidade
            if(false === $this->VerifyVars($diretorio, $arquivos, $config)){return false;}

            //valida os arquivos
            if(false === $this->Validate($this->arquivo)){
                $this->dir_obj->remove($this->diretorio);
                return false;
            }

            //faz o upload das imagens
            if(false === $this->UploadImages($this->arquivo, $this->diretorio)){
                $this->dir_obj->remove($this->diretorio);
                return false;
            }

            return true;
        }
        
                private function VerifyVars($diretorio, $arquivos, $config){

                    //se o arquivo é vazio retorna falso
                    if(empty ($arquivos)){return $this->setErrorMessage("Selecione Um Arquivo.");}

                    //se o diretorio é vazio, enviará os arquivos para o diretorio padrao
                    $this->nome = $this->geraNome();
                    $diretorio .= $this->nome."/";
                    $dir        = $diretorio;
                    if(false === $this->checkFileExists($dir)){return false;}

                    $this->arquivo   = array_shift($arquivos);
                    $this->diretorio = $diretorio;
                    return true;
                }
                
                        private function geraNome(){
                            return (rand(0, 5) . date('HisdmY'));
                        }
                        
                        private function checkFileExists($diretorio){
                            if(file_exists($diretorio)){return true;}
                            $this->LoadResource("files/dir", "dir_obj");
                            $dir      = explode("/", $diretorio);
                            $name     = array_pop($dir);
                            $nm       = ($name == "")? array_pop($dir):$name;
                            $location = implode("/",$dir) . "/";
                            if(!$this->dir_obj->create($location, $nm)){
                                return $this->setErrorMessage($this->dir_obj->getErrorMessage());
                            }
                            return true;
                        }
                
                private function Validate($arquivos){
                    if(array_key_exists("tmp_name", $arquivos)){
                        return($this->ValidaImagem($arquivos));
                    }
                    
                    foreach($arquivos as $imagem){
                        if(false === $this->ValidaImagem($imagem)){return false;}
                    }
                    return true;
                }
                        
                        private function ValidaImagem($imagem){

                            // verifica se imagem possui dimensoes
                            $tamanhos = array();
                            if(false === $this->verifyDimensions($imagem, $tamanhos)){return false;}

                            //verifica os tipos da imagem
                            if(false === $this->verifyMimeType($tamanhos)){return false;}
                            
                            //verifica o tamanho da imagem
                            return $this->verifySizes($imagem, $tamanhos);
                        }
                        
                                private function verifyDimensions($imagem, &$tamanhos){
                                    $tamanhos = getimagesize( $imagem['tmp_name'] );
                                    if ( !is_array( $tamanhos ) || empty( $tamanhos ) || $tamanhos === false){
                                        return $this->setErrorMessage("Arquivo enviado não é imagem");
                                    }
                                    return true;
                                }
                                
                                private function verifyMimeType($tamanhos){
                                    $type = $tamanhos['mime'];
                                    if(strpos($type, "image/")!==false){return true;}
                                    return $this->setErrorMessage(
                                        "Arquivo em formato inválido! A imagem deve ser dos tipo: 
                                        " . UPLOAD_IMAGE_EXTENSIONS ."
                                        .Envie outro arquivo"
                                    );
                                }
                                
                                private function verifySizes($imagem, $tamanhos){
                                    if(false != $this->config['resize']){return true;}

                                    // Verifica tamanho do arquivo
                                    if($imagem["size"] > UPLOAD_IMAGE_SIZE && UPLOAD_IMAGE_SIZE != 0){
                                        return $this->setErrorMessage("A imagem não pode ultrapassar (" . UPLOAD_IMAGE_SIZE . ") bytes.");
                                    }

                                    // Verifica largura
                                    if($tamanhos[0] > UPLOAD_IMAGE_WIDTH && UPLOAD_IMAGE_WIDTH != 0){
                                        return $this->setErrorMessage("Largura da imagem não deve ultrapassar " . UPLOAD_IMAGE_WIDTH . " pixels");
                                    }

                                    // Verifica altura
                                    if($tamanhos[1] > UPLOAD_IMAGE_HEIGHT && UPLOAD_IMAGE_HEIGHT != 0){
                                        return $this->setErrorMessage("Altura da imagem não deve ultrapassar " . UPLOAD_IMAGE_HEIGHT . " pixels");
                                    }
                                    return true;
                                }
                                
                private function UploadImages($arquivos, $diretorio){

                    //se forem várias imagens
                    if(!array_key_exists("tmp_name", $arquivos)){
                        foreach($arquivos as $img){
                            if(!$this->uploadOneImage($img, $diretorio)) {return false;}
                        }
                    }

                    //se for uma imagem
                    elseif(!$this->uploadOneImage($arquivos, $diretorio)) {return false;}

                    return $this->setSuccessMessage("Imagens Enviadas com sucesso!");
                }                                
                
        
                        private function uploadOneImage($img, $diretorio){

                            //gera um nome unico
                            $name = "";
                            $this->prepareImageName($img, $name);
                            $this->setName($this->nome, $name);
                            $dir = $diretorio . $name;

                            //salva todos os thumbs
                            foreach ($this->config['images'] as $thumb){
                                if(false === $this->SaveImage($img, $dir . $thumb['sufix'], $thumb)){
                                    return false;
                                }
                            }
                            return true;
                        }
                        
                                private function prepareImageName($img, &$name){
                                    $name = explode(".", $img['name']);
                                    array_pop($name);
                                    $name = implode(".", $name);
                                    $name = GetPlainName($name);
                                    $name = str_replace(" ", "-", $name);
                                }
                                
                                private function setName($folder, $name){
                                    $file = ($this->img_relative_path ."$folder/$name");
                                    getTrueDir($file);
                                    $temp = str_replace(DS, DS.DS, $file);
                                    $this->paths[]    = $temp;
                                    $this->names[]    = $name;
                                    $this->uploaded[] = str_replace(DS, "/", "$temp.png");
                                }

                                private function SaveImage($imagem, $diretorio, $config){
                                    $obj = $this->loadResizer();
                                    if(!$obj->SaveImage($imagem, $diretorio, $config, $this->extension)){
                                        return $this->setErrorMessage($obj->getErrorMessage());
                                    }
                                    return true;
                                }
                                
                                        private function loadResizer(){
                                            if(is_object($this->resizer)){return $this->resizer;}
                                            $class = RESIZER_PADRAO."Resizer";
                                            require_once  dirname(__FILE__) . "/resizers/".RESIZER_PADRAO."/$class.php";
                                            $this->resizer = new $class();
                                            return $this->resizer;
                                        }
        
        public function drop($diretorio){
            getTrueDir($diretorio);
            if(trim($diretorio) == ""){return true;}
            $this->LoadResource("files/dir", "dir_obj");
            $filedir = DIR_UPLOAD.$diretorio;
            getTrueDir($filedir);
            $file    = str_replace(array('upload'.DS."upload", "uploadupload"),'upload',$filedir);
            if(!is_file($file)){
                $this->prepareDir($diretorio);
                $files = $this->dir_obj->getArquivos($diretorio);
                if(false === $this->checkImageCount($files, $diretorio)){return false;}
                else{$this->removeFiles($diretorio, $files);}
            }else{
                $this->dir_obj->remove($file);
            }
            
            return $this->setSuccessMessage("Imagem removida com sucesso!");
        }
        
                private function prepareDir(&$diretorio){
                    $diretorio = explode(DS, $diretorio);
                    array_pop($diretorio);
                    $diretorio = implode(DS, $diretorio);
                    $diretorio = str_replace(DIR_DEFAULT_UPLOAD, "", $diretorio);
                    $diretorio = DIR_UPLOAD.$diretorio;
                }
                
                private function checkImageCount($files, $diretorio){
                    if(count($files) >= count($this->config['images'])){
                        if(!$this->dir_obj->remove($diretorio)){
                            $this->setErrorMessage($this->dir_obj->getErrorMessage());
                            return false;
                        }
                    }
                    return true;
                }
                
                private function removeFiles($diretorio, $files){
                    $array = explode("/", $diretorio);
                    $find  = end($array);
                    foreach($files as $img){
                        if(strpos($img, $find) === false){continue;}
                        $dirname = str_replace(DIR_DEFAULT_UPLOAD, "", $diretorio);
                        $dirname = DIR_UPLOAD.$dirname;
                        foreach($this->config['img'] as $itemp){
                            $fileName = $dirname . $img['sufix'] . ".png";
                            $this->dir_obj->removeFile($fileName);
                        }
                    }
                }
        
        private function configure(){
            $this->config['efeito'] = array("adaptiveResize");
            $this->config['resize'] = true;
            $this->config['images'] = array(
                'image' => array(
                    'sufix'  => UPLOAD_IMAGE_SUFIX,
                    "height" => UPLOAD_IMAGE_HEIGHT,
                    "width"  => UPLOAD_IMAGE_WIDTH
                ),
                
                'min' => array(
                    'sufix'  => UPLOAD_IMAGE_MIN_SUFIX,
                    "height" => UPLOAD_IMAGE_MIN_HEIGHT,
                    "width"  => UPLOAD_IMAGE_MIN_WIDTH
                ),
                
                'medium' => array(
                    'sufix'  => UPLOAD_IMAGE_MEDIUM_SUFIX,
                    "height" => UPLOAD_IMAGE_MEDIUM_HEIGHT,
                    "width"  => UPLOAD_IMAGE_MEDIUM_WIDTH
                ),
                
                'max' => array(
                    'sufix'  => UPLOAD_IMAGE_MAX_SUFIX,
                    "height" => UPLOAD_IMAGE_MAX_HEIGHT,
                    "width"  => UPLOAD_IMAGE_MAX_WIDTH
                )
            );
        }
}