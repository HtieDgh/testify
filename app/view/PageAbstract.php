<?php namespace view;

use view\ViewAbstract;
use Template;

abstract class PageAbstract extends ViewAbstract
{
    protected string $header='';
    protected string $footer='';
    public string $burgerMenu='';
    protected string $errorModalWrap='';

    public string $title='';

    public function addTitle(string $title='') : static 
    {
        $this->title=$title;
        return $this;
    }
    abstract public function body() : string;   
    /**
     * addHeader
     *
     * @param  mixed $op либо serach.htm либо headerTitle.htm
     * @param  mixed $parameter = href для search | Заголовок для title
     * @return static
     */
    public function addHeader(string $op='search.htm',string $parameter='') : static 
    {
        $f3=\Base::instance();
        $this->_set_css(['flexable.css','general.css']);
        if($op=='search.htm'){ $this->_set_css(['search.css']); }
        $f3->set('headerInclude',$op);
        $f3->set('parameter',$parameter);
        $this->header=Template::instance()->render('header.htm');
        return $this;
    }
    public function addFooter() : static 
    {
        $this->_set_css(['flexable.css','general.css']);
        $this->footer=Template::instance()->render('footer.htm');
        return $this;
    }
    public function addErrorModalWrap($msg='') : static
    {
        $this->_set_js(['err_wrap.js','jquery.modal.min.js']);
        $this->_set_css(['jquery.modal.min.css']);
        $this->errorModalWrap='
                <div id="err_wrap" class="modal">
                    <p id="exept_txt">'.$msg.'</p>
                    <br>
                    <a href="'.\Base::instance()->get("BASE").'/profile">В профиль</a>
                </div>
                ';
        return $this;
    }
    /**
     * Меню навигации Бургер типа, требует @user.isAuth == true
     */
    public function addBurgerMenu():static
    {
        $this->_set_css(['general.css','burger.css']);
        $this->_set_js(['burger.js']);
        $this->burgerMenu=Template::instance()->render('burger.htm');
        return $this;
    }
    /**
     * <p>Возвращает готовый html</p>
     * @param string для вывода 
     * @return string DOMString
     */
    public function htmlRender(string $head,  string $body){
        return '<!DOCTYPE html>
            <html lang="ru">
            '.$head.'
            '.$body.'
            </html>';
    }
    /**
     * <p>Возвращает тег <head> для DOMString</p>
     * @param object f3 
     * @return string DOMString <head>
     */
    public function head(){
        $f3=\Base::instance();
        return '
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>'.$this->title.'</title>
            <link rel="stylesheet" type="text/css" href="'.$f3->get("BASE").'/reset.css">
            <link rel="stylesheet" type="text/css" href="'.$f3->get("BASE").'/color_theme.css">
                                
            <link rel="icon" type="image/x-icon" href="'.$f3->get("BASE").'/favicon.ico">
            <link rel="icon" type="image/png" sizes="16x16" href="'.$f3->get("BASE").'/favicon-16x16.png">
            <link rel="icon" type="image/png" sizes="32x32" href="'.$f3->get("BASE").'/favicon-32x32.png">
            <link rel="icon" type="image/png" sizes="48x48" href="'.$f3->get("BASE").'/favicon-48x48.png">
            <link rel="icon" type="image/png" sizes="64x64" href="'.$f3->get("BASE").'/favicon-64x64.png">
            <link rel="icon" type="image/png" sizes="128x128" href="'.$f3->get("BASE").'/favicon-128x128.png">
            <link rel="icon" type="image/png" sizes="256x256" href="'.$f3->get("BASE").'/favicon-256x256.png">
            <link rel="icon" type="image/svg+xml" href="'.$f3->get("BASE").'/favicon.svg">
            <link rel="apple-touch-icon" sizes="192x192" href="'.$f3->get("BASE").'/android-chrome-192x192.png">
            <link rel="manifest" href="'.$f3->get("BASE").'/site.webmanifest">
            <meta name="msapplication-TileColor" content="#da532c">
            <meta name="theme-color" content="#ffffff">
            <script src="'.$f3->get("BASE").'/jquery-3.3.1.js"></script>
            <script src="'.$f3->get("BASE").'/_main.js"></script>
            '.$this->css2link($f3->get("BASE"),$this->css).'
        </head>
        ';
    }
}
?>