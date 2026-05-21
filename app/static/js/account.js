function account(){
    //удаление пользователя
    $('.delete_user_btn_js').click(function(e){
        e.preventDefault();
        $userLine=$(this).parent().parent().parent().parent()
        $.ajax({
            url: $(this).attr('href'),
            method: 'DELETE',
            data:'',
            success: function(data){
                console.log(data);
                let msg=JSON.parse(data);
                if(!msg.err){
                    $userLine.detach();
                }else{
                    $('#err_wrap').modal();
                    $('#exept_txt').html(msg.err_txt);                    
                }
            }
        });
    })
}
Functions.push(account);