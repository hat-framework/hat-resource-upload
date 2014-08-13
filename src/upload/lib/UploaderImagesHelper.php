<?php

use classes\Classes\Object;
class UploaderImagesHelper extends classes\Classes\Object {

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
        
        private function geraNome(){
            return (rand(0, 5) . date('HisdmY'));
        }
        
        public function Upload($diretorio, $arquivos = array(), $config = NULL){

            $this->img_relative_path = DIR_DEFAULT_UPLOAD . "/". $diretorio;
            $diretorio = DIR_UPLOAD.$diretorio; //diretorio das imagens

            //inicializa as variaveis e faz os testes de sanidade
            if(!$this->VerifyVars($diretorio, $arquivos, $config))return false;

            //valida os arquivos
            if(!$this->Validate($this->arquivo))return false;

            //faz o upload das imagens
            if(!$this->UploadImages($this->arquivo, $this->diretorio))return false;

            return true;
        }
        
        private $uploaded = array();
        private function setName($folder, $name){
            $temp = ($this->img_relative_path ."$folder/$name");
            $temp = str_replace("//", "/", $temp);
            $this->paths[] = $temp;
            $this->names[] = $name;
            $this->uploaded[] = "$temp/$name.png";
        }
        
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


        private function VerifyVars($diretorio, $arquivos, $config){

            //se o arquivo é vazio retorna falso
            if(empty ($arquivos)){
                $this->setErrorMessage("Selecione Um Arquivo.");
                return false;
            }

            //se o diretorio é vazio, enviará os arquivos para o diretorio padrao
            $this->nome = $this->geraNome();
            $diretorio .= $this->nome."/";
            $dir = $diretorio;
            if(!file_exists($dir)){
                $this->LoadResource("files/dir", "dir_obj");
                $dir      = explode("/", $dir);
                $name     = array_pop($dir);
                $name     = ($name == "")? array_pop($dir):$name;
                $location = implode("/",$dir) . "/";
                if(!$this->dir_obj->create($location, $name)){
                    $this->setErrorMessage($this->dir_obj->getErrorMessage());
                    return false;
                }
            }
            
            $this->arquivo   = array_shift($arquivos);
            $this->diretorio = $diretorio;
            return true;
        }
        
        private function Validate($arquivos){
            if(array_key_exists("tmp_name", $arquivos)){
                if(!$this->ValidaImagem($arquivos)) return false;
            }else{
                foreach($arquivos as $imagem) 
                    if(!$this->ValidaImagem($imagem))
                        return false;  
            }
            
            return true;
        }

        private function ValidaImagem($imagem){

            // verifica se imagem possui dimensoes
            $tamanhos = getimagesize( $imagem['tmp_name'] );
            if ( !is_array( $tamanhos ) || empty( $tamanhos ) || $tamanhos === false){
                $this->setErrorMessage("Arquivo enviado não é imagem");
                return false;
            }

            //verifica os tipos da imagem
            $type = $tamanhos['mime'];
            if(strpos($type, "image/")===false){
                $this->setErrorMessage("Arquivo em formato inválido! A imagem deve ser dos tipo: 
                    " . UPLOAD_IMAGE_EXTENSIONS ."
                    .Envie outro arquivo");
                return false;
            }

            if(!$this->config['resize']){

                // Verifica tamanho do arquivo
                if($imagem["size"] > UPLOAD_IMAGE_SIZE){
                    $this->setErrorMessage("A imagem não pode ultrapassar (" . UPLOAD_IMAGE_SIZE . ") bytes.");
                    return false;
                }

                // Verifica largura
                if($tamanhos[0] > UPLOAD_IMAGE_WIDTH){
                    $this->setErrorMessage("Largura da imagem não deve
                                        ultrapassar " . UPLOAD_IMAGE_WIDTH . " pixels");
                    return false;
                }

                // Verifica altura
                if($tamanhos[1] > UPLOAD_IMAGE_HEIGHT){
                    $this->setErrorMessage("Altura da imagem não deve
                                        ultrapassar " . UPLOAD_IMAGE_HEIGHT . " pixels");
                    return false;
                }
            }
            return true;
        }

        private function UploadImages($arquivos, $diretorio){
            
            //se forem várias imagens
            if(!array_key_exists("tmp_name", $arquivos)){
                foreach($arquivos as $img)
                    if(!$this->uploadOneImage($img, $diretorio)) return false;
            }
            
            //se for uma imagem
            elseif(!$this->uploadOneImage($arquivos, $diretorio)) return false;
            
            $this->setSuccessMessage("Imagens Enviadas com sucesso!");
            return true;
        }
        
        private function uploadOneImage($img, $diretorio){

            //gera um nome unico
            $name = explode(".", $img['name']);
            array_pop($name);
            $name = implode(".", $name);
            $name = GetPlainName($name);
            $name = str_replace(" ", "-", $name);

            $this->setName($this->nome, $name);
            $dir = $diretorio . $name;

            //salva todos os thumbs
            foreach ($this->config['images'] as $thumb)
                 if(!$this->SaveImage($img, $dir . $thumb['sufix'], $thumb))
                       return false;
            return true;
        }

        private function SaveImage($imagem, $diretorio, $config){
            $class = RESIZER_PADRAO."Resizer";
            require_once  dirname(__FILE__) . "/resizers/".RESIZER_PADRAO."/$class.php";
            $obj = new $class();
            if(!$obj->SaveImage($imagem, $diretorio, $config, $this->extension)){
                $this->setErrorMessage($obj->getErrorMessage());
                return false;
            }
            return true;
        }
        
        public function drop($diretorio){
            
            $this->LoadResource("files/dir", "dir_obj");
            $diretorio = explode("/", $diretorio);
            array_pop($diretorio);
            $diretorio = implode("/", $diretorio);
            $diretorio = str_replace(DIR_DEFAULT_UPLOAD, "", $diretorio);
            $diretorio = DIR_UPLOAD.$diretorio;
            
            $files = $this->dir_obj->getArquivos($diretorio);
            if(count($files) >= count($this->config['images'])){
                if(!$this->dir_obj->remove($diretorio)){
                    $this->setErrorMessage($this->dir_obj->getErrorMessage());
                    return false;
                }
            }else{
                $find = explode("/", $diretorio);
                $find = end($find);
                foreach($files as $img){
                    if(strpos($img, $find) !== false){
                        $dirname = str_replace(DIR_DEFAULT_UPLOAD, "", $diretorio);
                        $dirname = DIR_UPLOAD.$dirname;
                        foreach($this->config['img'] as $itemp){
                            $fileName = $dirname . $img['sufix'] . ".png";
                            $this->dir_obj->removeFile($fileName);
                        }
                    }
                }
            }
            $this->setSuccessMessage("Imagem removida com sucesso!");
            return true;
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

?>
