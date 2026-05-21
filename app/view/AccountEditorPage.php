<?php namespace view;

use view\PageAbstract;

final class AccountEditorPage extends PageAbstract
{
    protected string $accountEditorHtml='';
    protected string $searchHtml='';
    protected string $usersListHtml='';
    protected string $paginatorTop='';
    protected string $paginatorBottom='';
    protected string $backBtns='';

    protected static $i;

    public static function i(): static {
        if (!(static::$i instanceof static)) {
            static::$i = new static();
        }
        return static::$i;
    }
    public function addAccountEditor($rec,$head_label,$user_id,$pass_notiece,$goToUrl,$rolesList){
        $this->_set_css(['flexable.css','general.css','decor_form.css','check.css']);
        $f3=\Base::instance();
        $this->accountEditorHtml='
        <div class="note">
            <div class="flex_c_r">
                <form class="decor" method="POST" action="'.$goToUrl.'">
                    <div class="form-inner">
                        <p class="italyc"> 
                        <h3>'.$head_label.'</h3><br>
                        </p>
                        <input type="hidden" name="user_id" value="'.$user_id.'">
                        
                        <p class="good_txt">Имя</p>
                        <input type="text" name="u_name" required placeholder="Имя" value="'.$rec['name'].'">
                         <p class="good_txt">Статус</p>
                        <input type="text" name="u_status" required placeholder="Статус" value="'.$rec['status'].'">
                        <p class="good_txt">Дата регистрации. Поле можно оставить пустым - будет внесена текущая дата</p>
                        <input type="text" name="u_created" placeholder="Напр. 2020-05-26" value="'.$rec['created'].'">
                        
                        <p class="good_txt">Логин</p>
                        <input type="text" name="u_login" required placeholder="Логин" value="'.$rec['login'].'">
                        <p class="good_txt">Пароль</p>
                        '.$pass_notiece.'
                        <input type="text" name="u_pass" required placeholder="Пароль" value="'.$rec['pass'].'">
                        
                        <fieldset>
                        <legend>Роли:</legend>
                        <div class="flex_c_r_ac flex_wr">';
                        
                        foreach ($rolesList as $v) {
                            $this->accountEditorHtml.='
                            <div class="flex_c_r">
                                <input type="checkbox" id="'.$v['type'].'" name="'.$v['type'].'" '.$v['checked'].'/>
                                <label for="'.$v['type'].'">'.$v['title'].'</label>
                            </div>';
                        }
                        $this->accountEditorHtml.='</div></fieldset>';

                        $this->accountEditorHtml.='
                        <div class="flex_c_r flex_wr">
                            <input type="submit" value="Подтвердить">
                        </div>
                    </div>
                </form>
            </div>
        </div>';
        return $this;
    }
    public function addUsersList($users,$goToUrl) : static 
    {
        $this->_set_css(['general.css','table.css','editor.css']);
        $this->usersListHtml='<div class="note">           
            <p>Все пользователи:'.($goToUrl!=''?' <a href="'.$goToUrl.'">Отменить поиск</a>':'').'</p>
            <div style="overflow-x: auto;">
            <table class="table mr_t_10">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Логин</th>
                    <th>Имя</th>
                    <th>Статус</th>
                    <th>Дата регистрации</th>
                    <th>Админ?</th>
                    <th>Автор?</th>
                    <th></th>
                </tr>
            </thead>
            '.$this->_getUsersTbody($users).'
            </table>
            </div>
            <a title="Добавить пользователя" id="add_qst_btn" class="qst_btn" href="'.\Base::instance()->get('BASE').'/editor/accounts/new/0"><img alt="Добавить вопрос" src="'.\Base::instance()->get('BASE').'/add_test.svg"></a>
        </div>';
        return $this;
    }
    public function addGoBackBtns() : static
    {
        $f3=\Base::instance();
        $this->_set_css(['general.css','buttons.css']);
        $this->backBtns='
        <div class="wrap_block">
            <div class="flex_c_r_ac flex_wr">
                <a class="page_nums" href="'.$f3->get('BASE').'/profile">В профиль</a>
                <a class="page_nums" href="'.$f3->get('BASE').'">На главную</a>
                <a class="page_nums" href="'.$f3->get('BASE').'/profile/staticstics">К статистике</a>
            </div>
        </div>';
        return $this;
    }
    public function addPageNavigation($pageHtml) : static
    {
        $this->_set_css(['general.css','buttons.css']);
        $this->paginatorTop='<div class="note">
            <div class="w_100 page_block">
                <hr>
                <p>Перейти на страницу:</p>
                    '.$pageHtml.'
            </div>
        </div>';

        $this->paginatorBottom='</div>
        <div class="wrap_block">
            <div class="page_block">
                <p>Перейти на страницу:</p>
                '.$pageHtml.'
            </div>
        </div>';
        return $this;   
    }
    //возвращает tbody
    protected function _getUsersTbody($result){
        $this->_set_css(['general.css','table.css']);
        $this->_set_js(['account.js']);
        $f3=\Base::instance();
        $html_txt='<tbody>';
        foreach ($result as $v) {
            
            $html_txt.='<tr class="test_line">';
            foreach ($v as $k=>$va) {
                if($k==='pass') continue;
                $html_txt.='<td>'.$va.'</td>';
            }
             $html_txt.='
             <td>
                <div class="flex_fe_r_ac">
                    <div class="test_btn mr_r_10">
                        <a title="Изменить" href="'.$f3->get("BASE").'/editor/accounts/change/'.$v['id'].'"><img alt="Изменить" src="'.$f3->get("BASE").'/change_test.svg"></a>
                    </div>
                    <div class="test_btn"> 
                        <a title="Удалить" class="delete_user_btn_js" href="'.$f3->get("BASE").'/user/'.$v['id'].'"><img alt="Удалить" src="'.$f3->get("BASE").'/minus_test.svg"></a>
                    </div>
                </div>
            </td>
            </tr>';
        }
        $html_txt.='</tbody>';
        return  $html_txt;
    }
    /**
     * <p>Добавляет разметку поиска</p>
     *
     * @param  mixed $searchUrl
     * @return static
     */
    public function addSearch($searchUrl='') : static
    {
        $this->_set_css(['general.css','flexable.css','search.css']);
        \Base::instance()->set('parameter',$searchUrl);
        $this->searchHtml='<div class="wrap_block">
        '.\Template::instance()->render('search.htm').'
        </div>';
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
            '<section class="content">
            
            '.$this->searchHtml.'
            '.$this->paginatorTop.'
            '.$this->usersListHtml.'
            '.$this->paginatorBottom.'
            '.$this->accountEditorHtml.'
            '.$this->backBtns.'
            </section>'.
            $this->errorModalWrap.
            $this->footer.
            $this->js2link($f3->get("BASE"),$this->js).
        '</body>';
    }
}
?>