$(document).ready(function(){
    var t_id=$('.add_elem_btn').attr('href');

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
                test_id:$('#test_id').val()
            };
        }else{
            throw new Error('Заполните Название и Описание теста, прежде чем завершать редактирование');
        }
        
        return tst_data;
    }
    function getVariantData(){
        let variant_data=[];
           
        $('.'+t_id+'_test_var').each((k,v)=>{
            let t=$(v).find('.title').val();
            if(t==''){
                throw new Error('Заполните все названия Вариантов, прежде чем переходить на след шаг');
            }
            variant_data.push({
                link: $(v).find('.chosen_variant').val(),
                title: t,
                is_active:$(v).find('.chosen_variant:checked').length>0 ? 1:0.
            });
        });
        return variant_data;
    }

        //==== Добавление варианта ====
        // Одиночный клик - Новый вариант
        $('.add_elem_btn').off('click').click(function(e){
            e.preventDefault();
            let v_count=Number($('v_countjs').val());
           
            v_count++;
            
            $(this).before(

                `<div class="`+t_id+`_test_var flex_fs_r_ac">
                              
                <div class="flex_c_r_ac">
                    <input type="radio" class="chosen_variant" name="variant" value="0">
                    <span class="fs14_txt answ_number mr_r_10">`+v_count+`</span>
                </div>
                    
                    
                    <textarea rows="1" class="title mr_r_10" name="0_id_var" placeholder="Название Варианта" value="" required></textarea>
                    
                    <a title="Удалить вариант теста" class="qst_btn_alt del_answ_btn" href=""><img alt="Удалить вариант теста" src="minus_test.svg"></a>
                
                </div>
                `);
            $('#'+t_id+'_v_count').val(v_count);
            //Плавное появление варианта 
            $('.'+t_id+'_test_var').animate({opacity:1},300);

        });


        $('#confirm_edit_btn').click(function(e){
            e.preventDefault();
            let fd=new FormData();
            $(this).html('Подождите...')
            try {
                //Включение test_data в форму отправки
                fd.append('test_data',JSON.stringify(getTestData()));
                fd.append('variant_data',JSON.stringify(getVariantData()));
                console.log(fd.get('test_data'));
                console.log(fd.get('variant_data'));
                $.ajax({
                    url: '../../edit/test/'+$('.chosen_variant:checked').val(),
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
                            location.href='../../edit/questions/'+m.variant_link;
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
