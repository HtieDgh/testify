<?php namespace view;

use view\PageAbstract;
use Template;
 /**
 * <p>Возвращает форму авторизации</p>
 * @param html_txt сообщение пользователю. Например об ошибке.
 * @return string DOMString
*/
final class LoginFormPage extends PageAbstract 
{
    protected static $i;
    protected string $content='';

    public static function i(): static {
        if (!(static::$i instanceof static)) {
            static::$i = new static();
        }
        return static::$i;
    }

    public function addLoginForm(string $loginError='') : self
    {
        $f3=\Base::instance();
        $this->_set_css(['flexable.css','login.css']);
        $f3->set('error',$loginError);
        $this->content=Template::instance()->render('loginForm.htm');
        return $this;
    }
    public function addRegistForm(\Base &$f3) : self
    {
        $this->_set_css(['flexable.css','general.css','login.css']);
        $this->_set_js(['regist.js']);
        $this->content=Template::instance()->render('registForm.htm');
        return $this;
    }
   
    //возвращает тег <body>
    public function body(): string
    {
        $f3=\Base::instance();
        return '<body>
           '.
           $this->header.
           $this->burgerMenu.
           $this->content.
           $this->errorModalWrap.
           $this->footer.
           $this->js2link($f3->get("BASE"),$this->js).
        '</body>';
    }
}
?>