$(document).ready(function(){
    $('.EnterBtn').click(function(e){
        e.preventDefault();
        $.post(
            'regist/new',
            {
                name:$('#s_n').val(),
                login:$('#s_l').val(),
                password:$('#s_p').val()
            },
            function(data){
                console.log(data);
                let msg=JSON.parse(data);
                if(!msg['err']){
                    alert('Учетная запись зарегестрирована! Сейчас вы будете перенаправлены в свой профиль');
                    location.href='./';
                }else{
                    $('.alert_txt').html(msg['err_txt']);
                }
            }
        );
    });
});