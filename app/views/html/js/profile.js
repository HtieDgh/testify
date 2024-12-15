$(document).ready(function(){
    //Удаление теста
    $('.test_del_btn').click(function(e){
        e.preventDefault();
        var test_wrap=$(this).parent().parent().parent();
        if(confirm('Удалить тест?')){
            $.get(this.href,
            {},
            (data)=>{
                let msg=JSON.parse(data);
                console.log(msg);
                if(msg['err']==false){ 
                    test_wrap.remove();
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
  //==== Отображение текста ошибки от сервера если она есть ====
  if($('#err_wrap').length){
        $('#err_wrap').modal();
    }
});