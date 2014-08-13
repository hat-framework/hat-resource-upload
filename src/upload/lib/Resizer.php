<?php

/*
 * Esta classe não deve acessar o banco de dados para salvar imagens,
 * isto é feito por outra classe, o model contido neste mesmo diretorio
 */
interface Resizer{
    
    /*
     *  $imagem    = o array de imagens $_FILES, 
     *  $diretorio = diretorio onde serão salvas as imagens
     *  $config    = configurações de imagens. É um array de arrays
     *               Cada subarray possui as seguintes opções de imagem:
     *               width, height, sufix. A classe deve criar uma imagem
     *               com cada subarray
     */
    public function SaveImage($imagem, $diretorio, $config, $extension);
}

?>
