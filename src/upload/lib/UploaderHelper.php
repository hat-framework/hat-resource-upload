<?php

use classes\Classes\Object;
class UploaderHelper extends classes\Classes\Object{

	//arquivo que conterá as fotos ou o array de fotos
        static private $instance;

        public static function getInstanceOf(){
            $class_name = __CLASS__;
            if (!isset(self::$instance))
                self::$instance = new $class_name;
            return self::$instance;
        }

        public function Upload($diretorio, $arquivos){
            return ($this->UploadFile($diretorio, $arquivos));
        }

	protected final function UploadFile($arquivos, $diretorio, $nome){
            foreach($arquivos as $arquivo){
                if(!$this->ValidaArquivo($arquivo))return false;
                if(!$this->SalvaArquivo($arquivo, $nome, $diretorio))return false;
            }
            return true;
	}

        protected final function SalvaArquivo($arquivo, $nome, $diretorio){
            if(empty ($arquivo)){
                $this->setErrorMessage("Selecione um arquivo");
                return false;
            }
            if($arquivo['error'] == 4){
                $this->setErrorMessage("Arquivo selecionado com erro");
                return false;
            }
            $ext = "." . end(explode(".", $arquivo['name']));

            // Caminho de onde a imagem ficará
            $diretorio = DIR_UPLOAD . (($diretorio == "")? DIR_DEFAULT_UPLOAD : $diretorio) . $nome . $ext;

            // Faz o upload da imagem
            if(!move_uploaded_file($arquivo["tmp_name"], $diretorio)){
                $this->setErrorMessage("O diretório $diretorio não tem permissão de escrita.");
                return false;
            }
            $this->setSuccessMessage("Arquivo enviado com sucesso!");
            return true;
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

?>
