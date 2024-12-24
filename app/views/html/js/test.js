$(document).ready(function(){
    //==== Отображение текста ошибки от сервера если она есть ====
if($('#exept_txt').val()!=''){
    $('#err_wrap').modal();
}
    //TODO localStrorage
    var test_state=0;
    var q_iter=0;
    
    var q_ids=JSON.parse($('.start_test_btn').attr('data-questions'));



$('.start_test_btn').click(function(e) {
    e.preventDefault();
       
    //Кнопка подтвердить должна запускать видео/аудио если оно есть.
    if(test_state!=2)
    {
        $('#'+q_ids[q_iter].id+'_q').show();
        $('#'+q_ids[q_iter].id+'_q').animate({opacity:1},300);

        $('video').each(function (k,e) {
            e.pause();
        });
        $('audio').each(function (k,e) {
            e.pause();
        });
        if($('#'+q_ids[q_iter].id+'_q video').length>0){
            $('#'+q_ids[q_iter].id+'_q video:first-child')[0].play();
        }
        if($('#'+q_ids[q_iter].id+'_q audio').length>0){
            $('#'+q_ids[q_iter].id+'_q audio:first-child')[0].play();
        }
    }
    console.log({t_state:test_state});
    switch (test_state) {
        case 0://Начало теста => отобразить первый вопрос
            test_state=1;
            q_iter++;
            if(q_iter==q_ids.length){ //если вопрос первый и последний
                test_state=2;
                q_iter=0;
                $(this).html('Завершить тест');
            }else{
                $(this).html('Подтвердить');
            }
            break;
        case 1://Тест уже идет
            q_iter++;
            if(q_iter==q_ids.length){
                q_iter--;
                test_state=2;
                $(this).html('Завершить тест');
            }
            break;
        case 2://Завершить тест

            $.post(
                '../new/result/'+$('#variant_link').val(),
                {
                    answ_data: getResult(),
                },
                function (data) {
                    console.log(data);
                    $(this).html('Ошибка');
                    let msg=JSON.parse(data);
                    if(!msg.err){
                        location.href=msg.result_link;
                    }else{
                        $('#err_wrap').modal();
                        $('#exept_txt').html(msg.err_txt);
                    }
                    
                }
            );
            $(this).html('Загрузка результатов');
            break;
        
        default:
            break;
    }
    
});

//Открыть скрытое видео
$('.open_video_btn').click(function(e) {
    e.preventDefault();
    $('#'+$(this).attr('id')+'_v').removeClass('invis');
    $(this).remove();
});

function getResult() {

    let answ_data=[];
    
    for (let q_it = 0; q_it < q_ids.length; q_it++) 
    {
        const element = $('#'+q_ids[q_it].id+'_q');
        let input;
        let answ_ids=[];
        let user_in='';
        if(q_ids[q_it].is_open==1)
        {
           input=element.find('input[type=text]');
           user_in=input.val();
        }else{
            input=element.find('input[type=checkbox]:checked')
        }
        input.each(function(k,e){
            
            answ_ids.push(  $(e).attr('id').substr( $(e).attr('id').indexOf('_')+1 )  );
            
        });
        answ_data.push({
            q_id: q_ids[q_it].id,
            answ: answ_ids,
            descriptor: user_in
        });

    }
    return answ_data;
}

});