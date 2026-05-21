function test_profile(){
    var domain='/course-project-2024-4243';
    //Удаление теста
    $('.test_del_btn').click(function(e){
        e.preventDefault();
        let test_wrap=$(this).parent().parent().parent();
        if(confirm('Удалить тест?')){
            $.get(this.href,
            {},
            (data)=>{
                console.log(data);
                let msg=JSON.parse(data);
                if(!msg['err']){ 
                    test_wrap.remove();
                }else{
                    alert(msg['err_txt']);
                }
            });
        }
        
    });
    //нажать на кнопку "пройти тест"
    $('.do_try_btn').click(function(e){
        e.preventDefault();
        $('#do_test_modal').modal();
    })
    //подтвердить ссылку на вариант
    $('.goto_test_btn').click(function(e){
        e.preventDefault();
        location.href=$('#modal_test_link').val();
    });
    //отобразить ссылки на варианты теста
    $('.get_var_btn').click(function(e){
        e.preventDefault();
        $.get(this.href,
        {},
        (data)=>{
            console.log(data);
            let msg=JSON.parse(data);
            if(msg.err){ 
                $('#err_wrap').modal();
                $('#exept_txt').html(msg.err_txt);
                return;
            }
            $('#ex_variants textarea').val('');
            msg.variants.forEach(v => {
                $('#ex_variants textarea').val($('#ex_variants textarea').val()+v.title+': '+window.location.origin+domain+'/test/'+v.unique_url+'\r\n');
                $('#ex_variants textarea').attr('rows', Number($('#ex_variants textarea').attr('rows'))+1);
            });
            $('#ex_variants').modal();
        });
    })
}
Functions.push(test_profile);