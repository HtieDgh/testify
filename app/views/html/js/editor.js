$(document).ready(function(){
    var all_qst_count=Number($('.all_qsts').html());
    //Создает запрос на скачивание zip архива теста
    function downloadFile(link) {
        let a = document.createElement("a");
        a.href = link;
        a.target="_blank";
        a.click();
        }
    // Возвращает текущую структуру данных теста
    function getTestData() {
        let qst_data=[];
        //Формирование списка существующих вопросов и ответов на них
        let q_id=0;
        while($('#'+q_id+'_q').length==0){
            q_id++;
        } 
        do {
            let cur_qst=$('#'+q_id+'_q');
            
            //Список имен файлов и их типов жля последуюбщей обработки на сервере
            let files_input=cur_qst.find('#'+q_id+'_files');
            
            let fls_list=[];
            for (let i = 0; i < files_input[0].files.length; i++) {
                
                fls_list.push({
                    name: files_input[0].files[i].name,
                    mime: files_input[0].files[i].type
                });
            }
            //Список ответов
            let answ_list=[]
            cur_qst.find('.'+q_id+'_qst_answ').each(function(k,v) {
                if($('textarea[name='+q_id+'_'+k+'_qst_answ]').val()!=''){
                    answ_list.push({
                        text: $('textarea[name='+q_id+'_'+k+'_qst_answ]').val(),
                        price: $(v).find('input[name=price]').val(),
                        fine: $(v).find('input[name=fine]').val()
                    });
                }else{
                    throw new Error('Заполните все поля ответов, прежде чем завершать редактирование');
                }
            });
            if(cur_qst.find('.qst_title').val().trim()!=''){
                qst_data.push({
                    title: cur_qst.find('.qst_title').val(),
                    text: cur_qst.find('.qst_txt').val(),
                    type: cur_qst.find('input[name=type]:checked').val(),
                    is_vid_hidden: cur_qst.find('#'+q_id+'_is_vid_hidden').prop('checked'),
                    file_names: fls_list,
                    answs: answ_list
                });
            }else{
                throw new Error('Заполните Заголовки вопросов, прежде чем завершать редактирование');
            }
            
            
            q_id++;
        } while ($('#'+q_id+'_q').length>0);
        
        if($('#test_description').val().trim()!=''||
        $('#test_title').val()!=''){
            //Структура данных теста
            console.log($('#test_title').val().replace(/"/g,'\\\\\"'));
            var tst_data={
                link:$('#test_link').val(),
                title:$('#test_title').val().replace(/"/g,'\\\\\"'),
                descript:$('#test_description').val().replace(/"/g,'\\\\\"'),
                limit:$('#limit').val(),
                test_start:$('#start').val(),
                test_end:$('#end').val(),
                qsts: qst_data
            };
        }else{
            throw new Error('Заполните Название и Описание теста, прежде чем завершать редактирование');
        }
        
        return tst_data;
    }
       
    /*===========Добавление событий на кнопки после их рендера на странице=================*/
    function newEvents(){
        //===== Удаление вопроса из списка =====
        $('.del_qst_btn').off('click').click(function(e){
            e.preventDefault();
            let q_id=$(this).attr('href');
            //Плавное исчезновение
            $('#'+q_id+'_q').animate({opacity:0},300,function(){
                $(this).remove();
                all_qst_count--;
                $('.all_qsts').html(all_qst_count);
                $('.cur_all_qsts').each((k,v)=>{
                    $(v).html(k+1);
                })
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
        // Двойтой клик - Открыть список существующих вопросов в БД 
        // TODO


        //=== Удаление варианта ответа ===
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
    
        //Смена типа с Закрытого на Открытый меняет варианты ответов
        $('.form-inner input[name=type]').off("change").change(function(){
            let q_id = $(this).attr('id').substring(0,$(this).attr('id').indexOf('_'));
            
            $('.'+q_id+'_qst_answ').remove();
            $('#'+q_id+'_answ_count').val('1');
            if (this.value == '1') {
                $('#'+q_id+'_answ_list').after(`
                <div class="`+q_id+`_qst_answ flex_fs_r_ac transparent">
                    
                    <textarea rows="1" class=" mr_r_10" name="`+q_id+`_0_qst_answ" placeholder="Текст ответа" value="" required></textarea>
                    
                    <input type="number" name="price" min="-1000" max="1000" value="0">
                    <input type="number" name="fine" min="-1000" max="1000" value="0">
    
                </div>`);
                $('#'+q_id+'_q').find('input[name=fine]').show();
                $('.add_answ_btn[href='+q_id+']').remove();
    
            }else{
                $('#'+q_id+'_answ_list').after(`
                <div class="`+q_id+`_qst_answ flex_fs_r_ac transparent">
                                        
                    <span class="fs14_txt answ_number mr_r_10">1</span>
                    <textarea rows="1" class=" mr_r_10" name="`+q_id+`_0_qst_answ" placeholder="Текст ответа" value="" required></textarea>
                    
                    <input type="number" name="price" min="-1000" max="1000" value="0">
                    <input type="number" name="fine" min="-1000" max="1000" value="0">
                    <a title="Удалить вариант ответа" class="qst_btn_alt del_answ_btn" href="0"><img alt="Удалить вариант ответа" src="minus_test.svg"></a>
                
                </div>
                <a title="Добавить вариант ответа" class="qst_btn add_answ_btn" href="`+q_id+`"><img alt="Добавить вариант ответа" src="add_test.svg"></a>`);
                
                newEvents();
            }
            $('.transparent').animate({opacity:1},300);
        });
    }
    //==== Добавление вопроса в список ====
    $('#add_qst_btn').click(function(e){
        e.preventDefault();
        $(this).before(
            `<div id="`+all_qst_count+`_q" class="note transparent">
            <div class="flex_sb_r">
                <form class="decor" method="post" action="new_test/">
                    <div class="form-inner">
    
                        <input type="text" class="fs12_txt qst_title" name="qst_title" placeholder="Заголовок" value="" required>
                        <div class="flex_sb_r flex_wr">
                            <div class="qst_type flex_sb_r_ac">
                                <div class="flex_fs_r_ac">
                                    <input  class="mr_r_10" type="radio" id="`+all_qst_count+`_type" name="type" value="0" checked />
                                    <label for="`+all_qst_count+`_type">Закрытый</label>
                                </div>
    
                                <div class="flex_fs_r_ac">
                                    <input class="mr_r_10" type="radio" id="`+all_qst_count+`_2_type" name="type" value="1" />
                                    <label for="`+all_qst_count+`_2_type">Открытый</label>
                                </div>
                            </div>
                            
                        </div>
    
                        <textarea name="note_txt" class="edit_txt qst_txt" placeholder="Текст вопроса" rows="4" required></textarea>
                        <div class="flex_sb_r_ac flex_wr">
                            <input class="file_in UserIn" id="`+all_qst_count+`_files" accept="image/*,video/*,audio/*" name="user_files[]" type="file" multiple>
                            <div class="flex_fs_r_ac">
                                <input class="mr_r_10" type="checkbox" name="is_vid_hidden" id="`+all_qst_count+`_is_vid_hidden">
                                <label for="`+all_qst_count+`_is_vid_hidden">Скрыть видео?</label>
                            </div>
                        </div>
                            
                        <div class="mt_10">
                            <p class="ac_txt">Варианты ответов:</p>
                            <input id="`+all_qst_count+`_answ_count" type="hidden" name="answ_count" value="1">
                            <hr id="`+all_qst_count+`_answ_list">
                            <div class="`+all_qst_count+`_qst_answ flex_fs_r_ac">
                            
                                <span class="fs14_txt answ_number mr_r_10">1</span>
                                <textarea rows="1" class=" mr_r_10" name="`+all_qst_count+`_0_qst_answ" placeholder="Текст ответа" value="" required></textarea>
                                
                                <input type="number" name="price" min="-1000" max="1000" value="0">
                                <input type="number" class="fine_input" name="fine" min="-1000" max="1000" value="0">
                                <a title="Удалить вариант ответа" class="qst_btn_alt del_answ_btn" href="`+all_qst_count+`"><img alt="Удалить вариант ответа" src="minus_test.svg"></a>
                            
                            </div>
                            <a title="Добавить вариант ответа" class="qst_btn add_answ_btn" href="`+all_qst_count+`"><img alt="Добавить вариант ответа" src="add_test.svg"></a>
                        </div>
                        
                    </div>
                </form>
                <div class="flex_fs_c_ac">
                    <div class="qst_number ac_txt fs12_txt">
                        <span class="cur_all_qsts">`+(all_qst_count+1)+`</span>/<span class="all_qsts">`+(all_qst_count+1)+`</span>
                    </div>
                    
                    <a title="Удалить вопрос из теста" class="qst_btn_alt del_qst_btn" href="`+all_qst_count+`">
                        <div class="flex_c_c">
                            <img alt="Удалить вопрос из теста" src="minus_test.svg">
                        </div>
                    </a>
                    
                </div>
                
            </div>
            </div>`);
        //Плавное появление
        $('#'+all_qst_count+'_q').animate({opacity:1},300);
        //Прибавка +1 к номеру для отображения
        all_qst_count++;
        $('.all_qsts').html(all_qst_count);
        
        newEvents();
    });
    //==== Отображение текста ошибки от сервера если она есть ====
    if($('#err_wrap').length){
        $('#err_wrap').modal();
    }
    
    //==== Завершение Редактирования. Отправка Теста на сервер и получение файла сохранения на клиенте ====
    //кол-во файлов
    
    $('#confirm_edit_btn').click(function(e){
        e.preventDefault();
        //Подсчет кол-ва загружаемых на сервер файлов
        var up_file_count=0;
        $('.file_in').each(function(){
                        
            for (let i = 0; i < $(this).prop('files').length; i++) {
                up_file_count++;
            }
        });
    
        if($(this).html()!=`Отправка...`){
            //Запуск анимации загрузки: изменяется текст
            $(this).html(`Отправка...`);
            try {
    
                function fn_serverResponse(msg){
                    $('#confirm_edit_btn').html('Ошибка');
                    console.log(msg);
                    let m=JSON.parse(msg);
                    console.log(m);
                    if(!m.err){
                        $('#ex1').modal();
                        $('#a_test_link').attr('href',m.link).html(m.link);
                        downloadFile(m.test_file_link);
                        $('#confirm_edit_btn').html('Завершить редактирование');
                    }else{
                        throw new Error(m.err_txt);
                    }
                    
                }
                function fn_onCompleteFilesLoad() {
                    let fd2=new FormData();
                    //Включение test_data в форму отправки
                   fd2.append('test_data',JSON.stringify(getTestData()));
                   //Отправка запроса
                   $.ajax({
                       url: '../../new_test',
                       data: fd2,
                       cache: false,
                       contentType: false,
                       processData: false,
                       method: 'POST',
                       type: 'POST', // For jQuery < 1.9
                       success: fn_serverResponse
                   });
                   
                }
    
    
                if(up_file_count>0) {
                    let fd=new FormData();
            
                    //Поготовка данных перед отправкой файлов теста 
                    fd.append('test_link',$('#test_link').val());
                    fd.append('test_title',$('#test_title').val());
                    fd.set('file_count',up_file_count);
                    
                    $('.file_in').each(function(){
                        
                        for (let i = 0; i < $(this).prop('files').length; i++) {
                            //Размер файла боьше 50 Мб - ошибка
                            if(this.files[i].size> 52428800){
                                $('#file_name').html(files_input[0].files[i].name);
                                throw new Error(this.files[i].name+' - файл превышает размер 50 мб');
                                
                            }
                            fd.set('file',this.files[i]);
                            
                            $.ajax({
                                url: '../../send_test_file',
                                data: fd,
                                cache: false,
                                contentType: false,
                                processData: false,
                                method: 'POST',
                                type: 'POST', // For jQuery < 1.9
                                success: function(msg){
        
                                    console.log(msg);
                                    if(--up_file_count == 0) {
                                        //Попытка отследить отправку последнего файла
                                        fn_onCompleteFilesLoad();
                                    }
                                    console.log(up_file_count);
                                }
                            });
                            
                        }
                        
                    });
                    
                    delete fd;
                //=== Отправка файлов окончена ===
                }else{
                    //файлов нет
                    fn_onCompleteFilesLoad();
                }
                
               
            } catch (ex) {
                
                $('#ex2').modal();
                $('#exept_txt').html(ex.message);
                $('#confirm_edit_btn').html(`Завершить редактирование`);
            }
    
        }
       
    });
      
    //Регистрация событий кнопок управления
    newEvents(); 
    });