<?php

class uploadImageModel extends classes\Classes\Object{
    
    private $img = array();
    public function __construct() {
        $this->LoadModel("galeria/foto" , "gfotos");
    }
    
    public function upload(){
        
        if(false === $this->init()){return false;}
        
        if(false === $this->uploadImage() && false === $this->uploadFile()){return false;}
        
        $msg                = array();
        $i                  = 0;
        $len                = count($this->paths);
        $dados              = array();
        $dados['cod_album'] = $this->album;
        $dados['ext']       = $this->ext;
        $this->extension    = $this->ext;
        $this->img          = array();
        while($i < $len){
            $dados['url']   = $this->paths[$i];
            if(false === $this->gfotos->inserir($dados)) {$msg[] = $this->gfotos->getErrorMessage();}
            $where = $this->getWhere($dados, $i);
            $data  = $this->gfotos->selecionar(array(), $where, '1');

            $i++;
            if(empty($data)){
                        $this->gfotos->db->printSentenca();
                continue;
                
            }
            $this->img[] = array_shift($data);
        }
        if(!empty($msg)){ return $this->setErrorMessage ("Erro: ".implode("<br/>", $msg));}
        return $this->setSuccessMessage("Imagem enviada com sucesso!");
    }
    
            private function getWhere($dados, $i){
                $where   = array();
                $url     = str_replace("//",'/', $this->paths[$i]);
                $url2    = $url;
                getTrueUrl($url);
                getTrueDir($url2);
                $where[] = "`url` = '$url'  AND `cod_album` = '".$dados['cod_album']."'";
                $where[] = "`url` = '$url2' AND `cod_album` = '".$dados['cod_album']."'";
                
                $url     = str_replace("/",'//', $url);
                $url2    = str_replace("\\",'\\\\', $url2);;
                $where[] = "`url` = '$url'  AND `cod_album` = '".$dados['cod_album']."'";
                $where[] = "`url` = '$url2' AND `cod_album` = '".$dados['cod_album']."'";
                return implode(" OR ", $where);
            }
    
            private function uploadImage(){
                $img = UploaderImagesHelper::getInstanceOf();
                if(false === $img->Upload($this->upfolder, $_FILES)){
                    return $this->setErrorMessage ($img->getErrorMessage());
                }
                $this->paths = $img->getPaths();
                $this->ext   = $img->getExtension();
                return true;
            }
            
            private function uploadFile(){
                $file = UploaderHelper::getInstanceOf();
                if(false === $file->Upload($_FILES, "$this->upfolder")){
                    return $this->setErrorMessage ($file->getErrorMessage());
                }
                $this->paths = $file->getPaths();
                $this->ext   = $file->getExtension();
                return true;
            }
    
    public function getUrl($cod_foto, $size = "max"){
        $this->LoadModel("galeria/foto", 'foto');
        $foto = $this->foto->getItem($cod_foto);

        $this->LoadComponent('galeria/foto/foto', 'fotos');
        return $this->fotos->getUrl($foto, $size);
    }
    
    public function load($cod_album){
        return $this->gfotos->getAlbum($cod_album);
    }
    
    public function apagar($cod_foto){
        $img       = UploaderImagesHelper::getInstanceOf();
        $item      = $this->gfotos->getItem($cod_foto);
        if(empty($item)){
            return $this->setErrorMessage("A foto indicada não existe ou já foi apagada!");
        }

        $diretorio = $item['url'];
        if(false === $this->gfotos->apagar($cod_foto)){
            $img->drop($diretorio);
            $this->setMessages($this->gfotos->getMessages());
            return false;
        }
        
        if(false === $img->drop($diretorio)){
            $this->setAlertMessage("Foto excluída do banco de dados mas não apagada na pasta");
            return false;
        }

        $this->setMessages($this->gfotos->getMessages());
        return true;
    }
    
    public function setCapa($cod_foto){
        $item      = $this->gfotos->getItem($cod_foto);
        $cod_album = array_shift($item['cod_album']);
        $this->LoadModel('galeria/album', 'galbum');
        $bool = $this->galbum->setCapa($cod_album, $cod_foto);
        $this->setMessages($this->galbum->getMessages());
        return $bool;
    }
    
    public function getCapa($cod_album){
        $this->LoadModel('galeria/album', 'galbum');
        return $this->galbum->getCapa($cod_album);
    }
    
    public function getImagesPath(){
        return $this->paths;
    }
    
    public function getExtension(){
        return $this->extension;
    }
    
    public function getImgFromDatabase(){
        return $this->img;
    }
    
    public function draw($fotos = "", $cod_album = '', $print = true){
        if($fotos == "" || empty ($fotos)) {$fotos = array_shift ($this->img);}
        $this->LoadComponent('galeria/foto/foto', 'fotos');
        $this->fotos->enableGetUrl();
        return $this->fotos->DrawAlbum($fotos, $cod_album, $print);
    }
    
    public function drawPicture($print = true){
        $foto = array_shift($this->img);
        $this->LoadComponent('galeria/foto/foto', 'fotos');
        return $this->fotos->DrawPicture($foto, $print);
    }
    
    private function init(){
        $folder      = "";
        $usuario     = isset($_REQUEST['usuario'])?$_REQUEST['usuario']:"";
        $this->album = isset($_REQUEST['album'])?$_REQUEST['album']:"";
        if(isset($_REQUEST['folder'])){
            $folder = base64_decode($_REQUEST['folder']);
            if(false === $folder){$folder = $_REQUEST['folder'];}
            $this->upfolder  = "/$folder/$usuario/$this->album/";
        }
        $bool= true;
        if($this->album    == "") {$this->appendErrorMessage("O album não pode ser vazio");   $bool= false;}
        if($this->upfolder == "") {$this->appendErrorMessage("A pasta não pode ser vazia");   $bool= false;}
        if($usuario        == "") {$this->appendErrorMessage("O usuário não pode ser vazio"); $bool= false;}
        return $bool;
    }
    
}