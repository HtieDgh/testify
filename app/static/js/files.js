function files(){
var domain='/testify';
var ext='';//допустимые расщирения принятые от сервера
var chosen={jobj:{img:[],file:[],video:[]},img:[],file:[],video:[]};
$.get(domain+'/editor/files/ext',
    {},
    function(data){
        console.log(data);
        let msg=JSON.parse(data);
        if(!msg['err']){
            ext=msg['ext'];
        }else{
            alert(msg['err_txt'])
        }
    }
);
/*====Загрузка файла=======*/
$( '.upl_fl').click(function(e){
    e.preventDefault();
    let upld_type='';
    let accept='*';
    $('#upld_type').val($(this).attr('href'));
    switch($('#upld_type').val()){
        case 'img':
            upld_type="Загрузка фото";
            accept=ext['IMG'];
            break;
        case 'file':
            upld_type="Загрузка файла";
            accept=ext['FILE'];
            break; 
        case 'video':
            upld_type="Загрузка видео";
            accept=ext['VIDEO'];
            break;
    }
    $('#upl_type_txt').html(upld_type);
    $("#modal_file").modal();
    $("#upload_files").attr('accept',accept );
});

//посмотреть все фото
$('.get_fl').click(function(e){
    e.preventDefault();
    $button=$(this);
    $.get(
        $(this).attr('href'),
        {},
        function(data){
            console.log(data);
            let msg=JSON.parse(data);
            if(!msg['err']){
                $('.'+$button.attr('data-file-scope')+' > .file_wrap').html('')
                msg['files'].forEach(val => {
                    var markdown='';
                    
                    switch ($button.attr('data-file-scope')) {
                        case 'photos_block':
                            markdown='<img class="files_img" alt="Картинка '+val['name']+'" src='+domain+'/'+val['src']+'>';
                            break;
                        case 'files_block':
                            markdown='<p><input class="file_chk" type="checkbox" name="file_url" value="'+domain+'/'+val['src']+'"><a download="" href="'+domain+'/'+val['src']+'"">'+val['name']+'</a></p>';
                            break;
                        case 'video_block':
                            markdown='<p><input class="video_chk" type="checkbox" name="file_url" value="'+domain+'/'+val['src']+'"><a class="video" href="'+domain+'/'+val['src']+'"" target="_blank">'+val['name']+'</a></p>';
                            break;
                    }
                    $('.'+$button.attr('data-file-scope')+' > .file_wrap').append(markdown); 
                    
                });
                $button.hide();
                newEvents();
            }else{
                alert(msg['err_txt'])
            }
        }
    )
})



function deleteFile(){
    if(confirm("Удалить выбраные файлы?")){
        $.ajax({
            url: domain+'/file',
            method: 'DELETE',
            data:JSON.stringify(chosen.img.concat(chosen.file,chosen.video)),
            success: function(data){
                console.log(data);
                let msg=JSON.parse(data);
                if(!msg['err']){
                    chosen.jobj.img.concat(chosen.jobj.file,chosen.jobj.video).forEach(v=>{
                        v.detach();
                    })
                }else{
                    alert(msg['err_txt'])
                }
            }
        });
    }
    
}

/**Конец добавление фото */
function newEvents(){
/*Удаление фото */

    $('.wrap_block img').off('click').click(function(){
        $(this).toggleClass('exposed');
        chosen.img=[];
        chosen.jobj.img=[];
        $('#upld_type').val("")
        /*Заполнение поля span выбраними изображениями для отправки на удаление */
        let chosen_n='';
        let src='';
        $('.exposed').each(function(){
            src=$(this).attr('src');
            chosen.img.push(src);
            chosen.jobj.img.push($(this));
            chosen_n+=', '+src.substring(src.lastIndexOf('/')+1);
        })
        if(chosen.img.length!=0){
            $('#photo_del').css('visibility','visible');
        }else{
            $('#photo_del').css('visibility','hidden');
        }
        
        $("#chosen_ph").html(chosen_n);

    }); 
    $('#photo_del,#file_del,#video_del').off('click').click(function(e){
        e.preventDefault();
        deleteFile();
        $('#photo_del,#file_del,#video_del').css('visibility','hidden');
        $("#chosen_ph,#chosen_fl,#chosen_vd").html('');
    });
/*Конец Удаление фото */
/*Удаление файла */
    $('.file_chk').off('change').change(function () {
        chosen.file=[];
        chosen.jobj.file=[];
        /*Заполнение поля span выбраними изображениями для отправки на удаление */
        let chosen_n='';
        let src='';
        $('.file_chk:checked').each(function(){
            src=$(this).val();
            chosen.file.push(src);
            chosen.jobj.file.push($(this));
            chosen.jobj.file.push($(this).next( "a" ));
            chosen_n+=', '+src.substring(src.lastIndexOf('/')+1);
        })
        if(chosen.file.length!=0){
            $('#file_del').css('visibility','visible');
        }else{
            $('#file_del').css('visibility','hidden');
        }
        $("#chosen_fl").html(chosen_n);
    });
    

    
/*Конец Удаление файла */
/*Удаление видео */
    $('.video_chk').off('change').change(function () {
        chosen.video=[];
        chosen.jobj.video=[];
        let chosen_n='';
        let src='';
        $('.video_chk:checked').each(function(){
            src=$(this).val();
            
            chosen.video.push(src);
            chosen.jobj.video.push($(this));
            chosen.jobj.video.push($(this).next( "a" ));
            chosen_n+=', '+src.substring(src.lastIndexOf('/')+1);
        })
        
        if(chosen.video.length!=0){
            $('#video_del').css('visibility','visible');
        }else{
            $('#video_del').css('visibility','hidden');
        }
        $("#chosen_vd").html(chosen_n);
    });
/*Конец Удаление видео */


 /**картинка на весь экран */
 $('.wrap_block img').off('dblclick').dblclick(function(){
    $('#open_full_img').attr('src',$(this).attr('src'));
    $('#open_full_img').show();
 });
 $('#open_full_img').off('click').click(function(){
    $(this).hide();
    });
}
newEvents();
}
Functions.push(files);