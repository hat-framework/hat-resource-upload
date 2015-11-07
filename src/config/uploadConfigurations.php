<?php
        
class uploadConfigurations extends \classes\Classes\Options{
                
    protected $files   = array(
        
        'upload/upload' => array(
            'title'        => 'Opções do Upload de Imagens',
            'descricao'    => 'Configure as opções para upload de imagens',
            'grupo'        => 'Upload',
            'type'         => 'resource', //config, plugin, jsplugin, template, resource
            'referencia'   => 'upload/upload',
            'visibilidade' => 'webmaster', //'usuario', 'admin', 'webmaster'
            'configs'      => array(
                'UPLOAD_IMAGE_SIZE' => array(
                    'name'          => 'UPLOAD_IMAGE_SIZE',
                    'label'         => 'Tamanho da imagens (em KB)',
                    'type'          => 'varchar',//varchar, text, enum
                    'default'       => '2013766',
                    'value'         => '2013766',
                    'value_default' => '2013766'
                ),
                'UPLOAD_IMAGE_WIDTH' => array(
                    'name'          => 'UPLOAD_IMAGE_WIDTH',
                    'label'         => 'Largura máxima da Imagem',
                    'description'   => 'Largura Máxima (em pixels) da imagem que será salva no banco de dados',
                    'type'          => 'varchar',//varchar, text, enum
                    'default'       => '1336',
                    'value'         => '1336',
                    'value_default' => '1336'
                ),
                'UPLOAD_IMAGE_HEIGHT' => array(
                    'name'          => 'UPLOAD_IMAGE_HEIGHT',
                    'label'         => 'Altura máxima da Imagem',
                    'description'   => 'Altura Máxima (em pixels) da imagem que será salva no banco de dados',
                    'type'          => 'varchar',//varchar, text, enum
                    'default'       => '1336',
                    'value'         => '1336',
                    'value_default' => '1336'
                ),
            ),
        ),
    );
}