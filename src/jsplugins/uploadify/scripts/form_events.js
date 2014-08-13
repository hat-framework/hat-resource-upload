
$(document).ready(function() {
    
    $('.album_excluir').live('click', function(){
        blockUI_wait("Enviando solicitação...");
        var url = $(this).attr('href');
        var id  = $(this).attr('id');
        $.ajax({
            url: url,
            dataType: 'json',
            success: function(json) {
                blockUI_unwait();
                if(json.status == 1){
                    $('#'+id).parent().fadeOut(1500);
                    blockUI_success(json.response);
                }else{
                    blockUI_error(json.response);
                }
            },
            error: function(erro){
                blockUI_unwait();
                blockUI_error('Erro na comunicação com o site');
            }
        });
        return false;
    });
    
    $('.album_capa').live('click', function(){
        var url = $(this).attr('href');
        var id  = $(this).attr('id');
        $.ajax({
            url: url,
            dataType: 'json',
            success: function(json) {
                if(json.status == 1){
                    $('a').parent().removeClass('is_capa');
                    $('#'+id).parent().addClass('is_capa');
                    $.blockUI({ 
                        css: {border: 'none', padding: '15px', backgroundColor: '#000', 
                            '-webkit-border-radius': '10px', '-moz-border-radius': '10px', 
                            opacity: .5, color: '#00733C','font-size':'26px'
                        },
                        fadeOut: 400, 
                        message: json.response +"<br/><span class='blockOverlay closemsg'>Fechar(x)</span>"
                    });
                    $('.blockOverlay').attr('title','Clique para fechar').click($.unblockUI); 
                }else{
                    $.blockUI({ 
                        css: {border: 'none', padding: '15px', backgroundColor: '#000', 
                            '-webkit-border-radius': '10px', '-moz-border-radius': '10px', 
                            opacity: .5, color: '#FF3800','font-size':'26px'
                        },
                        fadeOut: 400, 
                        message: json.response + "<br/><span class='blockOverlay closemsg'>Fechar(x)</span>"
                    });
                    $('.blockOverlay').attr('title','Clique para fechar').click($.unblockUI); 
                }
            },
            error: function(erro){
                $.blockUI({ 
                    css: {border: 'none', padding: '15px', backgroundColor: '#000', 
                        '-webkit-border-radius': '10px', '-moz-border-radius': '10px', 
                        opacity: .5, color: '#FF3800','font-size':'26px'
                    },
                    fadeOut: 400, 
                    message: 'Erro na comunicação com o site' + "<br/><span class='blockOverlay closemsg'>Fechar(x)</span>"
                });
                $('.blockOverlay').attr('title','Clique para fechar').click($.unblockUI); 
            }
        });
        return false;
    });

});