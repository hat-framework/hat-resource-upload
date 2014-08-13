<?php

class uploadifyUploader implements uploader{

	public $file_sample = "sample.php";
    public $project_url = 'http://www.uploadify.com/';
    private $functions = "";

	//carrega os arquivos
	public function load(){
            $this->Html->LoadCssFromPlugins('upify', $this->base_url."scripts/uploadify.css");
            $this->Html->LoadJsFromPlugins ('upify', $this->base_url."scripts/jquery.uploadify.js");
	}
	
	//aplica o uploader no id passado
	public function draw($camp){
        
        if($this->functions == ""){
        	throw new pluginException('uplodify', 'o plugin deve ser configurado!');
        }
        $camp = $this->field_name;
        $this->load();
        
        echo "<div id=\"$camp\" class='uify'>Existe algum problema com o seu javascript</div>
              <div id='$camp"."_msg' class='inf response-msg' style='display:none;'></div>";
        
    }
    
    
    public function configure($field_name = 'upload', $album = "", $usuario = "", $folder = ''){
   
        $this->LoadModel("usuarios/login", 'login');
        $autor = $this->login->getUserId();
    	$this->LoadConfig('upload');
    	static $i = 0;
    	if($field_name == 'upload'){
    		$i++;
    		$field_name.= $i;
    	}
    	$this->field_name = $field_name;
    	$this->functions .= "
                $('#$field_name').fileUpload({
                    'uploader': '".$this->relative_url."scripts/uploader.swf',
                    'cancelImg': '".$this->relative_url."scripts/cancel.png',
                    'script': '".$this->relative_url."scripts/upload.php',
                    'folder': '/$folder/',
                
                    'auto'       : true,
                    'buttonText' : 'Selecionar Imagem',
                    'displayData': 'percentage',
                    'fileDesc'   : 'Envie suas imagens',
                    'fileExt'    : '*.jpg;*.jpeg;*.gif;*.png',
                    'multi'      : true,
                    'method'     : 'post',
                     onComplete: function (evt, queueID, fileObj, response, data) {
                            $('#$field_name"."_msg').text(response).fadeIn('slow');//.delay(1000).fadeOut('slow');
                     },
                     'scriptData'  : {
                            'album':'$album',
                            'usuario':'$usuario',
                            'autor':'$autor',
                            'base_path':'/".PROJECT_NAME."/'
                     },
                     'simUploadLimit' : 1,
                     'wmode'       : 'transparent'
                });";
        $this->Html->LoadJsFunctions($this->functions);
    }

}

?>
