<?php namespace view;

use view\PageAbstract;
use model\Security;
use view\Statistics as ST;
use Stringable;
use Template;

final class ProfilePage extends PageAbstract
{
    public static $i;
    public string $burgerMenu='';
    public string $content='';
    public string $cntrlPanelHtml='';
    public string $scrollToTopBtn='';
    public string $commentForm='';
    public string $noNotesHtml='';
    public string $searchHtml='';
    public string $testResultsHtml='';
    public string $myTestsHtml='';
    public string $variantsLinkModalHtml='';
    public string $goToTestModalHtml='';
    public string $userInfo='';
    public string $modalTestBackupLoad='';
    public string $page_html='';// переключение между статистика/ ваши записи/ подписки/ тесты.

    public string $b_a_html='';
    public string $f_a_html='';


    public static function i(): static {
        if (!(static::$i instanceof static)) {
            static::$i = new static();
        }
        return static::$i;
    }
    //Навигация в верхней части профиля
    public function addNavigation(string $pageName='notes'):static
    {
        $f3=\Base::instance();
        $page_style=array(
            'statistics'=>'page_nums_rev',
            'courses'=>'page_nums_rev',
            'notes'=>'page_nums_rev',
            'tests'=>'page_nums_rev',
            'subscribes'=>'page_nums_rev'
        );
            
      //Переключение активной кнопки Статистика ваши записи, Ваши подписки, Все авторы, Курсы.
        $page_style[$pageName]='page_nums';
        
        $this->page_html = $f3->get('user.isAuthor') || $f3->get('user.isAdmin') ? 
        '<a class="prof_op_btn prof_op_page '.$page_style['statistics'].'" href="'.$f3->get('BASE').'/profile/statistics">Статистика</a>
        <a class="prof_op_btn prof_op_page '.$page_style['courses'].'" href="'.$f3->get('BASE').'/profile/courses">Курсы</a>'
        :
        '';
        $this->page_html.=
        '<a class="prof_op_btn prof_op_page '.$page_style['notes'].'" href="'.$f3->get('BASE').'/profile/notes">Ваши записи</a>
        <a class="prof_op_btn prof_op_page '.$page_style['subscribes'].'" href="'.$f3->get('BASE').'/profile/subscribes/current">Подписки</a>
        <a class="prof_op_btn prof_op_page '.$page_style['tests'].'" href="'.$f3->get('BASE').'/profile/tests">Тесты</a>';
        return $this;
    }
    public function addStatistic(ST $st) : static
    {
        $this->_set_css(['buttons.css','general.css']);
        $this->content='<div class="note">'.$st.'</div>';
        return $this;
    }

    public function addBecomeAuthorHelper() : static
    {
        $this->_set_css(['general.css','flexable.css']);
        $this->b_a_html=Template::instance()->render('becomeAuthorHelper.htm');
        return $this;
    }
    public function addCreateCourseHelper() : static
    {
        $this->_set_css(['general.css','flexable.css']);
        $this->b_a_html=Template::instance()->render('createCourseHelper.htm');
        return $this;
    }
    public function addFindAuthorHelper() : static
    {
        $this->_set_css(['general.css','flexable.css']);
        $this->f_a_html=Template::instance()->render('findAuthorHelper.htm');
        return $this;
    }
    public function addProfileInfo() : static
    {
        $this->_set_css(['general.css','profile.css','buttons.css']);
        
        $this->userInfo='<section class="left_block">'.
        Template::instance()->render('profileInfo.htm').
        '</section>';
        return $this;
    }
    public function addNoNotesHelper(\Base &$f3) : static
    {
        $this->_set_css(['general.css']);
        $this->content='<div class="note"><p class="mr_b_10">
				Вы еще не создали ни одной записи, вы можете попробовать '.(!$f3->get('user.isAuthor')?'<a href="'.$f3->get('BASE').'/profile/become_author">стать автором</a>':'<a href="'.$f3->get('BASE').'/editor/note/new/0">написать свою первую заметку</a>').'
			</p></div>';
        return $this;
    }
    public function addNoCoursesHelper(\Base &$f3) : static
    {
        $this->_set_css(['general.css']);
        $this->content='<div class="note">
            <p class="mr_b_10">
				Вы еще не создали ни одного курса, вы можете попробовать <a href="'.$f3->get('BASE').'/editor/course/new/0">создать его</a>
			</p>
            </div>';
        return $this;
    }
    public function addNoMyCoursesHelper(\Base &$f3) : static
    {
        $this->_set_css(['general.css']);
        $this->content='<div class="note">
            <p class="mr_b_10">
				Вы еще не подали заявки ни на один курс, вы можете попробовать <a href="'.$f3->get('BASE').'/profile/subscribes/all">найти автора</a>
			</p>
            </div>';
        return $this;
    }
    public function addNoAuthorHelper(\Base &$f3) : static
    {
        $this->_set_css(['general.css']);
        $this->content='<div class="note">
            <p class="mr_b_10">
				Вы еще не подписаны ни на одного автора, вы можете попробовать <a href="'.$f3->get('BASE').'/profile/subscribes/all">поискать их среди существующих</a>
			</p>
            </div>';
        return $this;
    }
    public function addNotes(\view\Notes $notes) : static
    {
        $this->js=array_merge($this->js, $notes->js);
        $this->css=array_merge($this->css, $notes->css);

        $this->content=
        '<div class="w_100 note">
            <p>Всего записей: '.$notes->count.'</p>
            '.$notes->dropSearchHtml.'
            <div class="w_100 page_block">
                <hr>
                <p>Перейти на страницу:</p>
                    '.$notes->pageHtml.'
            </div>
        
        </div>
        <div>'.implode('',$notes->notesHtmlList).'</div>'.
        '
        <div class="wrap_block">
            <div class="page_block">
                <p>Перейти на страницу:</p>
                '.$notes->pageHtml.'
            </div>
        </div>';
        return $this;
    }
    // Панель управления записями
    public function addNotesPanel(\Base &$f3) : static
    {
        $this->_set_css(['general.css','flexable.css','buttons.css']);
        $this->cntrlPanelHtml='
        <div class="note"><div class="flex_c_r"><p class="good_txt italyc">Панель управления</p></div><hr>
                <div class="flex_c_r_ac">
                    <a class="page_nums_rev" href="'.$f3->get('BASE').'/editor/note/new/0">Новая запись</a>
                    <a class="page_nums_rev" href="'.$f3->get('BASE').'/editor/files">Фото / Файлы</a>
                </div>
            </div>
        ';
        return $this;
    }
    // Панель управления записями    
    /**
     * addCntrlPanel
     *
     * @param  array $vd = [['class', 'url','txt'], ...]
     * @return static
     */
    public function addCntrlPanel(array $vd) : static
    {
        $this->_set_css(['general.css','flexable.css','buttons.css']);
        $this->cntrlPanelHtml='
        <div class="note"><div class="flex_c_r"><p class="good_txt italyc">Панель управления</p></div><hr><div class="flex_c_r_ac">';
        foreach ($vd as $v) {
            $this->cntrlPanelHtml.='<a class="'.$v['class'].'" href="'.$v['url'].'">'.$v['txt'].'</a>';
        }
        $this->cntrlPanelHtml.='</div></div>';
        return $this;
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
        '.Template::instance()->render('search.htm').'
        </div>';
        return $this;
    }
    public function addScrollToTop(): static
    {
        $this->_set_css(['general.css','modal.css']);
        $this->_set_js(['scroll.js']);
        $this->scrollToTopBtn=Template::instance()->render('arrowUpBtn.htm'); 
        return $this;
    }
    public function addAuthors(Authors $authors) : static 
    {
        $this->js=array_merge($this->js, $authors->js);
        $this->css=array_merge($this->css, $authors->css);

        $this->_set_css(['general.css','buttons.css']);

        $this->content='
        <div class="w_100 note">
            <p>Всего авторов: '.$authors->count.'</p>
            '.$authors->dropSearchHtml.'
            <div class="w_100 page_block">
                <hr>
                <p>Перейти на страницу:</p>
                    '.$authors->pageHtml.'
            </div>
        
        </div>
        '.$authors->content.'
        <div class="wrap_block">
            <div class="page_block">
                <p>Перейти на страницу:</p>
                '.$authors->pageHtml.'
            </div>
        </div>
        ';
        return $this;
    }
    public function addCourses(Courses $courses) : static 
    {
        $this->js=array_merge($this->js, $courses->js);
        $this->css=array_merge($this->css, $courses->css);

        $this->_set_css(['general.css','buttons.css']);
        $this->content='
        <div class="w_100 note">
            <p>Всего курсов: '.$courses->count.'</p>
            '.$courses->dropSearchHtml.'
            <div class="w_100 page_block">
                <hr>
                <p>Перейти на страницу:</p>
                    '.$courses->pageHtml.'
            </div>
        
        </div>
        '.$courses->content.'
        <div class="wrap_block">
            <div class="page_block">
                <p>Перейти на страницу:</p>
                '.$courses->pageHtml.'
            </div>
        </div>
        ';        

        return $this;
    }
    public function addRequests(array $rqsts,Courses $courses,$count=0) : static 
    {
        $this->js=array_merge($this->js, $courses->js);
        $this->css=array_merge($this->css, $courses->css);

        $this->_set_css(['general.css','buttons.css']);


        foreach ($rqsts as $v) {
            $this->content.='<div id="rqst_'.$v['user_id'].'" class="flex_sb_r_ac flex_wr">
                <div>
                    <div class="flex_sb_r_ac">
                        <div class="ava ava-rounded ava-small mr_r_10">
                            <img id="img_'.$v['user_id'].'" src="'.$v['ava_url'].'">
                        </div>
                        <div>
                            <h2 class="comment_title">'.$v['name'].'</h2>
                        </div>
                    </div>
                </div>
                <div class="flex_sb_r">
                    <a class="cnfrm_sub_btn page_nums_rev" href="#" data-uid="'.$v['user_id'].'" data-cid="'.$v['course_id'].'">Принять</a>
                    <a class="cncl_sub_btn page_nums_red_rev" href="#" data-uid="'.$v['user_id'].'" data-cid="'.$v['course_id'].'">Отклонить</a>
                </div>
            </div>';
        }
        $this->content='<div>
            <p class="good_txt italyc ac_txt">Заявки</p>
            <hr>
            '.$this->content.'
        </div>';

        $this->content='
        '.$courses->content.'
        <div class="w_100 note">
            <p>Всего заявок: '.$count.'</p>
            '.$courses->dropSearchHtml.'
            <div class="w_100 page_block">
                <hr>
                <p>Перейти на страницу:</p>
                    '.$courses->pageHtml.'
            </div>
        
        </div>
        
        '.$this->content.'
        <div class="wrap_block">
            <div class="page_block">
                <p>Перейти на страницу:</p>
                '.$courses->pageHtml.'
            </div>
        </div>
        ';  
        return $this;
    }
    //отсутвует кнопка принять и слово щаменено на Участники
    public function addCourseSubscribes(array $rqsts,Courses $courses,$count=0) : static 
    {
        $this->js=array_merge($this->js, $courses->js);
        $this->css=array_merge($this->css, $courses->css);

        $this->_set_css(['general.css','buttons.css']);


        foreach ($rqsts as $v) {
            $this->content.='<div id="rqst_'.$v['user_id'].'" class="flex_sb_r_ac">
                <div>
                    <div class="flex_sb_r_ac">
                        <div class="ava_img cmnt_ava_img mr_r_10">
                            <img id="img_'.$v['user_id'].'" src="'.$v['ava_url'].'">
                        </div>
                        <div>
                            <h2 class="comment_title">'.$v['name'].'</h2>
                        </div>
                    </div>
                </div>
                <div class="flex_sb_r">
                    <a class="cncl_sub_btn page_nums_red_rev" href="#" data-uid="'.$v['user_id'].'" data-cid="'.$v['course_id'].'">Отклонить</a>
                </div>
            </div>';
        }
        $this->content='<div class="note">
            <p class="good_txt italyc ac_txt">Участники</p>
            <hr>
            '.$this->content.'
        </div>';

        $this->content=$courses->content.'
        <div class="w_100 note">
            <p>Всего участников: '.$count.'</p>
            '.$courses->dropSearchHtml.'
            <div class="w_100 page_block">
                <hr>
                <p>Перейти на страницу:</p>
                    '.$courses->pageHtml.'
            </div>
        
        </div>
        
        '.$this->content.'
        <div class="wrap_block">
            <div class="page_block">
                <p>Перейти на страницу:</p>
                '.$courses->pageHtml.'
            </div>
        </div>
        ';  
        return $this;
    }
    public function addTestResults(\Base &$f3,array $results,$goToUrl=''):static
    {
        $this->_set_css(['flexable.css','general.css']);
        //Попытки сдачи
        if(count($results)==0){
            $this->testResultsHtml='<p>Попыток не найдено</p>';
        }else{
            foreach ($results as $v) {
                $this->testResultsHtml.=$this->_GetResultWrap($v);
            }
        }

        $this->testResultsHtml='<div class="note">
            <div class="flex_sb_r_ac">
                <h2>Ваши попытки: '.($goToUrl!=''?'<a href="'.$goToUrl.'">Отменить поиск</a>':'').'</h2>
                <div class="test_btn">
                    <a class="do_try_btn" title="Добавить" href="#"><img alt="Добавить" src="'.$f3->get("BASE").'/add_test.svg"></a>
                </div>
            </div>
            <hr>
            '.$this->testResultsHtml.'
        </div>';
        
        return $this;    
    }
       
    public function addGoToTestModal(\Base &$f3) : static
    {
        $this->_set_css(['general.css','jquery.modal.min.css','search.css']);
        $this->_set_js(['test_profile.js']);

        $this->goToTestModalHtml='<div id="do_test_modal" class="modal">
                <p>
                    Вставте ссылку на тест в поле ниже:
                </p>
                <br>
                <div class="search_wrap">
                    <form method="GET" action="" class="flex_c_r" id="try_test">
                        <input type="text" required name="modal_test_link" id="modal_test_link" placeholder="Ссылка на тест">
                        <button type="submit" class="goto_test_btn"><img src="'.$f3->get('BASE').'/arrow_right.svg"></button>
                    </form>
                </div>
                
            </div>';
        return $this;    
    }
    //тесты созданные пользователем
    public function addMyTests(\Base &$f3,$tests,$goToUrl='') : static
    {
        $this->_set_css(['general.css','jquery.modal.min.css','search.css']);
        $this->_set_js(['test_profile.js']);

        //Пользовательские тесты
        $this->myTestsHtml='';
        foreach ($tests as $test) {
            $this->myTestsHtml.='
            <div class="flex_sb_r_ac test_line mr_t_10">
                <div>
                    <p>'.$test['title'].'</p><br>
                    '.($f3->get('user.isAdmin')?'
                    <p class="italyc_txt">Автор: '.$test['author_name'].'</p><br>
                    ':'').'
                    <p>
                        Н: <span class="italyc_txt">'.date('d.m.Y H:i:s',strtotime($test['start'])).'</span><br>
                        К:  <span class="italyc_txt">'.date('d.m.Y H:i:s',strtotime($test['end'])).'</span>
                    </p>
                    <a class="get_var_btn" href="'.$f3->get("BASE").'/variants/link/'.$test['id'].'">Ссылки для прохождения</>
                </div>
                <div class="flex_fe_r_ac">
                    <div class="test_btn mr_r_10">
                        <a title="Статистика прохождения теста" href="'.$f3->get("BASE").'/test/statistics/'.$test['id'].'/"><img alt="Статистика" src="'.$f3->get("BASE").'/stat_test.svg"></a>
                    </div>
                    <div class="test_btn mr_r_10">
                        <a title="Изменить тест" href="'.$f3->get("BASE").'/editor/test/id/'.$test['id'].'/"><img alt="Изменить" src="'.$f3->get("BASE").'/change_test.svg"></a>
                    </div>
                    <div class="test_btn">
                        <a title="Удалить тест" class="test_del_btn" href="'.$f3->get("BASE").'/delete/test/'.$test['id'].'/"><img alt="Удалить" src="'.$f3->get("BASE").'/minus_test.svg"></a>
                    </div>
                </div>
            </div>
        ';
        }
        
        $this->myTestsHtml=$this->myTestsHtml==''?'Тесты не найдены':$this->myTestsHtml.'<hr>';
                    
        $this->myTestsHtml='<div class="note">
            <div class="flex_sb_r_ac">
                <h2>Список тестов: '.($goToUrl!=''?'<a href="'.$goToUrl.'">Отменить поиск</a>':'').'</h2>
                <div class="flex_sb_r_ac">
                    <div class="test_btn mr_r_10">
                        <a title="Создать новый" href="'.$f3->get("BASE").'/editor/test/0"><img alt="Создать новый тест" src="'.$f3->get("BASE").'/add_test.svg"></a>
                    </div>
                    <div class="test_btn">
                        <a title="Загрузить существующий" тест href="#" id="upload_backup_btn_js"><img alt="Загрузить существующий тест" src="'.$f3->get("BASE").'/upl_test.svg"></a>
                    </div>
                </div>
            </div>
            <hr>
            '.$this->myTestsHtml.'
        </div>';
        return $this; 
    }
    //тесты созданные пользователем
    public function addVariantsLink() : static
    {
        $this->_set_css(['general.css','jquery.modal.min.css','search.css']);
        $this->_set_js(['test_profile.js','jquery.modal.min.js']);
        $this->variantsLinkModalHtml='<div id="ex_variants" class="modal">
                <p>
                    Названия вариантов и их ссылки для прохождения:
                </p>
                <br>
                <textarea rows="3" class="w_100" name="varlinks" placeholder="Название Варианта" value=""></textarea>
            </div>';
        return $this;
    }
    //тесты созданные пользователем
    public function addBackupLoad() : static
    {
        $this->_set_css(['general.css','jquery.modal.min.css']);
        $this->_set_js(['test_profile.js','jquery.modal.min.js']);
        $this->modalTestBackupLoad='<div id="load_test_backup_js" class="modal">
            <form method="POST" action="'.\Base::instance()->get('BASE').'/test/restore" enctype="multipart/form-data">
                <p>
                    Выберете zip архив загружаемого теста
                </p>
                <div  class="flex_sb_r_ac mr_t_10">
                    <input type="file" name="backup" id="backup">
                    <button type="submit" class="page_nums"> Отправить </button>
                </div>
            </form>
        </div>';
        
        return $this;
    }
    protected function _GetResultWrap(array $v)
    {
        $f3=\Base::instance();
        return '
            <a class="flex_sb_r_ac flex_wr test_line mr_t_10" href="'.$f3->get("BASE").'/check/'.$v['unique_url'].'">
                <p class="fs12_txt">'.$v['title'].' &#8226; '.$v['v_title'].'</p>
                <p>'.date('d.m.Y H:i:s',strtotime($v['created'])).'</p>
                '.(
                    $v['status']>0 ? 
                    '<p class="italyc_txt good_txt">Сдан</p>'
                    :
                    '<p class="italyc_txt alert_txt">Не сдан</p>'
                ).'
                
            </a>
        ';
    }
    //возвращает тег <body>
    public function body(): string
    {
        $f3=\Base::instance();
        return '<body>
            '.
            $this->header.
            $this->burgerMenu.

            '
        <section class="content ">
        <div id="scrollTarget" class="ClearFix">
            <article class="flex_c_r artcl_block">
                '.$this->userInfo.'
               
                <section class="center_page">
                    '.( $this->page_html != '' ?
                    '<div class="note page_block flex_c_r_ac flex_wr">
                        '.$this->page_html.'
                    </div>'
                    :
                    '' ).'
                    
            
                    '.$this->cntrlPanelHtml.'
                    '.$this->searchHtml.'
                    '.$this->content.'
                    '.$this->testResultsHtml.'
                    '.$this->myTestsHtml.'
                </section>
                <section class="right_block_profile" >
                    '.$this->b_a_html.'
                    '.$this->f_a_html.'
                    
                </section>
            </article>
        </div>
        </section>
        '.$this->commentForm.'
        
        </div>
        '.$this->goToTestModalHtml.'
        '.$this->variantsLinkModalHtml.'
        '.$this->scrollToTopBtn.'
        '.$this->errorModalWrap.'
        '.$this->modalTestBackupLoad.'
        '.$this->footer.
        $this->js2link($f3->get("BASE"),$this->js).
        '</body>';
    }
}

?>