$(document).ready(function(){
    //Удаление теста
    $('.test_del_btn').click(function(e){
        e.preventDefault();
        var test_wrap=$(this).parent().parent().parent();
        if(confirm('Удалить тест?')){
            $.get(this.href,
            {},
            (data)=>{
                console.log(data);
                let msg=JSON.parse(data);
                if(!msg['err']){ 
                    test_wrap.remove();
                    alert('Тест удален!');
                }else{
                    alert(msg['err_txt']);
                }
            });
        }
        
    });
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
            if(msg['err']){ 
                $('#err_wrap').modal();
                $('#exept_txt').html(msg['err_txt']);
                return;
            }
            $('#ex_variants textarea').val('');
            msg['variants'].forEach(element => {
                $('#ex_variants textarea').val($('#ex_variants textarea').val()+element['title']+': '+msg['absolute']+'test/'+element['link']+'\r\n');
                $('#ex_variants textarea').attr('rows', Number($('#ex_variants textarea').attr('rows'))+1);
            });
            $('#ex_variants').modal();
        });
    })
  //==== Отображение текста ошибки от сервера если она есть ====
  if($('#exept_txt').html()!=''){
        $('#err_wrap').modal();
    }
});