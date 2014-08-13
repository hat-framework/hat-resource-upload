<?php


use classes\Classes\Object;
class uploadImageModel extends classes\Classes\Object{
    
    private $img = array();
    public function __construct() {
        $this->LoadModel("galeria/foto", "gfotos");
    }
    
    public function upload(){
        
        $this->init();        
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
            if(!$this->gfotos->inserir($dados)) $msg[] = $this->gfotos->getErrorMessage();
            
            $where = "`url` = '".$this->paths[$i]."' && `cod_album` = '".$dados['cod_album']."'";
            $this->img[] = $this->gfotos->selecionar(array(), $where);
            $i++;
        }
        
        if(!empty($msg)){ $this->setErrorMessage ("Erro: ".implode("<br/>", $msg));}
        else              $this->setSuccessMessage("Imagem enviada com sucesso!");
        return (empty($msg));
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
            $this->setErrorMessage("A foto indicada não existe ou já foi apagada!");
            return false;
        }

        if(!$this->gfotos->apagar($cod_foto)){
            $this->setMessages($this->gfotos->getMessages());
            return false;
        }
        
        $diretorio = $item['url'];
        $img = UploaderImagesHelper::getInstanceOf();
        if(!$img->drop($diretorio)){
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
        if($fotos == "" || empty ($fotos)) $fotos = array_shift ($this->img);
        $this->LoadComponent('galeria/foto/foto', 'fotos');
        $this->fotos->enableGetUrl();
        return $this->fotos->DrawAlbum($fotos, $cod_album, $print);
    }
    
    private function init(){
        $folder = $_REQUEST['folder'];
        $folder = explode("/", $folder);
        array_shift($folder);
        array_pop($folder);
        $folder = implode("/", $folder);
    
        $this->album     = $_REQUEST['album'];
        $usuario         = $_REQUEST['usuario'];
        $this->upfolder  = "/$folder/$usuario/$this->album/";

        if($this->album    == "") {$this->setErrorMessage("O album não pode ser vazio");   return false;}
        if($usuario        == "") {$this->setErrorMessage("O usuário não pode ser vazio"); return false;}
        if($this->upfolder == "") {$this->setErrorMessage("A pasta não pode ser vazia");   return false;}
    }
    
}


?>
