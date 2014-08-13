<?php

interface uploader{

	//carrega os arquivos
	public function load();
	
	//aplica o uploader no id passado
	public function draw($id);
	
}

?>
