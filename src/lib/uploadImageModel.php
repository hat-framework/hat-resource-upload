<?php


use classes\Classes\Object;
class uploadImageModel extends classes\Classes\Object{
    
    private $img = array();
    public function __construct() {
        $this->LoadModel("galeria/foto" , "gfotos");
    }
    
    public function upload(){
        
        if(false === $this->init()){return false;}
        $img    = UploaderImagesHelper::getInstanceOf();
        if(!$img->Upload($this->upfolder, $_FILES)){
            $this->setErrorMessage ($img->getErrorMessage());
            return false;
        }
        
        $this->paths        = $img->getPaths();
        $ext                = $img->getExtension();
        $msg                = array();
        $i                  = 0;
        $len                = count($this->paths);
        $dados              = array();
        $dados['cod_album'] = $this->album;
        $dados['ext']       = $ext;     
        
        $this->extension    = $ext;
        $this->img          = array();
        while($i < $len){
            $dados['url']   = $this->paths[$i];
            if(false === $this->gfotos->inserir($dados)) {$msg[] = $this->gfotos->getErrorMessage();}
            
            $where = "`url` = '".$this->paths[$i]."' && `cod_album` = '".$dados['cod_album']."'";
            $data  = $this->gfotos->selecionar(array(), $where, '1');
            if(empty($data)){continue;}
            $this->img[] = array_shift($data);
            $i++;
        }
        if(!empty($msg)){ return $this->setErrorMessage ("Erro: ".implode("<br/>", $msg));}
        return $this->setSuccessMessage("Imagem enviada com sucesso!");
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
        
        $item = $this->gfotos->getItem($cod_foto);
        if(empty($item)){
            return $this->setErrorMessage("A foto indicada não existe ou já foi apagada!");
        }

        if(false === $this->gfotos->apagar($cod_foto)){
            $this->setMessages($this->gfotos->getMessages());
            return false;
        }
        
        $diretorio = $item['url'];
        $img = UploaderImagesHelper::getInstanceOf();
        if(false === $img->drop($diretorio)){
            return $this->setAlertMessage("Foto excluída do banco de dados mas não apagada na pasta");
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

