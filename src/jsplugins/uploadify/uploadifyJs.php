<?php

use classes\Classes\JsPlugin;
class uploadifyJs extends JsPlugin{
    
    public $file_sample = "sample.php";
    public $project_url = 'http://www.uploadify.com/';
    private $functions = "";
    
    static private $instance;
    public static function getInstanceOf($plugin){
        $class_name = __CLASS__;
        if (!isset(self::$instance)) {
            self::$instance = new $class_name($plugin);
        }

        return self::$instance;
    } 
    
    public function draw($form, $camp = ""){
        
        if($this->functions == "") throw new pluginException('uplodify', 'o plugin deve ser configurado!');
        $camp = $this->field_name; 
        $form->FieldSet("", "Fotos");
        $form->SetCustomCamp($this->getForm($camp));
        $form->CloseFieldSet();
        
    }
    
    public function drawForm($camp){
        echo $this->getForm($camp);
    }
    
    private function getForm($camp){
        return "<div id='$camp"."_msg' class='inf response-msg' style='display:none;'></div>
              <div id=\"$camp\" class='uify'>Existe algum problema com o seu javascript</div>
              <div id='$camp"."_img' class='uify_img images'></div>";
    }
    
    public function init(){
        $this->LoadJsPlugin('jqueryui/blockui', 'bui'); 
        $this->Html->LoadExternCss($this->url."/css/uploadify");
        $this->Html->LoadExternCss($this->url."/css/form");
        $this->Html->LoadJs($this->url."/scripts/jquery.uploadify", true);
        $this->Html->LoadJs($this->url."/scripts/form_events");
    }
    
    public function configure($field_name, $album, $usuario, $folder = 'fotos', $multi = true){
   
    	static $i = 0;
    	if($field_name == 'upload'){
    		$i++;
    		$field_name.= $i;
    	}
        $multi   = ($multi == true)?"true":"false";
        $loadurl = $this->resource_url . "/lib/actions/load.php?album=$album";
    	$this->field_name = $field_name;

    	$this->functions  = "
                $('.uify_img').load('$loadurl');
                $('.uify').fileUpload({
                    'uploader': '".$this->url_relative."/scripts/uploader.swf',
                    'cancelImg': '".$this->url_relative."/scripts/cancel.png',
                    'script': '".$this->resource_url_relative."/lib/upload.php',
                    'folder': '/$folder/',
                
                    'auto'       : true,
                    'buttonText' : 'Selecionar Imagem',
                    'displayData': 'percentage', //'percentage' - 'speed' // 
                    'fileDesc'   : 'Envie suas imagens',
                    'fileExt'    : '*.jpg;*.jpeg;*.gif;*.png',
                    'multi'      : $multi,
                    'method'     : 'post',
                     onComplete: function (evt, queueID, fileObj, response, data) {
                            var myjson = JSON.parse(response);
                            if(myjson.status == 0){
                                blockUI_error(myjson.response);
                            }else{
                                $('.uify_img').append(myjson.img);
                            }
                     },
                     'onError': function (event,ID,fileObj,errorObj) {
                          blockUI_error('Erro ao enviar imagem. Causa do erro: '+errorObj.type + ' Extras: '+errorObj.info);
                     },
                     'onCancel': function(event,ID,fileObj,data) {
                          blockUI_error('O upload do arquivo ' + fileObj.name + ' foi cancelado!');
                     },
                     'scriptData'  : {
                            'album':'$album',
                            'usuario':'$usuario',
                            'base_path':'/".PROJECT."/'
                     },
                     'simUploadLimit' : 1,
                     'wmode'       : 'transparent'
                });";
        $this->Html->LoadJQueryFunction($this->functions);
    }
    
    public function loadPluginModel(){
        require_once $this->base_path . "/scripts/classes/ImageModel.php";
        return new ImageModel();
    }
}


?>
