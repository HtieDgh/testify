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