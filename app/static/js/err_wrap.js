function err_wrap(){
//==== Отображение текста ошибки от сервера если она есть ====
if($('#exept_txt').html()!=''){
    $('#err_wrap').modal();
}
}
Functions.push(err_wrap);