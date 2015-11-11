<?php

class uploadifiveJs extends JsUploader{
    
    public $file_sample = "sample.php";
    public $project_url = 'http://www.uploadify.com/';
    private $functions = "";    
    protected $map = array(
        'auto'             => array('name'=> 'auto'            , 'type'=>'bool'     ,'default'=>true),
        'buttonText'       => array('name'=> 'buttonText'      , 'type'=>'text'     ,'default'=>'Selecionar Imagem'),
        'buttonClass'      => array('name'=> 'buttonClass'     , 'type'=>'text'     ,'default'=>'btn btn-danger'),
        'displayData'      => array('name'=> 'displayData'     , 'type'=>'text'     ,'default'=>'percentage'), //'percentage' - 'speed' // 
        'fileDesc'         => array('name'=> 'fileDesc'        , 'type'=>'text'     ,'default'=>'Envie suas imagens'),
        'fileExt'          => array('name'=> 'fileExt'         , 'type'=>'text'     ,'default'=>'*.jpg;*.jpeg;*.gif;*.png'),
        'height'           => array('name'=> 'height'          , 'type'=>'text'     ,'default'=>'30px'),
        'method'           => array('name'=> 'method'          , 'type'=>'text'     ,'default'=>'post'),
        'onUploadComplete' => array('name'=> 'onUploadComplete', 'type'=>'function' ,'default'=>"
            function (response, data, response) {
                var myjson = JSON.parse(data);
                if(typeof(myjson.status !== 'undefined') && myjson.status == 0){
                    if(typeof(myjson.response) !== 'undefined'){blockUI_error(myjson.response);}
                    else{blockUI_error('Falha ao enviar imagem!');}
                }else{
                    $('.uify_img').append(myjson.img);
                }
         }"),
         'onUploadError'=> array(
             'name'=> 'onUploadError', 'type'=>'function','default'=>"function (event,ID,fileObj,errorObj) {
              blockUI_error('Erro ao enviar imagem. Causa do erro: '+errorObj.type + ' Extras: '+errorObj.info);
         }"),
         'onCancel'=> array(
             'name'=> 'onCancel', 'type'=>'function','default'=>"function(event,ID,fileObj,data) {
              blockUI_error('O upload do arquivo ' + fileObj.name + ' foi cancelado!');
         }"),
         'removeCompleted'=> array('name'=> 'removeCompleted', 'type'=>'bool','default'=>true),
         'simUploadLimit' => array('name'=> 'simUploadLimit' , 'type'=>'text','default'=>'1'),
         'width'          => array('name'=> 'width'          , 'type'=>'text','default'=>'120px'),
         'wmode'          => array('name'=> 'wmode'          , 'type'=>'text','default'=>'transparent')
    );
    static private $instance;
    public static function getInstanceOf($plugin){
        $class_name = __CLASS__;
        if (!isset(self::$instance)) {
            self::$instance = new $class_name($plugin);
        }
        return self::$instance;
    } 
    
    public function draw($form, $camp = ""){
        
        if($this->functions == "") {throw new pluginException('uplodify', 'o plugin deve ser configurado!');}
        $camp = $this->field_name; 
        $form->FieldSet("", "Fotos");
        $form->SetCustomCamp($this->getForm($camp));
        $form->CloseFieldSet();
        
    }
    
    public function drawForm($camp){
        echo $this->getForm($camp);
    }
    
    private function getForm($camp){
        $this->LoadJsPlugin("galerias/lightbox", 'lb')->start("{$camp}_img", "#");
        return "<div id='{$camp}_msg' class='inf response-msg' style='display:none;'></div>
              <div id='$camp' class='uify'>Existe algum problema com o seu javascript</div>
              <div id='{$camp}_img' class='uify_img images'></div>";
    }
    
    public function init(){
        $this->LoadJsPlugin('jqueryui/blockui', 'bui'); 
        $baseurl = \classes\Classes\Registered::getResourceLocationUrl('upload')."/src/jsplugins/uploadifive/js";
        $this->Html->LoadJs("$baseurl/jquery.uploadifive.min");
        $this->Html->LoadCss("$baseurl/uploadifive.min");
    }
    
    private $lcbk = '';
    public function setLoadCallback($fn){
        $this->lcbk = ($fn !== "")?", $fn":"";
        return $this;
    }

    public function configure($field_name, $album, $usuario, $folder = 'fotos', $multi = true){
    	static $i = 0;
    	if($field_name == 'upload'){
            $i++;
            $field_name.= $i;
    	}
        
        $diruify_js       = \classes\Classes\Registered::getResourceLocation('upload')."/src/jsplugins/uploadifive/js";
        $foldr            = base64_encode($folder);
        $options          = $this->getOptions();
        $multi            = ($multi == true)?"true":"false";
        $loadurl          = $this->resource_url . "/src/lib/actions/load.php?album=$album";
        $upload_script    = $this->resource_url_relative."src/lib/upload.php?album=$album&usuario=$usuario&folder=$foldr";
    	$this->field_name = $field_name;
        getTrueUrl($upload_script);
        getTrueUrl($loadurl);
    	$this->functions  = "
            $('.uify_img').load('$loadurl' $this->lcbk );
            $('.uify').uploadifive({
                'uploadScript': '/$upload_script',
                'cancelImg'   : '/$diruify_js/uploadifive-cancel.png',
                'script'      : '/$upload_script',
                'multi'       : $multi,
                $options
            });";
        $this->Html->LoadJQueryFunction($this->functions);
        return $this;
    }
    
    public function loadPluginModel(){
        require_once $this->base_path . "/scripts/classes/ImageModel.php";
        return new ImageModel();
    }
}