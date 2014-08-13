 <?php

class UploadDbHelper extends UploaderHelper {

 	public function setName(){
		return $this->name;
	}
	
	public function setMime(){
		return $this->mime;
	}
	
	public function setData(){
		return $this->data;
	}
	
	public function setSize(){
		return $this->size;
	}
	
	public function getName($value){
		$this->name = $value;
	}
	
	public function getMime($value){
		$this->mime = $value;
	}
	
	public function getData($value){
		$this->data = $value;
	}
	
	public function getSize($value){
		$this->size = $value;
	}
}
    
?>
