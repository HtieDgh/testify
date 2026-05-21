function editor(){
////////////////////////////////////////////////////////////////////////////
//записи
$('#confirm_edit_btn').click(function(e){
    $('#editor_form').submit();
});

//вставка в разметку ссылок на файлы 
$('#insert_fl').click(function(e){
    e.preventDefault();
    //вставка фото
    $('.exposed').each(function(){
        let src=$(this).attr('src');
        $('#note_txt').val($('#note_txt').val()+`[img]${src}[/img]`)
    });
    //вставка видео
    $('.video_chk:checked').each(function(){
        let src=$(this).val();
        $('#note_txt').val($('#note_txt').val()+`[video]${src}[/video]`)
    });
    $('.file_chk:checked').each(function(){
        let src=$(this).val();
        const lastSlashIndex = src.lastIndexOf('/');//для получения имени файла
        if (lastSlashIndex === -1) {
            return ; 
        }
        $('#note_txt').val($('#note_txt').val()+`[url=${src}]${src.substring(lastSlashIndex + 1)}[/url]`)
    });
});

}
Functions.push(editor);