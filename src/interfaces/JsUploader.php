<?php

abstract class JsUploader extends \classes\Classes\JsPlugin{
    protected $map     = array();
    protected $options = array();

    public function setOption($name, $value){
        if(!isset($this->map[$name])){return $this;}
        $this->options[$this->map[$name]['name']] = $value;
        return $this;
    }
    
    protected function getOptions(){
        if(empty($this->map)){return "";}
        $out = array();
        foreach($this->map as $key => $val){
            if(!isset($val['default'])){continue;}
            $tempval = isset($this->options[$key])?$this->options[$key]:$val['default'];
            $value   = $this->getOptionValue($key, $tempval);
            $out[$key] = "'$key':$value";
        }
        return implode(", ", $out);
    }
    
            private function getOptionValue($key, $value){
                if(!isset($this->map[$key]['type'])){return $value;}
                if($this->map[$key]['type'] == 'text'){return "'".$value."'";}
                if($this->map[$key]['type'] == 'bool'){ return ($value === false)?'false':'true';}
                return $value;
            }
}