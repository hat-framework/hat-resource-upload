<?php

use classes\Classes\Object;
class UploaderHelper extends classes\Classes\Object{

	//arquivo que conterá as fotos ou o array de fotos
        static private $instance;
        private $ext   = "";
        private $paths = array();
        private $mimes = array();
        
        public static function getInstanceOf(){
            $class_name = __CLASS__;
            if (!isset(self::$instance))
                self::$instance = new $class_name;
            return self::$instance;
        }

        public function Upload($arquivos, $diretorio = "", $mimes = ""){
            if($mimes !== ""){
                $this->mimes = is_array($mimes)?$mimes:explode("|", $mimes);
            }
            return ($this->UploadFile($arquivos, $diretorio));
        }

	protected final function UploadFile($arquivos, $diretorio = ""){
            foreach($arquivos as $arquivo){
                if(!$this->ValidaArquivo($arquivo)){return false;}
                if(!$this->SalvaArquivo($arquivo, $diretorio)){return false;}
            }
            return true;
	}

        protected final function SalvaArquivo($arquivo, $diretorio = ""){
            
            if(false === $this->verifyFile($arquivo)){return false;}
            if(false === $this->verifyMimeType($arquivo)){return false;}
            $file = $this->getFileName($diretorio, $arquivo);
            
            // Faz o upload da imagem
            if(!$this->LoadResource('files/dir', 'dobj')->create($diretorio, '')){
                return $this->setErrorMessage($this->dir_obj->getErrorMessage());
            }
            
            if(!move_uploaded_file($arquivo["tmp_name"], $file)){
                return $this->setErrorMessage("Não foi possível mover o arquivo enviado para o diretório de imagens");
            }
            return $this->setSuccessMessage("Arquivo enviado com sucesso!");
	}
        
                private function verifyFile($arquivo){
                    if(empty ($arquivo)){
                        return $this->setErrorMessage("Selecione um arquivo");
                    }
                    if($arquivo['error'] == 4){
                        return $this->setErrorMessage("Arquivo selecionado com erro");
                    }
                    return true;
                }
                
                private function verifyMimeType($arquivo){
                    $mime = $this->LoadResource('files/file', 'fobj')->getMimeType($arquivo["tmp_name"]);
                    $exts = $this->fobj->getExtension($mime, true);
                    $e    = explode("|", UPLOAD_BLOCKED_EXTENSIONS);
                    foreach($exts as $ext){
                        if(!in_array($ext, $e)){continue;}
                        return $this->setErrorMessage(
                            "Arquivo em formato não permitido! O arquivo não pode ser dos tipo: 
                            " . UPLOAD_BLOCKED_EXTENSIONS ."
                            .Envie outro arquivo"
                        );
                    }
                    
                    if(!empty($this->mimes) && !in_array($mime, $this->mimes)){
                        return $this->setErrorMessage(
                            "Erro ao fazer upload! O arquivo que você enviou não é permitido!"
                        );
                    }
                    return true;
                    
                }
        
                private function getFileName(&$diretorio, $arquivo){
                    $e         = explode(".", $arquivo['name']);
                    $this->ext = array_pop($e);
                    $filename  = implode(".",$e);
                    $name      = GetPlainName($filename);
                    
                    
                    $dir        = ($diretorio == "")? DIR_DEFAULT_UPLOAD : $diretorio;
                    $diretorio  = DIR_UPLOAD ."/$dir";
                    $file       = "$diretorio/$name.$this->ext";
                    $i          = 0;
                    while(file_exists($file)){
                        $i++;
                        $file = "$diretorio/{$name}_$i.$this->ext";
                    }
                    $this->paths[]=($i > 0)?"upload/$dir/{$name}_$i".".".$this->ext: "upload/$dir/$name.$this->ext";
                    return $file;
                }
                

        public function getExtension(){
            return $this->ext;
        }
        
        public function getPaths(){
            return $this->paths;
        }
        
        protected function geraNome(){
            return (rand(0, 5) . date('HisdmY'));
        }

        private function ValidaArquivo($arquivo){
            if(empty($arquivo)){
                $this->setErrorMessage("Arquivo não pode ser vazio.");
                return false;
            }
            return true;
        }
}