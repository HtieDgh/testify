$(document).ready(function(){

    function getTestData() {
        if($('#test_description').val().trim()!=''||
        $('#test_title').val()!=''){
            //Структура данных теста
            var tst_data={
                title:$('#test_title').val().replace(/"/g,'\\\\\"'),
                description:$('#test_description').val().replace(/"/g,'\\\\\"'),
                limit:$('#limit').val(),
                test_start:$('#start').val(),
                test_end:$('#end').val(),
                test_id:$('#test_id').val(),
                test_cu:$('#test_cu').val(),
            };
        }else{
            throw new Error('Заполните Название и Описание теста, прежде чем завершать редактирование');
        }
        
        return tst_data;
    }

    function newEvents(){
        //===== Удаление вопроса из списка =====
        $('.del_answ_btn').off('click').click(function(e){
            e.preventDefault();
            
            let q_id=$(this).attr('href');
            let p=$(this).parent().parent();
            //Плавное исчезновение
            $(this).parent().animate({opacity:0},300,function(){
                $(this).remove();
                    //Обновление номеров у span
                p.find('.answ_number').each((k,v)=>{
                    $(v).html(k+1);
                });
                //Обновление кол-ва ответов для отображения номера
                $('#'+q_id+'_answ_count').val($('#'+q_id+'_answ_count').val()-1);
                //Удалить последний вар ответа нельзя
                if($('#'+q_id+'_answ_count').val()==1){
                    $('#'+q_id+'_q .del_answ_btn').hide();
                }
            });
        });
    
        //==== Добавление варианта ответа в вопрос ====
        // Одиночный клик - Новый вопрос
        $('.add_answ_btn').off('click').click(function(e){
            e.preventDefault();
            let q_id=$(this).attr('href');
            let answ_count=Number($('#'+q_id+'_answ_count').val());
           
            answ_count++;
            
            $(this).before(
                `<div class="`+q_id+`_qst_answ flex_fs_r_ac transparent">
    
                    <span class="fs14_txt answ_number mr_r_10">`+answ_count+`</span>
                    <textarea rows="1" class=" mr_r_10" name="`+q_id+`_`+(answ_count-1)+`_qst_answ" placeholder="Текст ответа" value="" required></textarea>
                    <input type="number"  name="price" min="-1000" max="1000" value="0">
                    <input type="number" class="fine_input" name="fine" min="-1000" max="1000" value="0">
    
                    <a title="Удалить вариант ответа" class="qst_btn_alt del_answ_btn" href="`+q_id+`"><img alt="Удалить вариант ответа" src="minus_test.svg"></a>
                
                </div>`
            );
            $('#'+q_id+'_answ_count').val(answ_count);
            //Плавное появление варианта ответа 
            $('.'+q_id+'_qst_answ').animate({opacity:1},300);
            //Добавление кнопки Удалить вар ответа если до этого она была скрыта
            $('#'+q_id+'_q .del_answ_btn').show();
            newEvents();
        });

    $('#confirm_edit_btn').click(function(e){
        e.preventDefault();
        let fd=new FormData();
        $(this).html('Подождите...')
        try {
            //Включение test_data в форму отправки
            fd.append('test_data',JSON.stringify(getTestData()));
            $.ajax({
                url: '../../edit/test',
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST', // For jQuery < 1.9
                success: function(msg){
                    console.log(msg);
                    let m=JSON.parse(msg);
                    console.log(m);
                    if(!m.err){
                        location.href='./';
                    }else{
                        $('#err_wrap').modal();
                        $('#exept_txt').html(m.err_txt);
                    }
    
                }
            });
        } catch (ex) {
            $('#err_wrap').modal();
            $('#exept_txt').html(ex.message);
            $(this).html('Следующий шаг');
        }

       
    });
});