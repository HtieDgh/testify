 function scroll() {
     /* скрол на топ    */
 var chosen_off=$('#scrollTarget').offset().top;
 window.onscroll=function(){
     if(window.scrollY>chosen_off){
         $('.mbtn').show();
     }else{
         $('.mbtn').hide();
     }
    
 };
 $(".mbtn").click( function (event) {
    //отменяем стандартную обработку нажатия по ссылке
    event.preventDefault();
    //забираем идентификатор бока с атрибута href
    let id  = $(this).attr('href'),
    //узнаем высоту от начала страницы до блока на который ссылается якорь
    top = $(id).offset().top-150;
    //анимируем переход на расстояние - top за 500 мс

    $('body,html').animate({scrollTop: top}, 500);

});
}
Functions.push(scroll);