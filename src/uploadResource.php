<?php

/*
 * jQuery File Upload Plugin PHP Class 5.11.2
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

if(!function_exists('array_replace_recursive')){
    function array_replace_recursive($first, $second){
        foreach($second as $nam=>$opt){
            if(array_key_exists($nam, $first)){
                if(!is_array($opt)){
                    $first[$nam] = $opt;
                }else{
                    $first[$nam] = array_replace_recursive($first[$nam], $opt);
                }
            }
        }
        return $first;
    }
}

class UploadResource extends \classes\Interfaces\resource
{
    protected $options;
    private $upload_folder = '';
    
    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!is_object(self::$instance))self::$instance = new $class_name();
        return self::$instance;
    }
    
    public function getDownloadUrl(){
        return URL_RESOURCES ."upload/lib/fileupload/download.php";
    }
    
    public function getDeleteUrl($cod_arquivo, $folder){
        return URL_RESOURCES ."upload/lib/fileupload/delete.php?file=$cod_arquivo&folder=$folder";
    }
    
    public function setOptions($options=null) {
        $this->upload_folder = DIR_FILES_RELATIVE."/files/";
        //print_r($options);
        $folder = array_key_exists('folder', $options)? $options['folder'] . "/":"";
        $this->folder = $folder; 
        $this->relative_path = "files/$folder";
        $this->LoadModel('files/pasta', 'pobj');
        $this->folder_cod = $this->pobj->getCodFolder($folder);
        if($this->folder_cod == 0){
            die(json_encode(array('error' => "Pasta $folder não pode ser criada! ". $this->pobj->getErrorMessage())));
        }
        $url = $this->getFullUrl();
        $this->options = array(
            'folder'     => $folder,
            'script_url' => $url.'/',
            'upload_dir' => DIR_FILES.$this->relative_path,
            'upload_url' => URL_FILES.$this->relative_path,
            'param_name' => 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'deleteType' => 'DELETE',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/.+$/i',
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to true to rotate images based on EXIF meta data, if available:
            'orient_image' => false,
            'image_versions' => array(
                // Uncomment the following version to restrict the size of
                // uploaded images. You can also add additional versions with
                // their own upload directories:
                /*
                'large' => array(
                    'upload_dir' => DIR_FILES.$this->relative_path."max/",
                    'upload_url' => URL_FILES.$this->relative_path."max/",
                    'max_width' => 1920,
                    'max_height' => 1200,
                    'jpeg_quality' => 95
                ),
                */
                'thumbnail' => array(
                    'upload_dir' => "thumb/",
                    'upload_url' => URL_FILES.$this->relative_path."thumb/",
                    'max_width' => 80,
                    'max_height' => 80
                )
            )
        );
        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }

        if(!file_exists($this->options['upload_dir'])){
            $this->LoadResource('files/dir', 'dir');
            if(!$this->dir->create($this->options['upload_dir'], "")){
                die($this->dir->getErrorMessage());
            }
        }
        
        foreach($this->options['image_versions'] as $arr){
            $dir = $this->getDirOfThumb($arr);
            if(!file_exists($dir)){
                $this->LoadResource('files/dir', 'dir');
                if(!$this->dir->create($dir, "")){
                    die($this->dir->getErrorMessage());
                }
            }
        }
    }
    
    
     public function get() {

        $this->LoadModel('files/arquivo', 'aobj');
        $var = $this->aobj->getFilesByFolder($this->folder_cod);
        
        $out = array();
        //print_r($var);
        $image = array('jpg', 'png', 'gif');
        foreach($var as $i => $v){
            $out[$i]['name'] = $v['file_label'];
            $out[$i]['size'] = "Tamanho: ". files_arquivoModel::convertSizeUnity($v['size']);
            $out[$i]['ext']  = $v['ext'];
            $out[$i]['url']  = $this->options['script_url']."download.php?file=".$v['cod_arquivo']."&folder=$this->folder";
            if(in_array($v['ext'], $image))
                $out[$i]['thumbnailUrl'] = $this->options['upload_url']."thumb/".$v['name'];
            $out[$i]['deleteType'] = "DELETE";
            $out[$i]['deleteUrl']  = $this->getDeleteUrl($v['cod_arquivo'], $this->folder);
            
        }
        header('Content-type: application/json');
        echo json_encode($out);
        return;
    }

    public function post() {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete();
        }
        $upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
        $info = array();
        if ($upload && is_array($upload['tmp_name'])) {
            // param_name is an array identifier like "files[]",
            // $_FILES is a multi-dimensional array:
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    isset($_SERVER['HTTP_X_FILE_NAME']) ?
                        $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                        $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                        $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $upload['error'][$index],
                    $index
                );
            }
        } elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            // param_name is a single object identifier like "file",
            // $_FILES is a one-dimensional array:
            $info[] = $this->handle_file_upload(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ?
                        $upload['name'] : null),
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ?
                        $upload['size'] : null),
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ?
                        $upload['type'] : null),
                isset($upload['error']) ? $upload['error'] : null
            );
        }
        
        header('Vary: Accept');
        $json = json_encode($info);
        $redirect = isset($_REQUEST['redirect']) ? stripslashes($_REQUEST['redirect']) : null;
        if ($redirect) {
            header('Location: '.sprintf($redirect, rawurlencode($json)));
            return;
        }
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else header('Content-type: text/plain');
        echo $json;
    }

    public function delete($fileid) {
        
        $this->LoadModel('files/arquivo', 'arq');
        $item = $this->arq->getItem($fileid);
        if(empty ($item)){
            echo "arquivo não encontrado!";
            return;
        }
        //print_r($this->options);
        
        if(!$this->arq->apagar($fileid)){
            $success['error'] = $this->obj->getErrorMessage();
        }
        
        $file = DIR_FILES . $item['url'].$item['name'];
        if(!file_exists($file)){return true;}

        $file_name = $item['name'];
        $success = is_file($file) && unlink($file);
        if ($success) {
            foreach($this->options['image_versions'] as $version => $options) {
                $file = DIR_FILES . $item['url'].$options['upload_dir'].$file_name;
                if (is_file($file)) unlink($file);
            }
        }

        header('Content-type: application/json');
        echo json_encode($success);
    }
    
    public function download($fileid){
        $this->LoadModel('files/arquivo', 'arq');
        $item = $this->arq->getItem($fileid);
        if(empty ($item)){
            echo "arquivo não encontrado!";
            return;
        }
        $file = DIR_FILES . $item['url'].$item['name'];
        if(!file_exists($file)){
            echo "arquivo não encontrado!";
            return;
        }

        // We'll be outputting a PDF
        $tam = filesize($file);
        header("Content-Type: application/save");
        //header('Content-type: application/pdf');
        header("Content-Length: $tam");
        header('Content-Disposition: attachment; filename="' . $item['name'] . '"'); 
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0'); 
        header('Pragma: no-cache'); 
        readfile($file);
        //$fp = fopen("$file", "r"); 
        //fpassthru($fp); 
        //fclose($fp);
    }
    
     public function apagar($folder){
        $dir = DIR_FILES."/files/$folder";
        if(!file_exists($dir)) return true;
        
        $this->LoadResource('files/dir', 'dir');
        if(!$this->dir->remove($dir)) {
            $this->setMessages($this->dir->getMessages());
            return false;
        }
        
        if(file_exists($dir)){
            $this->setErrorMessage("Diretório $dir não foi apagado!");
            return false;
        }
        return true;
    }

    public function getFullUrl() {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
      	return
    		($https ? 'https://' : 'http://').
    		(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		($https && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function set_file_deleteUrl($file) {
        $file->deleteUrl = $this->options['script_url']
            .'?file='.rawurlencode($file->name)."&folder=".$this->options['folder'];
        $file->deleteType = $this->options['deleteType'];
        if ($file->deleteType !== 'DELETE') {
            $file->deleteUrl .= '&_method=DELETE';
        }
    }

    protected function get_file_object($file_name) {
        $file_path = $this->options['upload_dir'].$file_name;
        if (is_file($file_path) && $file_name[0] !== '.') {
            $ext = explode(".", $file_name);
            $ext = end($ext);
            $file = new stdClass();
            $file->name = $file_name;
            $file->size = filesize($file_path);
            $file->ext  = $ext;
            $file->url  = $this->options['script_url']."download.php?folder=".
                    $this->options['folder'].
                    "&file=".rawurlencode($file->name);
            //$file->url = $this->options['upload_url'].rawurlencode($file->name);
            foreach($this->options['image_versions'] as $version => $options) {
                $dir = $this->getDirOfThumb($options);
                if (is_file($dir.$file_name)) {
                    $file->{$version.'_url'} = $options['upload_url']
                        .rawurlencode($file->name);
                }
            }
            $this->set_file_deleteUrl($file);
            return $file;
        }
        return null;
    }

    protected function get_file_objects() {
        if(!file_exists($this->options['upload_dir'])){
            $this->LoadResource("files/dir", 'dobj');
            $this->dobj->create($this->options['upload_dir'], "");
        }
        return array_values(array_filter(array_map(
            array($this, 'get_file_object'),
            scandir($this->options['upload_dir'])
        )));
    }

    protected function create_scaled_image($file_name, $options) {
        $file_path = $this->options['upload_dir'].$file_name;
        $new_file_path = $this->getDirOfThumb($options) . $file_name;
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }
        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height
        );
        if ($scale >= 1) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                $src_img = null;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path, $image_quality);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }

    protected function validate($uploaded_file, $file, $error, $index) {
        if ($error) {
            $file->error = $error;
            return false;
        }
        if (!$file->name) {
            $file->error = 'missingFileName';
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = 'acceptFileTypes';
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            $file->error = 'maxFileSize';
            return false;
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            $file->error = 'minFileSize';
            return false;
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            $file->error = 'maxNumberOfFiles';
            return false;
        }
        list($img_width, $img_height) = @getimagesize($uploaded_file);
        if (is_int($img_width)) {
            if ($this->options['max_width'] && $img_width > $this->options['max_width'] ||
                    $this->options['max_height'] && $img_height > $this->options['max_height']) {
                $file->error = 'maxResolution';
                return false;
            }
            if ($this->options['min_width'] && $img_width < $this->options['min_width'] ||
                    $this->options['min_height'] && $img_height < $this->options['min_height']) {
                $file->error = 'minResolution';
                return false;
            }
        }
        return true;
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function trim_file_name($name, $type, $index) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Add missing file extension for known image types:
        if (strpos($file_name, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $file_name .= '.'.$matches[1];
        }
        if ($this->options['discard_aborted_uploads']) {
            while(is_file($this->options['upload_dir'].$file_name)) {
                $file_name = $this->upcount_name($file_name);
            }
        }
        return $file_name;
    }

    protected function handle_form_data($file, $index) {
        // Handle form data, e.g. $_REQUEST['description'][$index]
    }

    protected function orient_image($file_path) {
      	$exif = @exif_read_data($file_path);
        if ($exif === false) {
            return false;
        }
      	$orientation = intval(@$exif['Orientation']);
      	if (!in_array($orientation, array(3, 6, 8))) {
      	    return false;
      	}
      	$image = @imagecreatefromjpeg($file_path);
      	switch ($orientation) {
        	  case 3:
          	    $image = @imagerotate($image, 180, 0);
          	    break;
        	  case 6:
          	    $image = @imagerotate($image, 270, 0);
          	    break;
        	  case 8:
          	    $image = @imagerotate($image, 90, 0);
          	    break;
          	default:
          	    return false;
      	}
      	$success = imagejpeg($image, $file_path);
      	// Free up memory (imagedestroy does not delete files):
      	@imagedestroy($image);
      	return $success;
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null) {
        $file = new stdClass();
        $file->name = $this->trim_file_name($name, $type, $index);
        $file->size = intval($size);
        $file->type = $type;
        if ($this->validate($uploaded_file, $file, $error, $index)) {
            $this->handle_form_data($file, $index);
            $file_path = $this->options['upload_dir'].$file->name;
            $append_file = !$this->options['discard_aborted_uploads'] &&
                is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = filesize($file_path);
            if ($file_size === $file->size) {
            	if ($this->options['orient_image']) {
            		$this->orient_image($file_path);
            	}
                $file->url = $this->options['upload_url'].rawurlencode($file->name);
                foreach($this->options['image_versions'] as $version => $options) {
                    $dir = $this->getDirOfThumb($options);
                    if ($this->create_scaled_image($file->name, $options)) {
                        if ($this->options['upload_dir'] !== $dir) {
                            $file->{$version.'_url'} = $options['upload_url']
                                .rawurlencode($file->name);
                        } else {
                            clearstatcache();
                            $file_size = filesize($file_path);
                        }
                    }
                }
            } else if ($this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = 'abort';
            }
            $file->size = $file_size;
            $this->set_file_deleteUrl($file);
            if(!property_exists($file, 'error')){
                $ext = explode(".", $file->name);
                $post['folder']     = $this->folder_cod;
                $post['ext']        = array_pop($ext);
                $post['name']       = $file->name;
                $post['file_label'] = implode(".",$ext);
                $post['url']        = $this->relative_path;
                $post['size']       = $file->size;
                $post['type']       = $file->type;
                
                    
                $this->LoadModel('files/arquivo', 'obj');
                if(!$this->obj->inserir($post)){
                    $file->error = $this->obj->getErrorMessage();
                }
                $file->size = "Tamanho: ".files_arquivoModel::convertSizeUnity($file->size);
                $item = $this->obj->getFileByFolderAndName($this->folder_cod, $file->name);
                $file->deleteType = "DELETE";
                $file->deleteUrl = $this->options['script_url']."delete.php?file=".$item['cod_arquivo']."&folder=$this->folder";
            }
        }
        return $file;
    }
    
    private function getDirOfThumb($options){
        return DIR_FILES.$this->relative_path.$options['upload_dir'];
    }

    private function zip(){
        if (!extension_loaded('zip')) return;

        $dir = dirname(__FILE__)."/";
        $zip = new Zip();
        $zip->open($dir . "arquivo.zip", ZIP::CREATE);
        $zip->addfile($dir . "nome_do_arquivo.extensao", "nome_do_arquivo.extensao");
        $zip->close();
    }
    
    private function unzip($filename){
        if (!extension_loaded('zip')) return;
        
        $zip = new Zip();
        $zip->open($filename);
        $zip->extractTo("nome_dir");
        $zip->close();
    }

}
/*
$msg = '<br>Arquivo não existe.';
if (isset($_GET['id'])){
    $id = $_GET['id']; // Pega o ID do arquivo para comparar com a array 

    // Lista com os endereços
    $d[1] = 'dvd-to-avi.exe';
    $d[2] = 'index.php';
    $d[3] = 'musica.mp3';

    // Loop para ler o atributo de 'id' e transformar na variável 'file'.
    if(array_key_exists($id, $d)){
        $file = $d[$id];
        $tam = filesize($file);
        
        // Lista de Headers para preparar a página
        header("Content-Type: application/save");
        header("Content-Length: $tam");
        header('Content-Disposition: attachment; filename="' . $file . '"'); 
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0'); 
        header('Pragma: no-cache'); 

        // Lê e evia o arquivo para download
        $fp = fopen("$file", "rb"); 
        fpassthru($fp); 
        fclose($fp); 
        $msg = '';
    }
}
echo $msg; */