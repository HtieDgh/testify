<?php namespace view;

use Template;

class NotesViewerPage extends PageAbstract
{
    public string $authorsBlockHtml='';//блок слева с подписками
    public string $authorPageHtml='';//Блок с информацией об авторе
    public string $subbedCourseBlockHtml='';//Блок с информацией о подписанных курсах
    public string $menuBtnHtml='';
    public string $scrollToTopBtn='';
    public string $commentFormHtml='';
    public Notes $notesView;//Обьект представления записей

    protected static $i;
    public static function i(): static {
		if (!(static::$i instanceof static)) {
			static::$i = new static();
		}
		return static::$i;
	}
   
    /**
     * Отображение всех текущих подписок в левой части главной стр
     *
     * @return void
     */
    public function addAuthorsBlock( array $authors=[], int $author_id=0) : static
    {
        $f3=\Base::instance();
        $this->_set_css(['general.css']);
        if($f3->get('user.isAuth')){//пользователь авторизован
            
            if(count($authors)>0){
                foreach($authors as $a){
                    $this->authorsBlockHtml.='
                    <a class="author_href '.($a['id']==$author_id?'cur_author':'').'" href="'.$f3->get('BASE').'?a_id='.$a['id'].'">
                        <div class="author_wrap flex_fs_r_ac">
                            <div class="ava_prof_block">
                                <div class="ava_img">
                                    <img id="img_'.$a['id'].'" src="'.$a['ava_url'].'">
                                </div>
                            </div> 
                            <div>
                                <h2 class="note_title">'.$a['name'].'</h2>
                            </div>
                        </div> 
                    </a>
                    ';
                }
            }else{//пользователь еще не подписан ни на одного автора
                $this->authorsBlockHtml='
                    <p class="mr_t_10 mr_b_10 ac_txt">Вы еще не подписались ни на одного автора!</p>
                    <div class="flex_c_r"><a href="'.$f3->get('BASE').'/profile/subscribes/all" class="page_nums">Найти автора</a></div>
                ';
            }
        }else{//пользователь не авторизован
            $this->authorsBlockHtml='
            <p class="mr_t_10 mr_b_10 ac_txt">Чтобы подписаться на автора вам необходимо</p>
            <div class="flex_c_r"><a href="'.$f3->get('BASE').'/login/" class="page_nums">Войти</a></div>
        ';
        }
        $this->authorsBlockHtml='
            <div class="note"><div class="flex_c_r"><p class="good_txt italyc">Ваши подписки</p></div><hr>
                '.$this->authorsBlockHtml.'
            </div>
        ';
        return $this;
    }
    public function addSubbedCourseBlockHtml(array $courses,int $courseId=0): static
    {
        $f3=\Base::instance();
        $this->_set_css(['general.css']);
        if(count($courses)>0){
			foreach($courses as $a){
				$this->subbedCourseBlockHtml.='
				<a class="author_href '.($a['id']==$courseId?'cur_author':'').'" href="'.$f3->get('BASE').'?c_id='.$a['id'].'">
					<div class="author_wrap flex_fs_r_ac">
						<div class="ava_prof_block">
							<div class="ava_img">
								<img id="img_'.$a['id'].'" src="'.$a['ava_url'].'">
							</div>
						</div> 
						<div>
							<h2 class="note_title">'.$a['title'].'</h2>
						</div>
					</div> 
				</a>
				';
			}
		}else{
			$this->subbedCourseBlockHtml='
				<p class="note_cntrl_btn mr_t_10 ac_txt">Вы еще не подали заявки ни на один курс!</p>
				<div class="flex_c_r mr_t_10"><a href="'.$f3->get('BASE').'/profile/subscribes/all" class="page_nums note_cntrl_btn">Найти автора</a></div>
			';
		}
		$this->subbedCourseBlockHtml='
			<div class="note"><div class="flex_c_r"><p class="good_txt italyc">Ваши Курсы</p></div><hr>
				'.$this->subbedCourseBlockHtml.'
			</div>
		';
        return $this;
    }

    public function addControlBtns() {
        global $f3;
        $this->_set_css(['flexable.css','general.css']);
        $this->menuBtnHtml='
        <div class="note">
            <a class="author_href" href="'.$f3->get('BASE').'">
                <div class="author_wrap flex_fs_r_ac">
                    <div class="ava_prof_block">
                        <div class="ava ava-small">
                            <img src="./home_icon.svg">
                        </div>
                    </div> 
                    <div>
                        <h2 class="note_title">Главная</h2>
                    </div>
                </div>
            </a>
            <a class="author_href" href="'.$f3->get('BASE').'?cur_sub=1">
            <div class="author_wrap flex_fs_r_ac">
                <div class="ava_prof_block">
                    <div class="ava ava-small">
                        <img src="./list_icon.svg"">
                    </div>
                </div> 
                <div>
                    <h2 class="note_title">Подписки</h2>
                </div>
            </div>
        </a>
        </div>
        ';
    }

    public function addNotes(\view\Notes $notes) 
    {
        $this->notesView=$notes;
        $this->js=array_merge($this->js, $this->notesView->js);
        $this->css=array_merge($this->css, $this->notesView->css);
    }
    public function addScrollToTop() {
        $this->_set_css(['general.css','modal.css']);
        $this->_set_js(['scroll.js']);
        $this->scrollToTopBtn=$this->scrollToTopBtn=Template::instance()->render('arrowUpBtn.htm');
    }
    public function addAuthorBlock(Authors $authors):static
    {
        $this->js=array_merge($this->js, $authors->js);
        $this->css=array_merge($this->css, $authors->css);
        $this->authorPageHtml=$authors->content;
        return $this;
    }
    public function addAuthorCourseBlock(Courses $courses):static
    {
        $this->js=array_merge($this->js, $courses->js);
        $this->css=array_merge($this->css, $courses->css);
        $this->authorPageHtml.=$courses->content;
        return $this;
    }
    public function body(): string
    {
        $f3=\Base::instance();
        return '
        <body>
            '.$this->header.$this->burgerMenu.'
            <section class="content">
            <div id="scrollTarget" class="ClearFix">
                '.$this->authorPageHtml.'
                <div class="w_100 note">
                    <p>Всего записей: '.$this->notesView->count.'</p>
                    '.$this->notesView->dropSearchHtml.'
                    <div class="w_100 page_block">
                        <hr>
                        <p>Перейти на страницу:</p>
                            '.$this->notesView->pageHtml.'
                    </div>
                
                </div>
                <article class="artcl_block flex_sb_r">
                    <section class="left_block">
                        '.$this->menuBtnHtml.'
                        '.$this->authorsBlockHtml.'
                        '.$this->subbedCourseBlockHtml.'
                    </section>
                    <section class="right_block">
                        '.implode('',$this->notesView->notesHtmlList).'
                    </section>
                </article>
                <div class="wrap_block">
                    <div class="page_block">
                        <p>Перейти на страницу:</p>
                            '.$this->notesView->pageHtml.'
                    </div>
                </div>
            </div>
            </section>
            '.$this->scrollToTopBtn.'
            '.$this->commentFormHtml.'
            '.$this->errorModalWrap.'
           '.$this->footer.
           $this->js2link($f3->get("BASE"),$this->js).
        '</body>'; 
    }
}


?>