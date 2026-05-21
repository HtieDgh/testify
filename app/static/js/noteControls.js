function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function noteControl(){
/*для правельной работы скрипта указать корнивую папку с файлами */
var domain='/course-project-2024-4243';
function displayComments(data,id_note,cur_btn=false)
{
    let authorId=getCookie('security_id');
    console.log(data);
    let msg=JSON.parse(data);
    let html_txt='';
    if(msg['err']){
        alert(msg['err_txt']);
    }else{
        msg['comments'].forEach(com => {
            html_txt+=`
            <div class="comment" id="comment_${id_note}_${com['id']}">
                <div class="flex_sb_r_ac flex_wr">
                    <div class="flex_sb_r_ac"> 
                        <div class="ava_img cmnt_ava_img mr_r_10">
                            <img id="img_${com['author_id']}" src="${com['ava_url']}">
                        </div>
                        <div class="flex_fs_r_ac flex_wr">
                            <h2 class="comment_title mr_r_10">${com['name']}</h2>`;
                    if(com['author_id']==authorId || msg['access'])
                    {
                        html_txt+=`
                            <div class="test_btn"> 
                                <a class="comment_del" title="Удалить" data-comid="${com['id']}" data-nid="${id_note}" href="${domain}/comments/${id_note}"><img alt="Удалить" src="${domain}/minus_test.svg"></a>
                            </div>`;
                    }
                    html_txt+=`
                        </div>
                    </div>
                    <p class="comment_date italyc">${com['created']}</p>
                </div>`;
        html_txt+= `<hr>
                <p class="comment_text">${com['text']}</p>
            </div>`;
        });
        $('#cmntblock_'+id_note).html(`
            <div id="comment_${id_note}">${html_txt}</div>
            ${authorId?
                `<form action="" method="POST" class="mr_t_10">
                <hr>
                    <div class="form-inner mr_t_10" style="padding:0;">
                        <div class="flex_c_r_ac">
                            <input name="text" class="comment_txt" style="margin:0;" type="text" placeholder="Текст комментария">
                            <input type="submit" value="Отправить" class="send_cmnt" data-noteid="${id_note}" style="margin:0 0 0 10px;">
                        </div>
                    </div>
                </form>`
                :
                `<p class="ar_txt">Чтобы комментировать запись вам необходимо <a class="page_nums" href="${domain}/login">Войти</a> </p>`
            }`);
        $('#cmntblock_'+id_note).show();
        if(cur_btn){
            cur_btn.html("Скрыть комментарии");
        }
        newEvents();
    }
}
//=======Клик на кнопку Открыть коментарии=========
$('.note_cmt').click(function(e){
    e.preventDefault();
    
    let id_note=$(this).data('nid');
    let cur_btn=$(this);

    if(cur_btn.html()!="Скрыть комментарии"){
        $.get(
            $(this).attr('href'),
            {},
            function(data){
                displayComments(data,id_note,cur_btn);
            }
        );
    }else{
        $('#cmntblock_'+id_note).html('');
        $('#cmntblock_'+id_note).hide();
        cur_btn.html("Открыть комментарии...")
    }
});

/*Удаление заметки*/
$('.note_del').click(function(e){
    e.preventDefault();
        let id_note=$(this).data('nid')  
        let cur_note=$('#note_'+id_note);
    if(confirm("Удалить: '"+$('#note_'+id_note+' .note_title').text()+"'?")){
        $.ajax({
            url: $(this).attr('href'),
            method: 'DELETE',
            success: function(data){
                console.log(data);
                let msg=JSON.parse(data);
                if(msg['err']){
                    alert(msg['err_txt']);
                }else{
                    cur_note.detach();
                }
            }
        });
    }
}); 

/*клик на кнопку Комментировать запись*/
$('.close').click(function(e){
    e.preventDefault();
    $(".my_window").hide();
    
});


function newEvents()
{
    /*===========Удаление коментария=================*/
    $('.comment_del').off('click').click(function(e){
        e.preventDefault();
        let id_comment=$(this).data('comid');
        let id_note=$(this).data('nid');

        let cur_comment=$('#comment_'+id_note+"_"+id_comment);
        let span_count=$('#comment_'+id_note+' .comment_count');

        $.ajax({
            url: $(this).attr('href'),
            method: 'DELETE',
            success: function(data){
                console.log(data);
                let msg=JSON.parse(data);
                if(msg['err']){
                    alert(msg['err_txt']);
                }else{
                    cur_comment.detach();
                    span_count.html(span_count.html()-1);
                }
            }
        });

    });
    /*===========Конец Удаление коментария=================*/
    //отправка комментария
    $('.send_cmnt').off('click').click(function(e){
        e.preventDefault();
        let id_note=$(this).data('noteid');
        $.post(
            domain+'/comments/'+id_note,
            { 
                authorId:getCookie('security_id'),
                text:$(this).prev('input').val()
            },
            function(data){
                displayComments(data,id_note);
            }
        );
    });
}

}
Functions.push(noteControl);