<?php namespace view;

use view\PageAbstract;

final class ProfileEditorPage extends PageAbstract
{
    protected string $editorHtml='';
    public string $backBtns='';

    protected static $i;

    public static function i(): static {
        if (!(static::$i instanceof static)) {
            static::$i = new static();
        }
        return static::$i;
    }
    public function addEditProfileForm(): static
    {
        $f3=\Base::instance();
        $this->_set_css(['general.css','editor.css','decor_form.css','buttons.css']);
        $this->_set_js(['burger.js','send_ava.js']);
        $this->editorHtml='
        <div class="note">
            <div class="flex_c_r">
            <form class="decor" method="POST" action="'.$f3->get('BASE').'/editor/profile">
                <div class="form-inner">
                   
                    <div class="flex_c_r">
                        <div class="ava_img flex_c_c">
                            <img id="imgprof_'.$f3->get('user.id').'" src="'.$f3->get('user.ava_url').'">
                        </div>
                    </div>
                    <br>
                
                    <div class="flex_c_r_ac"><p class="good_txt">Изменить фото</p></div>
                    <div class="flex_c_r_ab flex_wr">
                        <input class="file_in" id="user_ava" accept="image/bmp,image/jpeg,image/png" name="user_ava" class="UserIn" type="file">
                        <a href="'.$f3->get('BASE').'/profile/edit/ava" class="page_nums_rev" id="send_ava_btn" class="disp_none">Отправить фото</a>
                </div>

                    <p class="good_txt">Имя</p>
                    <input type="text" name="name" required placeholder="Имя" value="'.$f3->get('user.name').'">

                    <p class="good_txt">Статус</p>
                    <input type="text" name="status" required placeholder="Статус" value="'.$f3->get('user.status').'">

                    <p class="good_txt">Изменение пароля</p>
                    <input type="password" name="new_pass" placeholder="Новый пароль" value="">  
                    <p class="good_txt">Подтвердите изменение</p>
                    <input type="password" name="cur_pass" required placeholder="Текущий пароль" value="">
                    <div class="flex_c_r flex_wr">
                        <input class="page_nums_rev" type="submit" value="OK">
                    </div>
                </div>
            </form>
            </div>
        </div>';
        return $this;
    }
    public function addGoBackBtns() : static 
    {
        $f3=\Base::instance();
        $this->backBtns='
        <div class="wrap_block">
                <a class="page_nums" href="'.$f3->get('BASE').'/profile">К профилю</a><a class="page_nums" href="'.$f3->get('BASE').'">На главную</a></p>
        </div>';
        return $this;
    }
    public function body(): string
    {
        $f3=\Base::instance();
        return '<body>'.
            $this->header.
            $this->burgerMenu.
            '<section class="content">'.
            $this->editorHtml.
            $this->backBtns.
            '</section>'.
            $this->errorModalWrap.
            $this->footer.
            $this->js2link($f3->get("BASE"),$this->js).
        '</body>';
    }

}
?>