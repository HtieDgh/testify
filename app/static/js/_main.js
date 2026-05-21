//файл заменяет document.ready(function(...)) чтобы не зависеть от разного порядка загрузки js файлов. другие файлы просто регистрируют свои функции через functions.push
const Functions = [];
$(document).ready(function(){
    //вызов всех обаботчиков когда они будут готовы
    Functions.forEach(
        func=>{func();console.log(`Функция ${func.name} добавлена`);}
    );
    
});