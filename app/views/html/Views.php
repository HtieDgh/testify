<?php
/* public $html_dflt_title='Главная страница - Testify'; */
//Файл заменяет Представление
    class Views{
        public $html_head='';
        public static $f3;
        private $css=[];
        private $js=[];
        private $view_links='';

        public function __construct(&$f3) {
            static::$f3=$f3;
            $this->html_head='
                <link rel="stylesheet" type="text/css" href="'.static::$f3->get("SITE_DOMAIN").'reset.css">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
                <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&display=swap" rel="stylesheet">
                
                <link rel="stylesheet" type="text/css" href="'.static::$f3->get("SITE_DOMAIN").'kanit_f.css">
                    
                <link rel="apple-touch-icon" sizes="180x180" href="'.static::$f3->get("SITE_DOMAIN").'apple-touch-icon.png">
                <link rel="icon" type="image/png" sizes="32x32" href="'.static::$f3->get("SITE_DOMAIN").'favicon-32x32.png">
                <link rel="icon" type="image/png" sizes="16x16" href="'.static::$f3->get("SITE_DOMAIN").'favicon-16x16.png">
                <link rel="manifest" href="'.static::$f3->get("SITE_DOMAIN").'app/views/html/site.webmanifest">
                <meta name="msapplication-TileColor" content="#da532c">
                <meta name="theme-color" content="#ffffff">
                <script src="'.static::$f3->get("SITE_DOMAIN").'jquery-3.3.1.js"></script>
            ';
        }

        /**
         * <p>Возвращает тег <body> для html главной страницы</p>
         * @param string $h - DOMString хедера
         * @param array $arr_htmls - DOMString необходимых блоков  
         * @param string $f - DOMString футера
         * @return string DOMString
         */
        public function BodyMainPage($h='',$arr_htmls=[],$f=''){
            $a_html=implode('',$arr_htmls);
            return '
            <body>
            '.$h.'
            
                <div class="ClearFix">
                    
                        '.$a_html.'
                    
                </div>
               '.$f.'
            </body>';
        }
        /**
         * <p>Возвращает готовый html</p>
         * @param string для вывода 
         * @return string DOMString
         */
        public function Htmlrender($t,$b){
            if($t===null) throw new \Exception("Ошибка: title не назначен", 4);
            if($b===null) throw new \Exception("Ошибка: body не назначен", 5);
            
            return '<!DOCTYPE html>
                <html lang="ru">
                '.$this->Head($t).'
                '.$b.'
                </html>';
        }
        /**
         * <p>Возвращает тег <head> для DOMString</p>
         * @param string title Заголовок страницы
         * @param string head Доп теги
         * @return string тег <head>
         */
        public function Head($title=''){
            return '
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>'.$title.'</title>
                '.$this->html_head.$this->view_links.'
            </head>
            ';
        }
        
        /**
         * <p>Возвращает тег Header для страниц</p>
         * @param bool Если авторизован, выводит кнопку "выйти"
         * @return string тег <header>
         */
        public function Header($is_auth=FALSE){
             return '<header>
            <div class="header_line flex_sb_r_ac">
                <div class="flex_fs_r_ac ">
                    <div>
                        <a href="'.static::$f3->get("SITE_DOMAIN").'"><img src="'.static::$f3->get("SITE_DOMAIN").'logo.png" alt="Логотип сайта" class="logo"></a>
                    </div>
                    <div class="head_txt kanit-bold">
                        <h1><a href="'.static::$f3->get("SITE_DOMAIN").'">Testify</a></h1>
                    </div>
                </div>
                '.($is_auth?
                
                '<div class="mr">
                     <a href="exit/">Выйти</a>
                 </div>'
                 :
                 ''
                 ).'
            </div>
            </header>
        ';
        }

        public function Footer(){
            
            return '
            <div class="ClearFix">
                <footer>
                    <div class=" flex_sb_r_ac">
                        <div class="contacts"><a href="#">Руководство</a></div>
                        <div><p>Система дистанционного тестирования Testify.</p></div>
                        <div class="contacts">&copy; 2024</div>
                    </div>
                </footer>
            </div>
            
            ';
        }
        /**
         * <p>Возвращает форму авторизации</p>
         * @param html_txt сообщение пользователю. Например об ошибке.
         * @return string DOMString
        */
        public function Login($login_error=''){
            $this->_setCss(['flexable.css','color_theme.css','general.css','login.css']);
            $login_error=$login_error<>''?$login_error.'<br><br>':'';
            return '
                <div class="flex_c_r_ac welcome">
                    <div id="signin_form" class="ComeIn">
                        <form action="'.static::$f3->get("SITE_DOMAIN").'" method="POST" class="LogIn">	
                            <h2 class="LogInTxt" id="serverInfo">Введите ваш логин и пароль</h2>
                            
                        <div class="group">      
                            <input class="UserIn" name="login" type="text" required>
                            <span class="bar"></span>
                            <label>Логин</label>
                        </div>
                        <div class="group">      
                            <input  class="UserIn" name="password" type="password" required>
                            <span class="bar"></span>
                            <label>Пароль</label>
                        </div>
                        
                        <div class="Entering">
                            <input class="EnterBtn" type="submit" value="Войти">
                            <br><br>
                            <p class="alert_txt">'.$login_error.'</p>
                        </div>
                            <p id="reg_txt">Еще нет учетной записи? <a href="'.static::$f3->get("SITE_DOMAIN").'regist/'.'">Зарегистрироваться</a></p>
                        </form>
                    </div>
                </div>
            ';
        }
        /**
         * <p>Возвращает форму регистрации новго пользователя</p>
         * @param html_txt сообщение пользователю. Например об ошибке.
         * @return string DOMString
        */
        public function Rigist($html_txt='')
        {
       
            $this->_setCss(['flexable.css','color_theme.css','general.css','login.css']);
            $this->_setJs(['jquery-3.3.1.js','regist.js']);
            return '
            <div class="flex_c_r_ac welcome">
                <div id="signin_form" class="ComeIn">
                    <form action="'.static::$f3->get("SITE_DOMAIN").'" method="POST" class="LogIn">	
                        <h2 class="LogInTxt" id="serverInfo">Регистрация</h2>
                        <div class="group">      
                        <input id="s_n" class="UserIn" name="name" type="text" required>
                        <span class="bar"></span>
                        <label>Имя</label>
                    </div>
                    <div class="group">      
                        <input id="s_l" class="UserIn" placeholder="введите email" name="login" type="email" required>
                        <span class="bar"></span>
                        <label>email</label>
                    </div>
                    <div class="group">      
                        <input id="s_p" class="UserIn" name="password" type="password" required>
                        <span class="bar"></span>
                        <label>Пароль</label>
                    </div>
                    <div class="Entering">
                        <input class="EnterBtn" type="submit" value="Зарегистрироваться">
                        <br><br>
                        <p class="alert_txt">'.$html_txt.'</p>
                        </div>
                        Уже зарегистрированы? <a href="'.static::$f3->get("SITE_DOMAIN").'">Войти</a><br><br>
                    </form>
                </div>
            </div>
            ';
        }
        /**
         * <p>Возвращает разметку профиля пользователя</p>
         * @param array ассоциативный масив с необходимыми для вывода данными. Должен содержать ключи: s_ut search of user tests- текст предыдущего запроса по тестам; ut user_tests - данные созданых пользователем тестов; ava_url - src до user аватар; u - данные user_data модели Security; s_ur - search of user results текст поиска по результатам пользователя
         * @return string DOMString
        */
        public function Profile($view_data)
        {
         
            $this->_setCss(['flexable.css','color_theme.css','general.css','profile.css','https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.css']);
            $this->_setJs(['jquery-3.3.1.js','profile.js','https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.js']);
            //Пользовательские тесты
            $ut_html='';
            foreach ($view_data['ut'] as $test) {
                $ut_html.='
                <div class="flex_sb_r_ac test_line mr_t_10">
                    <div>
                        <p>'.$test['title'].'</p><br>
                        <p>
                            Н: <span class="italyc_txt">'.date('d.m.Y H:i:s',strtotime($test['start'])).'</span><br>
                            К:  <span class="italyc_txt">'.date('d.m.Y H:i:s',strtotime($test['end'])).'</span>
                        </p>
                        <a href="'.static::$f3->get("SITE_DOMAIN").'test/TODO">Ссылка для прохождения</>
                    </div>
                    <div class="flex_fe_r_ac">
                        <div class="test_btn mr_r_10">
                            <a title="Статистика прохождения теста" href="statistics/TODO/"><img alt="Статистика" src="stat_test.svg"></a>
                        </div>
                        <div class="test_btn mr_r_10">
                            <a title="Изменить тест"  href="editor/TODO/"><img alt="Изменить" src="change_test.svg"></a>
                        </div>
                        <div class="test_btn">
                            <a title="Удалить тест" class="test_del_btn" href="delete_test/TODO/"><img alt="Удалить" src="minus_test.svg"></a>
                        </div>
                    </div>
                </div>
            ';
            }
           
            $ut_html=$ut_html==''?'Вы еще не создали не одного теста':$ut_html.'<hr>';
            //Попытки сдачи
            $ur_html='';
            foreach ($view_data['ur'] as $v) {
                $ur_html.=$this->_GetResultWrap($v);
            }
            
            return '
            <div class="flex_se_r_afe content flex_wrr">
            '.($view_data['u']['access'] > 1 ? '
                <div class="u_t">
                        <div class="note">
                            <div class="tst_srch">
                                <form method="GET" action="blog.php" class="flex_c_r" id="test_search">
                                    <input type="text" required name="user_search" placeholder="'.$view_data['s_ut'].'">
                                    <button type="submit"><img src="search.png"></button>
                                </form>
                            </div>
                        </div>
                    
                        <div class="note">
                            <div class="flex_sb_r_ac">
                                <h2>Ваши тесты</h2>
                                <div class="flex_sb_r_ac">
                                    <div class="test_btn mr_r_10">
                                        <a title="Создать новый" href="edit/test/0"><img alt="Создать новый тест" src="add_test.svg"></a>
                                    </div>
                                    <div class="test_btn">
                                        <a title="Загрузить существующий" тест href=""><img alt="Загрузить существующий тест" src="upl_test.svg"></a>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            '.$ut_html.'
                        </div>
                    </div>
                ':'').'
                
                <div class="u_t">
                    <div class="note">
                        <div class="flex_c_r">
                            <div class="ava_img flex_c_c">
                                <img id="imgprof_'.$view_data['u']['id'].'" src="'.$view_data['u']['ava_url'].'">
                            </div>
                        </div>
                        <h1 class="profile_info">'.$view_data['u']['name'].'</h1>
                    </div>
                    <div class="note">
                        <div class="tst_srch">
                            <form method="GET" action="" class="flex_c_r" id="result_search">
                                <input type="text" required name="user_search" placeholder="'.$view_data['s_ur'].'">
                                <button type="submit"><img src="search.png"></button>
                            </form>
                        </div>
                    </div>
                    <div class="note">
                        <div class="flex_sb_r_ac">
                            <h2>Ваши попытки:</h2>
                            <div class="test_btn mr_r_10">
                                <a title="Пройти тест" href="#ex1" rel="modal:open"><img alt="Пройти тест" src="add_test.svg"></a>
                            </div>
                        </div>
                        
                        <hr>
                        '.$ur_html.'
                    </div>
                </div>
                
            </div>
            <div id="ex1" class="modal">
                <p>
                    Вставте ссылку на тест в поле ниже:
                </p>
                <br>
                <div class="tst_srch">
                    <form method="GET" action="" class="flex_c_r" id="try_test">
                        <input type="text" required name="modal_test_link" id="modal_test_link" placeholder="Ссылка на тест">
                        <button type="submit" class="goto_test_btn"><img src="arrow_right.svg"></button>
                    </form>
                </div>
                
            </div>
            '.($view_data['err_txt']!=''? 
                $this->_GetErrWrap($view_data['err_txt'])
                :
                ''
            );
        }
        /**
         * <p>Возвращает DOMString Редактора теста. Для идентификации ответа в закрытом вопросе: 1(вопрос)_1(id ответа)_qst_answ</p>
         * @param array Данные теста которые необходимо отредактировать.
         * @return string
        */
        public function TestEditor($td=null){
            $this->_setCss(['flexable.css','color_theme.css','general.css','decor_form.css','editor.css']);
            $this->_setJs(['jquery-3.3.1.js','editTest.js']);
            $html='';
            if(isset($td))
            {
                //Возврат разметки для уже существующего теста
                //Заголовок теста
                $html.=$this->_getEditorTestTitle($td['test']);

            }else{
                //Возврат разметки для нового теста
                //Заголовок теста
                $html.=$this->_getEditorTestTitle();
            }
            $html='<div class="content">
                <div>
                    <p class=" ar_txt">Шаг 1/4</p>
                </div>
                '.$html.'
                <div class="note">
                    <div class="flex_c_r ">
                        <a href="'.static::$f3->get("SITE_DOMAIN").'" id="confirm_edit_btn" class="confirm_edit_btn ac_txt">Следующий шаг</a>
                    </div>  
                </div>
                <div class="note">
                    <div class="flex_c_r ">
                        <a href="'.static::$f3->get("SITE_DOMAIN").'" id="cancel_edit_btn" class="confirm_edit_btn_alt ac_txt">Отмена</a>
                    </div>
                </div>
            </div>'.$this->_GetErrWrap('');
            return $html;
        }


        /**
         * <p>Возвращает DOMString Редактора теста. Для идентификации ответа в закрытом вопросе: 1(вопрос)_1(id ответа)_qst_answ</p>
         * @param array Данные теста которые необходимо отредактировать.
         * @return string
        */
        public function Editor($td=null)
        {
         
            $this->_setCss(['flexable.css','color_theme.css','general.css','decor_form.css','editor.css','https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.css','modal.css']);
            $this->_setJs(['jquery-3.3.1.js','editor.js','https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.js']);
            $html='';
            if($err_txt!==''){
                $html.=$this->_GetErrWrap($err_txt);
            }
            if(isset($td))
            {//Возврат разметки для уже существующего теста
                //Заголовок теста
                $html.='
                <div class="content">
                    '.$this->_getEditorTestTitle($td['test']);
                $q_count=count($td['question']);
                for ($i=0; $i < $q_count; $i++) { 
                    $html.=$this->_getEditorQuestion(
                        $td['question'][$i],
                        $td['answers'][$td['question'][$i]['id']],
                        $td['files'][$td['question'][$i]['id']],
                        $i+1,$q_count
                    );
                }
                
            }else{//Возврат разметки по умолчанию для нового теста
                $html.='
                <div class="content">
                    '.$this->_getEditorTestTitle().'
                    '.$this->_getEditorQuestion();
                
            }
            $html.='    
                <a title="Добавить вопрос: Двойной клик - добавить уже созданый" id="add_qst_btn" class="qst_btn" href="#"><img alt="Добавить вопрос" src="add_test.svg"></a>
            
                <div class="note">
                    <div class="flex_c_r ">
                        <a href="" id="confirm_edit_btn" class="confirm_edit_btn ac_txt">Завершить редактирование</a>
                    </div>
                </div>
            
            </div>
            
            <div id="ex1" class="modal">
                <p>
                    Ссылка на проохождение теста: <a href="" id="a_test_link"></a><br><br>
                    Прохождение теста будет доступно только для тех, у кого есть эта ссылка!
                </p>
                <br>
                <p>
                    Архив с тестом был закружен на ваше устройство в качестве резервной копии. Сохраните его в удобном для вас месте, позже вы сможете изменить этот тест, загрузив этот архив на странице профиля.
                    <br><br>
                </p>
                
                <a href="'.static::$f3->get("SITE_DOMAIN").'">Выйти из редактора</a>
            </div>
            
            <div id="ex2" class="modal">
                <p id="exept_txt">
                    
                </p>
                <br>
                <a href="#" rel="modal:close">Закрыть окно</a>
            </div>
            ';
            return $html;
        }
        /**
         * <p>Возвращает разметку для варианта теста</p>
         * @param array test_data['variant'] - данные варианта теста cu - новый или старый вариант. учавствует в create или в update
         * @return string DOMString
        */
        protected function _getEditorVariant($var=null)
        {
            if($var===null){
                $test=[
                    'title'=>'',
                    'cu'=>'new',
                ];
            }
            return '
            <div class="note">
                <div class="flex_c_r">
                    <form class="decor" method="post" action="/">
                        <div class="form-inner">
                        
                            <h3 class="italyc_txt">Название варианта теста</h3><br>
                            <input id="test_title" class="fs12_txt" type="text" name="test_title" placeholder="Название Варианта" value="'.$var['title'].'" required>
                            <br>
                            <p class="fs12_txt">
                                Название варианта может быть любым. Рекомендуется выбирать название в форме <span class="italyc_txt">Вариант <Номер варианта></span>
                            </p> 
           
                            <input type="hidden" id="test_cu" value="'.$var['cu'].'">
                            
                        </div>
                    </form>
                </div>
            </div>
            ';
        }
      /**
         * <p>Возвращает заголовок редактора теста</p>
         * @param array test_data['test'] - данные заголовка теста test_cu - новый или старый тест
         * @return string DOMString
        */
        protected function _getEditorTestTitle($test=null)
        {
            if($test===null){
                $test=[
                    'id'=>'0',
                    'title'=>'',
                    'limit'=>'1',
                    'description'=>'',
                    'start'=>date("Y-m-d H:i"),
                    'end'=>date("Y-m-d H:i",time()+60*60*24*7),
                    'cu'=>'new'
                ];
            }
            return '<div class="note">
                        <div class="flex_c_r">
                            <form class="decor" method="post" action="/">
                                <div class="form-inner">
                                
                                    <h3 class="italyc_txt">Редактор Теста</h3><br>
                                    <input id="test_title" class="fs12_txt" type="text" name="test_title" placeholder="Название Теста" value="'.$test['title'].'" required>
                                    <div class="flex_fe_r_ac">
                                        <label class="mr_r_10" for="limit">Минимум баллов для прохождения:</label>
                                        <input id="limit" type="number" name="limit" min="1" max="9999" value="'.$test['limit'].'">
                                    </div>
                                    <textarea name="test_description" id="test_description"  class="edit_txt" placeholder="Описание теста" rows="5" required>'.$test['description'].'</textarea>
                                    
                                
                                    <p class="ac_txt">Текущая дата: '.date('d.m.Y').' </p>
                                    <p class="mr_l_10">Начать тестирование с:</p>
                                    <input type="datetime-local" id="start" name="start" value="'.$test['start'].'" min="'.date("Y-m-d H:i").'" max="'.date(DATE_ATOM,time()+60*60*24*365).'">
                                    <p class="mr_l_10">по:</p>
                                    <input type="datetime-local" id="end" name="end" value="'.$test['end'].'" min="'.date("Y-m-d H:i").' max="'.date("Y-m-d H:i",time()+60*60*24*365).'">
                                    <br>
                                    <input type="hidden" id="test_cu" value="'.$test['cu'].'">
                                    <input type="hidden" id="test_id" value="'.$test['id'].'">
                                </div>
                            </form>
                        </div>
                    </div>';
        }

        /**
         * <p>Возвращает разметку Вопроса</p>
         * @param array $q - test_data['question'][i] данные текущего вопроса
         * @param array $f - test_data['file'][i] информация о файлах
         * @param array $a - test_data['answer'][i] данные ответов на этот вопрос
         * @param array $q_cur_num - текущий номер вопроса
         * @param array $q_count - всего вопросов в тесте
         * @return string DOMString
        */
        protected function _getEditorQuestion($q=null,$a=null,$f=null,$q_cur_num=1,$q_count=1)
        {
            if($q===null){
                $q=[
                    'id'=>0,
                    'title'=>'',
                    'is_open'=>0,
                    'text'=>'',
                    'is_vid_hidden'=>0
                ];
                $a[0]=[
                    'text'=>'',
                    'price'=>'0',
                    'fine'=>'0'
                ];
                $f=[];
            }


            $html='
            <div id="'.$q['id'].'_q" class="note">
                <div class="flex_sb_r">
                    <form class="decor" method="post" action="new_test/">
                        <div class="form-inner">

                            <input type="text" class="fs12_txt qst_title" name="qst_title" placeholder="Заголовок" value="'.$q['title'].'" required>
                            <div class="flex_sb_r flex_wr">
                                <div class="qst_type flex_sb_r_ac">
                                    <div class="flex_fs_r_ac">
                                        <input  class="mr_r_10" type="radio" id="'.$q['id'].'_type" name="type" value="0" '.($q['is_open']==1?'':'checked').' />
                                        <label for="'.$q['id'].'_type">Закрытый</label>
                                    </div>

                                    <div class="flex_fs_r_ac">
                                        <input class="mr_r_10" type="radio" id="'.$q['id'].'_2_type" name="type" value="1" '.($q['is_open']==1?'checked':'').'/>
                                        <label for="'.$q['id'].'_2_type">Открытый</label>
                                    </div>
                                </div>
                                
                            </div>

                            <textarea name="note_txt" class="edit_txt qst_txt" placeholder="Текст вопроса" rows="4" required>'.$q['text'].'</textarea>
                            <div class="flex_sb_r_ac flex_wr">
                                <input class="file_in UserIn" id="'.$q['id'].'_files" accept="image/*,video/*,audio/*" name="user_files[]" type="file" multiple>
                                <div class="flex_fs_r_ac">

                                    <input class="mr_r_10" type="checkbox" name="is_vid_hidden" id="'.$q['id'].'_is_vid_hidden" '.($q['is_vid_hidden']==1?'checked':'').'>
                                    <label for="'.$q['id'].'_is_vid_hidden">Скрыть видео?</label>
                                </div>
                            </div>';
                                //Файлы
                                $f_count=count($f);
                                if($f_count>0){
                                    $html.='<p>Файлы ниже были использованы в вопросе:<br><br></p>';
                                    for ($i=0; $i < $f_count; $i++) { 
                                        $html.='<p class="italyc_txt">'.$f[$i]['file_name'].'</p>';
                                    }
                                    $html.='<p class="alert_txt"><br>Перед тем как Завершить редактирование выберете эти файлы заново</p>';
                                }
                                
                            
                                
                            $html.='<div class="mr_t_10">
                                <p class="ac_txt">Варианты ответов:</p>';
                                $answ_count=count($a);
                                $html.='
                                <input id="'.$q['id'].'_answ_count" type="hidden" name="answ_count" value="'.$answ_count.'">
                                <hr id="'.$q['id'].'_answ_list">
                                ';
                                
                                    //Перебор ответов
                                    if($q['is_open']==1){//Если вопрос открытый
                                        $html.='
                                        <div class="'.$q['id'].'_qst_answ flex_fs_r_ac">
                    
                                            <textarea rows="1" class=" mr_r_10" name="'.$q['id'].'_0_qst_answ" placeholder="Текст ответа" value="'.$a[0]['text'].'" required>'.$a[0]['text'].'</textarea>
                                            
                                            <input type="number" name="price" min="-1000" max="1000" value="'.$a[0]['price'].'">
                                            <input type="number" name="fine" style="display:block;" min="-1000" max="1000" value="'.$a[0]['fine'].'">
                            
                                        </div>';
                                    }else{//закрытый
                                        foreach ($a as $k=>$v) {
                                            
                                            $html.='
                                            <div class="'.$q['id'].'_qst_answ flex_fs_r_ac">
                                            
                                                <span class="fs14_txt answ_number mr_r_10">1</span>
                                                <textarea rows="1" class=" mr_r_10" name="'.$q['id'].'_'.
                                                $k.'_qst_answ" placeholder="Текст ответа" value="'.
                                                $v['text'].'" required>'.$v['text'].'</textarea>
                                                
                                                <input type="number" name="price" min="-1000" max="1000" value="'.$v['price'].'">
                                                <input type="number" name="fine" min="-1000" max="1000" value="'.$v['fine'].'">
                                                <a title="Удалить вариант ответа" class="qst_btn_alt del_answ_btn" href="'.$q['id'].'"><img alt="Удалить вариант ответа" src="minus_test.svg"></a>
                                            
                                            </div>
                                            ';
                                        }
                                        $html.='<a  title="Добавить вариант ответа" class="qst_btn add_answ_btn" href="'.$q['id'].'"><img alt="Добавить вариант ответа" src="add_test.svg"></a>';
                                    }
                                $html.='
                                
                            </div>
                            
                        </div>
                    </form>
                    <div class="flex_fs_c_ac">
                        <div class="qst_number ac_txt fs12_txt">
                            <span class="cur_all_qsts">'.$q_cur_num.'</span>/<span class="all_qsts">'.$q_count.'</span>
                        </div>
                        
                        <a title="Удалить вопрос из теста" class="qst_btn_alt del_qst_btn" href="'.$q['id'].'">
                            <div class="flex_c_c">
                                <img alt="Удалить вопрос из теста" src="minus_test.svg">
                            </div>
                        </a>
                        
                    </div>
                    
                </div>
            </div>';
            return $html;
        } 
        
        
        
        /**
         * <p>Возвращает разметку Плеера для теста</p>
         *
         * @param null $test
         * @param null $err_txt
         * 
         * @return string DOMString
         * 
         */
        public function Test($test_data=null, $user_path='', $err_txt = null)
        {
            $this->_setCss(['flexable.css','color_theme.css','general.css','decor_form.css','test.css','https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.css']);
            $this->_setJs(['jquery-3.3.1.js','test.js','https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.js']);
            $html='';
            if($err_txt!==''){
                $html.=$this->_GetErrWrap($err_txt);
            }
            if($test_data!=null){
                $html.='
                    <div class="content">
                        <div class="note">
                            <div class="flex_c_r">
                                <form class="decor" action="">
                                    <div class="form-inner">
                                    <input type="hidden" id="test_link" name="test_link" value="'.$test_data['test']['link'].'">
                                        <h3 class="italyc_txt">'.$test_data['test']['title'].'</h3><br>
                                        <div class="flex_fe_r_ac">
                                            <p class="mr_r_10">Минимум баллов для прохождения:</p>
                                            <span>'.$test_data['test']['limit'].'</span>
                                        </div>
                                        <p class="ac_txt mr_t_10">Текущая дата: '.date('d.m.Y').' </p>

                                        <hr>
                                        <p class="ac_txt mr_t_10">Данный тест проводится</p>
                                        <div class="flex_sb_r_ac">
                                            <p class="mr_r_10">с: </p>
                                            <span>'.$test_data['test']['start'].'</span>
                                        </div>
                                        <div class="flex_sb_r_ac">
                                            <p class="mr_r_10">по:</p>
                                            <span>'.$test_data['test']['end'].'</span>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="note">
                            <p>Описание:</p>
                            <p class="mr_t_10">'.$test_data['test']['description'].'</p>
                        </div>';
                        
                $html.='<div >';

                    $q_count = count($test_data['question']);

                    for ($i=0; $i < $q_count; $i++)
                    { 
                        $html.=$this->_getTestQuestion(
                            $test_data['question'][$i],
                            $test_data['answers'][$test_data['question'][$i]['id']],
                            $test_data['files'][$test_data['question'][$i]['id']],
                            $user_path.$test_data['test']['link'],
                            $i+1,$q_count
                        );
                        $q_ids[]=['id'=>$test_data['question'][$i]['id'],'is_open'=>$test_data['question'][$i]['is_open']];
                    }
                    
                $html.='</div>
                        <div class="note">
                            <div class="flex_c_r ">
                                <a href="#" class="ac_txt start_test_btn" data-questions=\''.json_encode($q_ids).'\' data-q-count="'.$q_count.'">Начать тест</a>
                            </div>
                        </div>
                    </div>
                ';
            }
           
            return $html;
        }
        
        /**
         * <p>Возвращает разметку вопроса для Плеера теста</p>
         *
         * @param mixed $q=null - test_data['question']
         * @param mixed $a=null - test_data['answer'][q_id]
         * @param mixed $f=null - test_data['file'][q_id]
         * @param mixed $test_path='' - путь до файлов теста
         * @param mixed $q_cur_num=1 - текущий номер вопроса
         * @param mixed $q_count=1 - всего вопросов
         * 
         * @return string DOMstring
         * 
         */
        protected function _getTestQuestion($q=null, $a=null, $f=null, $test_path='', $q_cur_num=1,$q_count=1)
        {
            $html='';
            $html.='
            <div id="'.$q['id'].'_q" class="note disp_none transparent">

                <div class="flex_sb_r">
                    <form class="decor" action="">
                        <div class="form-inner">
                        <h3>'.$q['title'].'</h3>
                        <p class="mr_t_10">'.$q['text'].'</p>
                        
                        <div class="flex_se_r flex_wr">
                        ';
                
                foreach ($f as $k=>$v) {
                    $html.=' <div class="file_wrap mr_t_10">';
                    switch ($v['mime']) {
                        case 'image':
                            $html.='<img src="../'.$test_path.'/'.$v['file_name'].'" alt="">';
                            break;

                        case 'audio':
                            $html.='<audio src="../'.$test_path.'/'.$v['file_name'].'" controls></audio>';             
                            break;

                        case 'video':
                            $html.='<video src="../'.$test_path.'/'.$v['file_name'].'" '.($q['is_vid_hidden']==1?'class="invis " id="'.$q['id'].'_'.$k.'_v"':'').' controls></video>';
                            if($q['is_vid_hidden']==1){
                                $html.='<a id="'.$q['id'].'_'.$k.'" href="" class="open_video_btn">Показать?</a>';
                            }
                            
                            break;

                        default:
                            $html.='<p class="alert_txt italyc_txt">Ошибка: файл не определен</p>';
                            break;
                    }
                    
                    $html.='</div>';
                }
                
            $html.='</div><hr>';
            foreach ($a as $v) {
               
                if($q['is_open']==1){
                    $html.='<div class="'.$q['id'].'_qst_answ flex_fs_r_ac">

                        <input type="text" name="answ" id="'.$q['id'].'_'.$v['id'].'" value="" placeholder="Ваш ответ" class="mr_r_10">
                    </div>
                    ';
                }else{
                    $html.='<div class="'.$q['id'].'_qst_answ flex_fs_r_ac">
                        <input type="checkbox" name="answ" id="'.$q['id'].'_'.$v['id'].'" value="'.$v['id'].'" class="mr_r_10">
                        <label for="'.$q['id'].'_'.$v['id'].'">'.$v['text'].'</p>
                    </div>';
                }
                
            }
            $html.='
                    </div>
                    </form>
                    <div class="qst_number ac_txt fs12_txt">
                        <span class="cur_all_qsts">'.$q_cur_num.'</span>/<span class="all_qsts">'.$q_count.'</span>
                    </div>
                </div>
                
            </div>';
            return $html;
        }

        public function Check($res_data,$err_txt='')
        {
            $this->_setCss(['flexable.css','color_theme.css','general.css','decor_form.css','check.css','editor.css']);
            
                $html='';
                if($err_txt!==''){
                    $html.=$this->_GetErrWrap($err_txt);
                    $this->_setJs(['jquery-3.3.1.js','https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.js']);
                    $this->_setCss(['https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.css']);
                }
                
                $html.='
                <div class="content">
                    <div class="note">
                        <div class="flex_c_r">
                            <form class="decor" action="">
                                <div class="form-inner">
                                
                                    <h3 class="italyc_txt">'.$res_data[0]['title'].'</h3><br>
                                    <div class="flex_fe_r_ac">
                                        <p class="mr_r_10">Минимум баллов для прохождения:</p>
                                        <span>'.$res_data[0]['limit'].'</span>
                                    </div>
                                    <p class="ac_txt mr_t_10">Текущая дата: '.date('d.m.Y').' </p>

                                    <hr>
                                    <p class="ac_txt mr_t_10">Данный тест проводится</p>
                                    <div class="flex_sb_r_ac">
                                        <p class="mr_r_10">с: </p>
                                        <span>'.$res_data[0]['start'].'</span>
                                    </div>
                                    <div class="flex_sb_r_ac">
                                        <p class="mr_r_10">по:</p>
                                        <span>'.$res_data[0]['end'].'</span>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="note">
                        <p>Описание:</p>
                        <p class="mr_t_10">'.$res_data[0]['description'].'</p>
                    </div>
                    <div class="note">
                        <div class="flex_sb_r_ac">
                            <h2>Ваши результаты:</h2>
                            <p class="mr_r_10">Баллы</p>
                        </div>
                        <hr>';
                
                    foreach ($res_data as $k=>$v) {
                        $html.=$this->_GetDetailResultWrap($k,$v);
                    }
                        $html.='
                        <a title="Пройти тест еще раз" id="add_qst_btn" class="qst_btn" href="'.static::$f3->get("SITE_DOMAIN").'test/'.$res_data[0]['link'].'"><img alt="Пройти тест" src="add_test.svg"></a>      
                    </div>
                ';
                $html.='</div>';
                return $html;
        }

        public function Statistics($view_data, $res_data, $err_txt='')
        {
           
            $this->_setCss(['flexable.css','color_theme.css','general.css','decor_form.css','check.css']);
            $html='';
            if($err_txt!=='')
            {
                $html.=$this->_GetErrWrap($err_txt);

            }
            if($res_data==null)
            {
                $res_data=[
                    0=>[
                        'title'=>'',
                        'limit'=>'_',
                        'start'=>'_',
                        'end'=>'_',
                        'description'=>'_'
                    ]
                ];
            }


            
            $html.='
            <div class="content">
                <div class="note">
                    <div class="flex_c_r">
                        <form class="decor" action="">
                            <div class="form-inner">
                            
                                <h3 class="italyc_txt">'.$res_data[0]['title'].'</h3><br>
                                <div class="flex_fe_r_ac">
                                    <p class="mr_r_10">Минимум баллов для прохождения:</p>
                                    <span>'.$res_data[0]['limit'].'</span>
                                </div>
                                <p class="ac_txt mr_t_10">Текущая дата: '.date('d.m.Y').' </p>

                                <hr>
                                <p class="ac_txt mr_t_10">Данный тест проводится</p>
                                <div class="flex_sb_r_ac">
                                    <p class="mr_r_10">с: </p>
                                    <span>'.date('d.m.Y H:i:s',strtotime($res_data[0]['start'])).'</span>
                                </div>
                                <div class="flex_sb_r_ac">
                                    <p class="mr_r_10">по:</p>
                                    <span>'.date('d.m.Y H:i:s',strtotime($res_data[0]['end'])).'</span>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
                <div class="note">
                    <p>Описание:</p>
                    <p class="mr_t_10">'.$res_data[0]['description'].'</p>
                </div>
                
                <div class="note">
                <div class="tst_srch">
                        <form method="GET" action="'.static::$f3->get("SITE_DOMAIN").'statistics/'.$res_data[0]['link'].'" class="flex_c_r">
                            <input type="text" required name="results_search" placeholder="'.$view_data['s_rslts'].'">
                            <button type="submit"><img src="'.static::$f3->get("SITE_DOMAIN").'search.png"></button>
                        </form>
                        
                    </div>
                    <br>
                    <div class="flex_sb_r_ac">
                        <h2 class="mr_t_10">Результаты пользователей: '.$view_data['s_cancel_btn'].'</h2>
                        <p class="mr_r_10">Баллы</p>
                    </div>
                <hr>
                ';
                foreach ($res_data as $k=>$v) {
                    $html.=$this->_GetDetailResultWrap($k,$v);
                }
            $html.='</div>
            </div>';
            return $html;
        }


        /**
         * <p>Возвращает оболочку с текстом ошибки для вывода на стороне клиента</p>
         * @param string $err_txt
         * @return [type]
         */
        protected function _GetErrWrap($err_txt)
        {
            $this->_setJs(['jquery-3.3.1.js','https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.js']);
            $this->_setCss(['https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.2/jquery.modal.min.css']);
            return '
                <div id="err_wrap" class="modal">
                    <p id="exept_txt">
                        '.$err_txt.'
                    </p>
                    <br>
                    <a href="'.static::$f3->get("SITE_DOMAIN").'">Обратно в профиль</a>
                </div>
                ';
        }

        protected function _GetDetailResultWrap($k,$v)
        {
            
            return '
                <div class="flex_sb_r_ac flex_wr test_line mr_t_10">
                    <span class="fs14_txt">'.($k+1).'</span>
                    '.(isset($v['user_name'])? '<p>'.$v['user_name'].'</p>' : '' ).'
                    <p>'.date('d.m.Y H:i:s',strtotime($v['date'])).'</p>
                    '.(
                        $v['status']>0 ? 
                        '<p class="italyc_txt good_txt">Сдан</p>'
                        :
                        '<p class="italyc_txt alert_txt">Не сдан</p>'
                    ).'
                       
                        <div class="fs14_txt">
                            <span >'.$v['sum'].'</span>/<span>'.$v['limit'].'</span>
                        </div>
                   
                </div>
            ';
            
        }

        protected function _GetResultWrap($v)
        {
            return '
                <a class="flex_sb_r_ac flex_wr test_line mr_t_10" href="'.static::$f3->get("SITE_DOMAIN").'check/'.$v['link'].'">
                    <p class="fs12_txt">'.$v['title'].'</p>
                    <p>'.date('d.m.Y H:i:s',strtotime($v['date'])).'</p>
                    '.(
                        $v['status']>0 ? 
                        '<p class="italyc_txt good_txt">Сдан</p>'
                        :
                        '<p class="italyc_txt alert_txt">Не сдан</p>'
                    ).'
                    
                </a>
            ';
        }
        

  
        /**
         * <p>Настраивает css <link> для <head> текущего представления</p>
         * @param array массив строк названий файлов css
        */
        protected function _setCss($css_a=[])
        {
          
            foreach ($css_a as $v) {
                if(!in_array($v,$this->css)){
                    $this->css[]=$v;
                    $this->view_links.='<link rel="stylesheet" type="text/css" href="'.(str_contains($v,'http')?'':static::$f3->get("SITE_DOMAIN")).$v.'">';
                }
            }
        }
        /**
         * <p>Настраивает js <link> для <head> текущего представления</p>
         * @param array массив строк из названий файлов js
        */
        protected function _setJs($js_a=[])
        {
          
            foreach ($js_a as $v) {
                if(!in_array($v,$this->js)){
                    $this->js[]=$v;
                    $this->view_links.='<script src="'.(str_contains($v,'http')?'':static::$f3->get("SITE_DOMAIN")).$v.'"></script>';
                }
            }
        }
    }
  
?>