<?php namespace controller;

use model\NoteProcessor;
use model\Security as S;
use model\CFuns;
use model\CourseProcessor;
use model\Subscriber;
use view\NotesViewerBuilder;
use view\NotesViewerPage;
use view\EmailFormPage;
use PHPMailer\PHPMailer\PHPMailer;
use view\NoteEditorPage;
use view\Courses;
use model\Uploads;
use view\Authors;
use view\FilesPage;


class BHttp{
    private $db;
    
    public function __construct()
    {
        global $f3;
        $this->db=new \DB\SQL
        (
            $f3->get('DB_TESTIFY.db_type').':host='.$f3->get('DB_TESTIFY.db_host').';port='.$f3->get('DB_TESTIFY.db_port').';dbname='.$f3->get('DB_TESTIFY.db_name'),
            $f3->get('DB_TESTIFY.db_login'),
            $f3->get('DB_TESTIFY.db_password')
        );
    }
    /**
     * Просмотр записей / главная страница
     */
    public function notesPage(\Base $f3,$params) 
    {
        //Проверка на авторизацию
		S::loginTest($this->db);
        $f3->set('GET', CFuns::sanitizeString($f3->get('GET')) );
        $builder=new NotesViewerBuilder( 'Testify' );
        $pageNum=1;
         
        if( $f3->exists('GET.page') ){
			if(preg_match("/[^0-9]/",$f3->get('GET.page') )){
                $f3->error(404);
            }else{
                $pageNum = $f3->get('GET.page');
            }
            $builder->addTitle('Страница '.$pageNum.' - Testify');
		}

        //Получение массива слов поиска
        $userSearchInput=$f3->get('GET.user_search')?$f3->get('GET.user_search'):'';
        $searchWords=CFuns::getSearchList($userSearchInput);
        $processor=new NoteProcessor();

        //Получение id подписчика если требуется отобразить записи в подписках
        $subId=0;
        if($f3->exists('GET.cur_sub')){
            if($f3->get('user.isAuth')){
                $subId=$f3->get('user.id');
            }
        }
        //получить id курса если требуется отобразить записи конкретного курса
        $courseId = 0;
        if( $f3->exists('GET.c_id') )
        {
            $courseId = $f3->get('GET.c_id');
        }  

        //отобразить записи только выбраного автора
        $author_id=0;
        $sub=new Subscriber($f3->get('user.id'));
        if($f3->exists('GET.a_id')){
            
            if(preg_match("/[^0-9]/",$f3->get('GET.a_id') )){
                $f3->error(404);
            }else{
                $author_id = $f3->get('GET.a_id');
            }
            $a=$sub->getAuthorsList($this->db,$searchWords,0,2,$author_id);
            //получить список курсов автора
            NotesViewerPage::i()
                ->addAuthorBlock(Authors::i()->add($a))
                ->addAuthorCourseBlock(
                    Courses::i()->addAuthorCourses(
                        $f3,   
                        $sub->getCourseList(
                            db:$this->db,
                            authorId:$author_id
                        )
                    )
                );
           
        }
        //сисок курсов в блоке слева
        NotesViewerPage::i()->addSubbedCourseBlockHtml(Subscriber::getCoursesBySubId($this->db,$f3->get('user.id')),$courseId);
        // Отобразить все текущие подписки в левой части стр   
        $authorsList=$sub->getAuthorsList($this->db,[''],0,1);

        $builder
            ->addAuthorsBlock($authorsList,$author_id)
            ->addNotes( 
            $processor->getAll(
                db:$this->db, authorId:$author_id, subId:$subId, pageNum:$pageNum,searchWords:$searchWords, userId:$f3->get('user.id'),courseId:$courseId
            ),
            $f3->get('BASE'),
            $userSearchInput,
            $author_id,
            $processor->count,
            $subId,
            $pageNum,
            $courseId
            )
            ->addHeader()
            ->addFooter()
            ->addControlBtns()
            ->addScrollToTop()
            ->addBurgerMenu();
        //Обновить кол-во просмотренных записей
        $processor->updateViews($this->db);
        
        $tmp = $builder->build();
        echo $tmp->htmlRender(
            head:$tmp->head(),
            body:$tmp->body()
        );
    }
    /**
	 * Страница с формой обратной связи
	 */
	public function emailPage(\Base $f3,$params) 
	{
        S::loginTest($this->db);
		$f3->set('POST',CFuns::sanitizeString($_POST));
		$f3->set('error',3);
		if(
			$f3->exists('POST.sender_subject') &&
			$f3->exists('POST.sender_name') &&
			$f3->exists('POST.sender_email') &&
			$f3->exists('POST.sender_txt')
		){
			$f3->set('error',0);
			//поля не должны быть пустыми
			try {
				foreach ( $f3->get('POST') as $k=>$v) {
					if($v == '') throw new \Exception("Поле $k - пустое",2);
				}
				$tmp=$this->_sendMail($f3);
				if($tmp!='') throw new \Exception("Письмо не отправлено, причина: $tmp",1);
				
			} catch (\Exception $e) {
				$f3->set('error',$e->getCode());
				$f3->set('msg',$e->getMessage());
			}
		}

		EmailFormPage::i()->addEmailForm()
                ->addTitle('Отправить отзыв - Testify')
                ->addBurgerMenu()
				->addHeader('headerTitle.htm','Отправить отзыв')
				->addFooter();
        echo EmailFormPage::i()->htmlRender(
            EmailFormPage::i()->head(),
            EmailFormPage::i()->body()
        );
	}
    /**
     * Редактор записей
     */
    public function noteEditorPage(\Base $f3,$params)
    {
        //Проверка на авторизацию
		S::loginTest($this->db);

        if(!$f3->get('user.isAuthor') && !$f3->get('user.isAdmin'))
        {
            $f3->error(404);
        }
        if(preg_match("/[^0-9]/",$params['id']))
        {
            $f3->reroute('/profile/'.urlencode('Ошибка передачи данных, попробуйте снова позже'));
        }
        if( preg_match("/[^(new|change)]/",$params['ed_type'])){
            $f3->reroute('/profile/'.urlencode('Ошибка передачи данных, попробуйте снова позже'));
        }
        NoteEditorPage::i()->addErrorModalWrap('')//пока нет ошибок
            ->addHeader('headerTitle.htm','Редактор записей')
            ->addFooter()
            ->addBurgerMenu()
            ->addGoBackBtns();
        $msgHtml='';//сообщение пользователю

        $processor=new NoteProcessor();
        $pdata=array_merge(
            NoteProcessor::$data,
            $processor->getWithCourse($this->db,$params['id'])
        );
        $pdata['article']=preg_replace("/<br>/","\r\n",$pdata['article']);
        //проверка если это владелец записи, то изменить, иначе ошибка
        if($params['ed_type']=='change' && ($pdata['author_id']!=$f3->get('user.id') && !$f3->get('user.isAdmin')))
        {
            $f3->reroute('/profile/'.urlencode('Вы не можете изменить чужую запись'));
        }else if(//обновить БД если пришли данные
            $f3->exists('POST.note_title') &&
            $f3->exists('POST.note_txt')
        ){
            $f3->set('POST',CFuns::sanitizeString($f3->get('POST')));

            $article = preg_replace("/\r\n/","<br>",$f3->get('POST.note_txt'));
            $course_id = $f3->get('POST.course')!='0' ? $f3->get('POST.course') : NULL;
            $tags = $f3->exists('POST.note_tags') ? $f3->get('POST.note_tags'):'';
            $title=$f3->get('POST.note_title');
            $date=date('Y-m-d');
            switch ($params['ed_type']) {
                case 'new':
                    $processor->create(
                        db:$this->db,
                        course_id: $course_id,
                        created: $date,
                        title: $title,
                        article: $article,
                        tags: $tags,
                        author_id:$f3->get('user.id')
                    );
                    break;
                case 'change':
                    $processor->update(
                        db:$this->db,
                        noteId:$params['id'],
                        course_id: $course_id,
                        title: $title,
                        article: $article,
                        tags: $tags,
                        author_id:$pdata['author_id']
                    );
                    $pdata['title']=$title;
                    $pdata['article']=$f3->get('POST.note_txt');
                    $pdata['tags']=$tags;
                    $pdata['course_id']=$course_id;
                    break;
            }
            $msgHtml='Успешно!';
        }

        //получить разметку записи
        $subscriber=new Subscriber($f3->get('user.id'));
        
        NoteEditorPage::i()->addEditorForm(
            f3:$f3,
            params:$params,
            noteData:$pdata,
            coursesListHtml:Courses::i()->getCourseListHtml(
                $subscriber->getCourseList(
                    db:$this->db,
                    authorId:$f3->get('user.id')
                ),
                $pdata['course_id']
                )
            )
            ->addErrorModalWrap($msgHtml)
            ->addTitle('Редактор записей - Testify');

        //получить разметку файлов
        $upl=new Uploads(
            users_dir:$f3->get('user_data_path'),
            login:$f3->get('user.login')
        );
        FilesPage::i()->addImgBlock($upl->getUserFiles($upl->img_dir));
        FilesPage::i()->addFilesBlock($upl->getUserFiles($upl->file_dir));
        FilesPage::i()->addVideoBlock($upl->getUserFiles($upl->video_dir));
        
        NoteEditorPage::i()->addFilesList(FilesPage::i());

        echo NoteEditorPage::i()->htmlRender(
            NoteEditorPage::i()->head(),
            NoteEditorPage::i()->body()
        );
    }    
    /**
     * Редактор курса
     */
    public function courseEditorPage(\Base $f3,$params)
    {
        //Проверка на авторизацию
		S::loginTest($this->db);

        if(!$f3->get('user.isAuthor') && !$f3->get('user.isAdmin'))
        {
            $f3->error(404);
        }
        if(preg_match("/[^0-9]/",$params['id']))
        {
            $f3->reroute('/login/'.urlencode('Ошибка передачи данных, попробуйте снова позже'));
        }
        if( preg_match("/[^(new|change)]/",$params['ed_type'])){
            $f3->reroute('/login/'.urlencode('Ошибка передачи данных, попробуйте снова позже'));
        }
        NoteEditorPage::i()->addErrorModalWrap('')//пока нет ошибок
            ->addHeader('headerTitle.htm','Редактор курса')
            ->addFooter()
            ->addBurgerMenu()
            ->addTitle('Редактор курса - Testify')
            ->addGoBackBtns();
        $msgHtml=['class'=>'','msg'=>''];//сообщение пользователю

        //получить информации о курсе
        $pdata=array_merge(
            CourseProcessor::$data,
            CourseProcessor::get($this->db,$params['id'])
        );
        $pdata['description']=preg_replace("/<br>/","\r\n",$pdata['description']);

        //проверка если это владелец курса, то можно изменить, иначе ошибка
        if($params['ed_type']=='change' && $pdata['author_id']!=$f3->get('user.id') && !$f3->get('user.isAdmin') ){
            $f3->reroute('/profile/'.urlencode('Вы не можете изменить чужой курс'));
        }else if(//обновить БД если пришли данные
            $f3->exists('POST.title') &&
            $f3->exists('POST.description')
        ){
            $f3->set('POST',CFuns::sanitizeString($f3->get('POST')));

            //получить ava_url
            $upl=new Uploads(
                users_dir:$f3->get('user_data_path'),
                login:$f3->get('user.login')
            );
            if($f3->get('FILES.course_ava.name')!==''){
                //проверить новую аватарку на валидность (доступны только png)
                if(!Uploads::isFileValid( $f3->get('FILES.course_ava'), Uploads::$ext['AVA']))
                {
                    NoteEditorPage::i()->addErrorModalWrap('Небыл загружен файл, проверьте формат файла для: '.$f3->get('FILES.course_ava.name'));
                }else{
                    //удалить пердыдущую аватарку если она была (кроме default_ava)
                    if($pdata['ava_url']!=CourseProcessor::$data['ava_url']){
                        unlink($upl->static_img_dir.$pdata['ava_url']);
                    } 

                    //Обновить базу данных и загрузить аватарку
                    $ava_name='c_id_'.md5($f3->get('FILES.course_ava.name')).Uploads::$ext['AVA'][$f3->get('FILES.course_ava.type')];

                    //загрузить новую
                    $f3->set('FILES.course_ava.name',$ava_name);

                    $upl->uploadFile(
                        file_data:$f3->get('FILES.course_ava'),
                        dir:$upl->static_img_dir.$upl->course_ava_dir
                    );
                    $pdata['ava_url']=$upl->course_ava_dir.$ava_name;
                }
            }

            $description = preg_replace("/\r\n/","<br>",$f3->get('POST.description'));
            $courseId = $params['id'];
           
            $is_private = $f3->get('POST.is_private')=='on' ? 1:0;
            $title = $f3->get('POST.title');
            $authorId = $pdata['author_id']!=='' ? $pdata['author_id'] : $f3->get('user.id');
            switch ($params['ed_type']) {
                case 'new':
                    CourseProcessor::create(
                        db:$this->db,
                        authorId: $authorId,
                        title: $title,
                        tdesc: $description,
                        ava_url: $pdata['ava_url'],
                        is_private: $is_private
                    );
                    
                    break;
                case 'change':
                    CourseProcessor::update(
                        db:$this->db,
                        courseId:$courseId,
                        authorId: $authorId,
                        title: $title,
                        tdesc: $description,
                        ava_url: $pdata['ava_url'],
                        is_private: $is_private
                    );
                    break;
            }
            //обновить данные для вывода в форму
            $pdata['title']=$title;
            $pdata['description']=preg_replace("/<br>/","\r\n",$description);
            $pdata['is_private']=$is_private;
            $params['ed_type']='change';
            NoteEditorPage::i()->addErrorModalWrap('Успешно!');
            $msgHtml=['class'=>'good_txt','msg'=>'Успешно!'];
        }

        NoteEditorPage::i()->addEditorCourseForm(
            f3:$f3,
            params:$params,
            course:$pdata,
            msgHtml:$msgHtml
        );

        echo NoteEditorPage::i()->htmlRender(
            NoteEditorPage::i()->head(),
            NoteEditorPage::i()->body()
        );
    }
    /**
     * Редактор файлов
     */
    public function filesEditorPage(\Base $f3,$params)
    {
        $loginError=S::loginTest($this->db);
        if(!$f3->get('user.isAuth')){
            $f3->reroute('/login/'.urlencode($loginError)); 
        }
        $msg=isset($params['msg'])?urldecode($params['msg']):'';
        if(!$f3->get('user.isAuthor') && !$f3->get('user.isAdmin'))
        {
            $f3->error(404);
        }
        //получить разметку файлов
        $upl=new Uploads(
            users_dir:$f3->get('user_data_path'),
            login:$f3->get('user.login')
        );

        if($f3->exists('FILES.user_files') && $f3->get('POST.upld_type'))
        {
            $f3->set('POST',CFuns::sanitizeString($f3->get('POST')));
            if(preg_match("/[^(img|file|video)]/",$f3->get('POST.upld_type')))
            {
                $f3->reroute('/editor/files/'.urlencode('Произошла ошибка: неправельный тип загрузки файла, свяжитесь с администратором или попробуйте снова'));
            }
            //поменять ключи и значения местами для удобной обработки
            $f3->set('FILES.user_files',CFuns::reArrayFiles($f3->get('FILES.user_files')));
            //загрузить полученые файлы
            $out='';
            $dir=$upl->file_dir;
            foreach ($f3->get('FILES.user_files') as $k => $v) {
                try {
                    switch ($f3->get('POST.upld_type')) {
                        case 'img':
                            $ex = Uploads::$ext['IMG'];
                            $dir=$upl->img_dir;
                            break;
                        case 'file':
                            $ex=Uploads::$ext['FILE'];
                            $dir=$upl->file_dir;
                            break;
                        case 'video':
                            $ex=Uploads::$ext['VIDEO'];
                            $dir=$upl->video_dir;
                            break;
                    }
                    if(!Uploads::isFileValid( $v, $ex))
                    {
                        throw new \Exception($v['name'], 403);
                    }
                    $upl->uploadFile(
                        file_data:$v,
                        dir:$dir
                    );
                } catch (\Exception $e) {
                    $out.=$e->getMessage()."\n";
                }
            }
            if($out!=''){
                $f3->reroute('/editor/files/'.urlencode('Небыл загружен файл, проверьте формат файла для: '.$out));
            }
        }

        FilesPage::i()->addImgBlock($upl->getUserFiles($upl->img_dir),true,$upl->fileCount>10)
            ->addFilesBlock($upl->getUserFiles($upl->file_dir),true,
            $upl->fileCount>10)
            ->addVideoBlock($upl->getUserFiles($upl->video_dir),true,
            $upl->fileCount>10)
            ->addHeader('headerTitle.htm','Список файлов')
            ->addTitle('Список файлов - Testify')
            ->addFooter()
            ->addBurgerMenu()
            ->addErrorModalWrap($msg)
            ->addGoBackBtns()
            ->addModalFileForm();

        echo FilesPage::i()->htmlRender(
            FilesPage::i()->head(),
            FilesPage::i()->body()
        );
    }   
    /**
     * Отправка почты с помощью PHPMailer
     *
     * @param  object $f3
     * @return string
     */
    protected function _sendMail(\Base &$f3)
    {
        $mail = new PHPMailer;
        $mail->CharSet = 'utf-8';

        $mail->isSMTP();                                  // Установть использование SMTP для mailer 
        $mail->Host = $f3->get('PHPMAILER.host');         // Адрес почтового сервера
        $mail->SMTPAuth = true;                           // Включить SMTP аутентификацию
        $mail->Username = $f3->get('PHPMAILER.username'); // Логин учетной записи которая авторизована для отправки писем с почтового сервера
        $mail->Password = $f3->get('PHPMAILER.password'); // и ее пароль 
        $mail->SMTPSecure = 'ssl';                        // Установка ssl
        $mail->Port = $f3->get('PHPMAILER.port');         // TCP хоста почтового сервера
        
        $mail->setFrom($f3->get('PHPMAILER.send_from'));  // От кого будет уходить письмо?
        $mail->addAddress('timohin.misha@gmail.com');     // Кому будет уходить письмо 
        $mail->isHTML(true);                              // Включить HTML в состав тела письма
        
        $mail->Subject = 'Письмо с сайта Testify';
        $mail->Body    = '<br>Почта этого пользователя: '.$f3->get('POST.sender_email').'
        <br>Имя этого пользователя: '.$f3->get('POST.sender_name').'
        <br>Тема письма: '.$f3->get('POST.sender_subject').'
        <br>Текст письма:<br> '.$f3->get('POST.sender_txt').'
        ';
        $mail->AltBody = '';
		$mail->send();                                     // Отправка
        return $mail->ErrorInfo ;
    }
    
}
?>