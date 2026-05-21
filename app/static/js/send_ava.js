function send_ava(){
//клик на Отправить Фото
var file;
$("#user_ava").on("change",function(){
    file=$(this)[0].files[0];
    if(!!file){//конвертация в bool
        $("#send_ava_btn").show();
    }else{
        $("#send_ava_btn").hide();
    }
});
$("#send_ava_btn").click(function(e){
    e.preventDefault();
    let form_data = new FormData();
    form_data.append('user_ava', file);
    $.ajax({
        url: $(this).attr('href'),
        type: 'POST',
        data: form_data,
        cache: false,
        dataType: 'text',
        processData: false, // Не обрабатываем файлы (Don't process the files)
        contentType: false,
        success: function( data, textStatus, jqXHR ){
            console.log(data);
            let msg=JSON.parse(data);
            if(msg.err==false){
                $('.ava_img img').attr("src",msg.new_ava_url);
                $("#send_ava_btn").hide();
            }else{
                $('#err_wrap').modal();
                $('#exept_txt').html(msg.err_txt);
            }
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.log([jqXHR, textStatus, errorThrown]);
        }
    });
       
});
}
Functions.push(send_ava);