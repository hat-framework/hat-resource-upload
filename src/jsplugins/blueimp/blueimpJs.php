<?php

use classes\Classes\JsPlugin;
class blueimpJs extends JsPlugin{
    
    public $file_sample = "index.html";
    public $project_url = 'https://github.com/blueimp/jQuery-File-Upload/';
    
    static private $instance;
    public static function getInstanceOf($plugin){
        $class_name = __CLASS__;
        if (!isset(self::$instance)) self::$instance = new $class_name($plugin);
        return self::$instance;
    } 
    
    public function draw($form, $camp = "", $config = array()){
        $form->append($this->getForm($camp, $config));
    }
    
    public function drawForm($camp, $config = array()){
        echo $this->getForm($camp, $config);
    }
    
    private function getForm($camp, $config){
        extract($config);
        if(!isset($folder)) die(__CLASS__. ":: a variável folder não foi definida");
        $url = "$this->resource_url/lib/fileupload/index.php?folder=$folder";
        
        ob_start();
        include dirname(__FILE__) .'/angular/angular.html';
        $var = ob_get_contents();
        ob_end_clean();
        return str_replace("{{URL}}", $url, $var);
    }

    private $css = array('bootstrap.min', 'style', 'gallery', 'jquery.fileupload', 'jquery.fileupload-ui');
    private $js  = array(
        'jquery-ui/jquery-ui.min', 'blueimp-image-image/js/load-image.min', 'blueimp-canvas-to-blob/js/canvas-to-blob.min', 
        'jquery.blueimp-gallery.min', 
        'jquery.iframe-transport', 'blueimp-file-upload/js/jquery.fileupload', 'blueimp-file-upload/js/jquery.fileupload-process', 
        'blueimp-file-upload/js/jquery.fileupload-image', 
        'blueimp-file-upload/js/jquery.fileupload-audio', 'blueimp-file-upload/js/jquery.fileupload-video', 
        'blueimp-file-upload/js/jquery.fileupload-validate', 'blueimp-file-upload/js/jquery.fileupload-angular');
    public function init(){
        foreach($this->css as $css){
            $this->Html->LoadCss("plugins/blueimp/$css");
        }
        $this->Html->LoadJquery();
        $this->Html->LoadAngular();
        foreach($this->js as $js){
            $this->Html->LoadBowerComponent($js);
        }
    }
    
    public function loadPluginModel(){
        require_once $this->base_path . "/scripts/classes/ImageModel.php";
        return new ImageModel();
    }
}


?>
