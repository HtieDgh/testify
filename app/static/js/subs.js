
function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
  }
function subs(){
    var domain='/testify';
//Кнопка Подписаться
$('.a_sub_btn').click(function(e){
    e.preventDefault();
    let id_str_btn=$(this);
    let id=id_str_btn.attr('id').substring(id_str_btn.attr('id').indexOf('_')+1);
//Подписка
    if(id_str_btn.hasClass("page_nums")){
        
        $.post(
            domain+'/author/subscribe/'+getCookie('security_id')+'/'+id,
            {},
            function(data){
                console.log(data);
                let msg=JSON.parse(data);
                if(!msg['err']){
                    id_str_btn.html("Отписаться");
                    id_str_btn.removeClass("page_nums");
                    id_str_btn.addClass("page_nums_rev");

                }else{
                    alert(msg['err_txt']);
                }
            }
        );
//Отписка
    }else if(id_str_btn.hasClass("page_nums_rev")){
        $.post(
            domain+'/author/unsubscribe/'+getCookie('security_id')+'/'+id,
            {},
            function(data){
                console.log(data);
                let msg=JSON.parse(data);
                if(!msg['err']){
                    id_str_btn.html("Подписаться");
                    id_str_btn.removeClass("page_nums_rev");
                    id_str_btn.addClass("page_nums");
                }else{
                    alert(msg['err_txt']);
                }
            }
        );
    }
});
// Подача заяки на курс
$('.c_sub_btn').click(function(e){
    e.preventDefault();
    $btn=$(this);
    let id=$btn.data('cid');
//Подача заявки
    if($btn.hasClass("new_sub_btn"))
    {
        $.post(
            domain+'/course/subscribe/'+getCookie('security_id')+'/'+id,
            {},
            function(data){
                console.log(data);
                let msg=JSON.parse(data);
                if(!msg['err']){
                    $btn.removeClass("new_sub_btn");
                    $btn.removeClass("page_nums");
                    $btn.addClass("page_nums_rev");
                    $btn.html(msg['data']);
                }else{
                    alert(msg['err_txt']);
                }
            }
        );
    }else{
//Отписка от курса / отмена заявки
        $.post(
            domain+'/course/unsubscribe/'+getCookie('security_id')+'/'+id,
            {},
            function(data){
                console.log(data);
                let msg=JSON.parse(data);
                if(!msg['err']){
                    $btn.html("Подписаться");
                    $btn.addClass("new_sub_btn");
                    $btn.removeClass("page_nums_rev");
                    $btn.addClass("page_nums");
                }else{
                    alert(msg['err_txt']);
                }
            }
        );
    }

});
//удаление курса
$('.crs_del').click(function(e){
    e.preventDefault();
    let id = $(this).data('cid');
    if(!confirm("Удалить: '"+$('#crsblock_'+id+' .note_title').text()+"'? При удалении курса также удаляются все записи этого курса")) return;
    $.ajax({
        url: $(this).attr('href'),
        method: 'DELETE',
        success: function(data){
            console.log(data);
            let msg=JSON.parse(data);
            if(msg['err']){
                alert(msg['err_txt']);
            }else{
                $('#crsblock_'+id).detach();
            }
        }
    });
})
//Принять заявку
$('.cnfrm_sub_btn').click(function(e){
    e.preventDefault();
    let cid = $(this).data('cid');
    let uid = $(this).data('uid');
    $.post(
        domain+'/request/confirm/'+cid+'/'+uid,
        {},
        function(data){
            console.log(data);
            let msg=JSON.parse(data);
            if(!msg['err']){
                $('#rqst_'+uid).detach();
            }else{
                alert(msg['err_txt']);
            }
        }
    );
});
$('.cncl_sub_btn').click(function(e){
    e.preventDefault();
    let cid = $(this).data('cid');
    let uid = $(this).data('uid');
    $.post(
        domain+'/request/cancel/'+cid+'/'+uid,
        {},
        function(data){
            console.log(data);
            let msg=JSON.parse(data);
            if(!msg['err']){
                $('#rqst_'+uid).detach();
            }else{
                alert(msg['err_txt']);
            }
        }
    );
});
}
Functions.push(subs);