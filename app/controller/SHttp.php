<?php namespace controller;

use \view\Notes;
use \model\NoteProcessor;
use \model\CFuns;
use \model\Security as S;
use \model\Statistics;
use \view\LoginFormPage;
use \model\Test;
use \model\Subscriber;
use view\AccountEditorPage;
use \view\Authors;
use \view\Courses;
use \view\ProfilePage;
use \view\ProfileEditorPage;

class SHttp{
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
     * Страница ввода логина и пароля
     */
    public function loginPage(\Base $f3,$params=NULL)
	{
		$loginError=S::loginTest($this->db);
		if(!$f3->get("user.isAuth"))//пользователь не авторизован
		{
			//Отображение формы входа
			if(isset($params['msg'])){
				LoginFormPage::i()->addLoginForm(urldecode($params['msg']));
			}else{
				LoginFormPage::i()->addLoginForm($loginError);
			}
			
			LoginFormPage::i()->addTitle('Авторизация - Testify')
				->addHeader('headerTitle.htm','Авторизация')
				->addFooter();
			
			echo LoginFormPage::i()->htmlRender(
				body:LoginFormPage::i()->body(),
				head:LoginFormPage::i()->head()
			);
			
		}else{
			$f3->reroute('/');
		}
	}
	/**
	 * Страница регистрации	нового пользователя
	 */
	public function registPage($f3,$params=NULL)
	{
		LoginFormPage::i()->addRegistForm($f3)
				->addTitle('Регистрация - Testify')
				->addHeader('headerTitle.htm','Регистрация')
				->addFooter();

		echo LoginFormPage::i()->htmlRender(
			body:LoginFormPage::i()->body(),
			head:LoginFormPage::i()->head()
		);
	}
	/**
	 * Страница Профиля, если пользователь прошел аутентификацию
	 */
	public function profilePage(\Base $f3,$params=NULL) 
	{
		$loginerr=S::loginTest($this->db);
		//если пользователь не авторизован вернуть на страницу с авторизацией
		if($f3->get('user.isAuth')==false)
		{
			$f3->reroute('/login');	
		}else{
			$f3->reroute('/profile/notes/'.(isset($params['msg'])?$params['msg']:'') );
		}
	}
	/**
	 * Профиль, раздел с записями
	 */
	public function profileNotesPage(\Base $f3,$params=NULL) 
	{
		$loginerr=S::loginTest($this->db);
		//если пользователь не авторизован вернуть на страницу с авторизацией
		if($f3->get('user.isAuth')==false)
		{
			$f3->reroute('/login');	
		}
		
		$f3->set('GET', CFuns::sanitizeString($f3->get('GET')) ); // очистка GET
		$userSearchInput=$f3->get('GET.user_search')?$f3->get('GET.user_search'):'';// получить поисковый запрос
		$searchWords=CFuns::getSearchList($userSearchInput);

		$msg=isset($params['msg'])?urldecode($params['msg']):'';//сообщение от другой страницы
		
		//Навигация по страницам записей
		$pageNum=1;
        
        if( $f3->exists('GET.page') ){
			if(preg_match("/[^0-9]/",$f3->get('GET.page') )){
                $f3->error(404);
            }else{
                $pageNum = $f3->get('GET.page');
            }
			ProfilePage::i()->addTitle('Страница '.$pageNum.' - Ваши записи - Testify');
		}else{
			ProfilePage::i()->addTitle('Профиль - Ваши записи - Testify');
		}

		//Получение списка записей
		if(!$f3->get('user.isAuthor')){
			ProfilePage::i()->addNoNotesHelper($f3);
		}else{			
			$noteProccessor = new NoteProcessor();
			$noteProccessor->getAll(
				db:$this->db,
				authorId:$f3->get('user.id'),
				userId:$f3->get('user.id'),
				subId:0,
				courseId:0,
				pageNum:$pageNum,
				searchWords:$searchWords
			);
			if( count(NoteProcessor::getAuthorId($this->db,$f3->get('user.id')))==0 ){ ProfilePage::i()->addNoNotesHelper($f3)->addNotesPanel($f3); }
			else{
				//пользователь уже создавал записи -> отобразить на странице
				Notes::i()->addNotes(
					notes:$noteProccessor->notesList
				);
				Notes::i()->addDropSearch(
					goToUrl:$f3->get('BASE').'/profile/notes',
					userSearchInput:$userSearchInput,
					count:$noteProccessor->count
				);
				Notes::i()->addPageNavigation(
					authorId:$f3->get('user.id'),
					subId:0,
					courseId:0,
					pageNum:$pageNum,
					count:$noteProccessor->count,
					goToUrl:$f3->get('BASE').'/profile/notes'
				);
				
				ProfilePage::i()->addCntrlPanel(
					[
						0=>['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/editor/note/new/0','txt'=>'Новая запись'],
						1=>['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/editor/files','txt'=>'Фото / Файлы']
					]
				)
					->addNotes(Notes::i())
					->addSearch('/profile/notes');
			}
		}

		//Информация о профиле
		ProfilePage::i()->addProfileInfo($f3);

		//Навигация в верхней части профиля
		ProfilePage::i()->addNavigation('notes');

		//Прочие элементы профиля
		if(!$f3->get('user.isAuthor')){ ProfilePage::i()->addBecomeAuthorHelper(); }
		else{ProfilePage::i()->addCreateCourseHelper();}
	
		ProfilePage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Профиль')
			->addFindAuthorHelper()
			->addScrollToTop()
			->addErrorModalWrap($msg)
			->addFooter();
		
		echo ProfilePage::i()->htmlRender(
			body:ProfilePage::i()->body(),
			head:ProfilePage::i()->head()
		);
	}
	/**
	 * Профиль, раздел с подписками
	 */
	public function profileSubscribersPage(\Base $f3,$params=NULL) 
	{
		$loginerr=S::loginTest($this->db);
		//если пользователь не авторизован вернуть на страницу с авторизацией
		if($f3->get('user.isAuth')==false)
		{
			$f3->reroute('/login');	
		}
		//поиск
		$f3->set('GET', CFuns::sanitizeString($f3->get('GET')) ); // очистка GET
		$userSearchInput=$f3->get('GET.user_search')?$f3->get('GET.user_search'):'';// получить поисковый запрос
		$searchWords=CFuns::getSearchList($userSearchInput);
		
		//Навигация по страницам авторов
		$pageNum=1;
        
        if( $f3->exists('GET.page')){
			if(preg_match("/[^0-9]/",$f3->get('GET.page') )){
                $f3->error(404);
            }else{
                $pageNum = $f3->get('GET.page');
            }
			ProfilePage::i()->addTitle('Страница '.$pageNum.' - Подписки - Testify');
		}else{
			ProfilePage::i()->addTitle('Профиль - Подписки - Testify');
		}

		if(preg_match("/[^(all|current)]/",$params['all']))
		{
			$f3->error(404);
		}

		$sub=new Subscriber($f3->get('user.id'));

		switch ($params['all']) {
			case 'all':
				$authorsList=$sub->getAuthorsList(
					db:$this->db,
					searchWords:$searchWords,
					pageNum:0,
					mode:0
				);
				break;
			case 'current':
				$authorsList=$sub->getAuthorsList(
					db:$this->db,
					searchWords:$searchWords,
					pageNum:0,
					mode:1
				);
				break;
			default:
				$authorsList=[];
				break;
		}

		//получить разметку авторов
		Authors::i()->add($authorsList)
			->addDropSearch($f3->get('BASE').'/profile/subscribes/'.$params['all'],$userSearchInput,$sub->count)
			->addPageNavigation(
				authorId:$f3->get('user.id'),
				subId:0,
				pageNum:$pageNum,
				count:$sub->count,
				goToUrl:$f3->get('BASE').'/profile/subscribes/'.$params['all']
			);
		if(empty($authorsList)){
			ProfilePage::i()->addNoAuthorHelper($f3);
		}else{
			ProfilePage::i()
				->addAuthors(
					Authors::i(),
				)
				->addSearch('profile/subscribes/'.$params['all']);
		}

		$vd=[
			'current'=>[
				'class'=>'page_nums_rev',
				'url'=>$f3->get('BASE').'/profile/subscribes/current',
				'txt'=>'Ваши подписки'],
			'all'=>[
				'class'=>'page_nums_rev',
				'url'=>$f3->get('BASE').'/profile/subscribes/all',
				'txt'=>'Все авторы'
			],
			['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/profile/courses/my','txt'=>'Курсы']];
		$vd[$params['all']]['class']='page_nums';
		ProfilePage::i()->addCntrlPanel($vd);
		//Информация о профиле
		ProfilePage::i()->addProfileInfo($f3);

		//Навигация в верхней части профиля
		ProfilePage::i()->addNavigation('subscribes');

		//Прочие элементы профиля
		if(!$f3->get('user.isAuthor')){ ProfilePage::i()->addBecomeAuthorHelper(); }
		else{ProfilePage::i()->addCreateCourseHelper();}

		ProfilePage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Профиль')
			->addScrollToTop()
			->addFindAuthorHelper()
			->addFooter();
			

		echo ProfilePage::i()->htmlRender(
			body:ProfilePage::i()->body(),
			head:ProfilePage::i()->head()
		);
	}	
	/**
	 * Страница с изменением профиля
	 */
	public function profileEditPage(\Base $f3,$params=NULL) {

		$logErr=S::loginTest($this->db);
		if($logErr!=''){ $f3->reroute('/login/'.urlencode($logErr)); }

		$f3->set("POST",\model\CFuns::sanitizeString($f3->get("POST")));

		$msg='';

		//Проверка что пользователь авторизован
			if(
				$f3->exists("POST.cur_pass") &&
				hash('sha256', $f3->get("POST.cur_pass") )===$f3->get("user.password") 
			)
			{
				if(
					$f3->exists("POST.name")&&
					$f3->exists("POST.status") &&
					$f3->exists("POST.new_pass")
				)
				{
					$name = $f3->get("POST.name");
					$status=$f3->get("POST.status");
					
					$new_pass=strlen($f3->get("POST.new_pass"))==0 ? $f3->get("user.password") : hash('sha256', $f3->get("POST.new_pass") );

					S::updateAccount($this->db, $f3->get('user.login'),$new_pass, $f3->get('user.id'));
					S::updateProfile($this->db,$name,$status,$f3->get('user.id'));
					$f3->set('user.status',$status);
					$f3->set('user.name',$name);
					$msg='Успешно! ';          
				// время жизни COOKIE-данных не продлевается
				
					S::updateCookie('security_login',$f3->get('user.login'),"/",$_SERVER['HTTP_HOST']);
					S::updateCookie('security_password',$new_pass,"/",$_SERVER['HTTP_HOST']);
				}else{
					$msg='Ошибка передачи данных. Попробуйте снова!';
				}
			}else if($_SERVER['REQUEST_METHOD']=='POST'){
				$msg='Проверьте правильность веденого пароля';
			}else{
				$msg='';
			}
		//прочие элементы
		ProfileEditorPage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Редактирование профиля')
			->addTitle('Редактирование профиля - Testify')
			->addEditProfileForm()
			->addErrorModalWrap($msg)
			->addGoBackBtns()
			->addFooter();
			
		echo ProfileEditorPage::i()->htmlRender(
			body:ProfileEditorPage::i()->body(),
			head:ProfileEditorPage::i()->head()
		);
	}	
	/**
	 * Профиль, раздел с курсами
	 */
	public function profileCoursesPage(\Base $f3,$params=NULL) 
	{
		$logErr=S::loginTest($this->db);
		if($logErr!=''){ $f3->reroute('/login/'.urlencode($logErr)); }
		if(!$f3->get('user.isAuthor')){
			$f3->reroute('/profile/'.urlencode('Недостаточно прав для отображения страницы'));
		}
		$sub=new Subscriber($f3->get('user.id'));
		//поиск
		$f3->set('GET', CFuns::sanitizeString($f3->get('GET')) ); // очистка GET
		$userSearchInput = $f3->exists('GET.user_search') ? $f3->get('GET.user_search'):'';
		$searchWords=CFuns::getSearchList($userSearchInput);

		//Навигация по страницам курсов
		$pageNum=1;
        
        if( $f3->exists('GET.page') ){
			if(preg_match("/[^0-9]/",$f3->get('GET.page') )){
                $f3->error(404);
            }else{
                $pageNum = $f3->get('GET.page');
            }
			ProfilePage::i()->addTitle('Страница '.$pageNum.' - Курсы - Testify');
		}else{
			ProfilePage::i()->addTitle('Профиль - Курсы - Testify');
		}
		$courseList=$sub->getCourseList(
			db:$this->db,
			searchWords:$searchWords,
			authorId:$f3->get('user.id'),
			page:$pageNum,
			isGetRqstCount:true
		);
		if($sub->count==0){
			ProfilePage::i()->addNoCoursesHelper($f3);
		}else{
			//получить разметку для курсов
			Courses::i()
				->add(
					f3:$f3,
					courseList:$courseList
				)
				->addDropSearch($f3->get('BASE').'/profile/courses/',$userSearchInput,$sub->count)
				->addPageNavigation(
					pageNum:$pageNum,
					count:$sub->count,
					goToUrl:$f3->get('BASE').'/profile/courses/'
				);
			
			ProfilePage::i()
				->addCourses(Courses::i(),'/profile/courses/')
				->addSearch('/profile/courses/');
		}

		//Навигация в верхней части профиля
		ProfilePage::i()->addNavigation('courses');

		//Информация о профиле
		ProfilePage::i()->addProfileInfo($f3);
		//Прочие элементы профиля
		if(!$f3->get('user.isAuthor')){ ProfilePage::i()->addBecomeAuthorHelper(); }
		else{ProfilePage::i()->addCreateCourseHelper();}

		ProfilePage::i()->addCntrlPanel(
			[
				['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/editor/course/new/0','txt'=>'Новый Курс'],
				['class'=>'page_nums','url'=>$f3->get('BASE').'/profile/courses','txt'=>'Ваши курсы']
			]
		);
		ProfilePage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Профиль')
			->addErrorModalWrap()
			->addFindAuthorHelper()
			->addFooter();

		echo ProfilePage::i()->htmlRender(
			body:ProfilePage::i()->body(),
			head:ProfilePage::i()->head()
		);
	}	
	/**
	 * Профиль, раздел с курсами на которые подписан пользователь
	 */
	public function profileMyCoursesPage(\Base $f3,$params=NULL) 
	{
		$logErr=S::loginTest($this->db);
		if($logErr!=''){ $f3->reroute('/login/'.urlencode($logErr)); }
		$sub=new Subscriber($f3->get('user.id'));
		//поиск
		$f3->set('GET', CFuns::sanitizeString($f3->get('GET')) ); // очистка GET
		$userSearchInput = $f3->exists('GET.user_search') ? $f3->get('GET.user_search'):'';
		$searchWords=CFuns::getSearchList($userSearchInput);

		//Навигация по страницам курсов
		$pageNum=1;
        
        if( $f3->exists('GET.page') ){
			if(preg_match("/[^0-9]/",$f3->get('GET.page') )){
                $f3->error(404);
            }else{
                $pageNum = $f3->get('GET.page');
            }
			ProfilePage::i()->addTitle('Страница '.$pageNum.' - Курсы - Testify');
		}else{
			ProfilePage::i()->addTitle('Профиль - Курсы - Testify');
		}
		$courseList=$sub->getMyCourseList(
			db:$this->db,
			searchWords:$searchWords,
			subscriberId:$f3->get('user.id'),
			page:$pageNum
		);
		if($sub->count==0){
			ProfilePage::i()->addNoMyCoursesHelper($f3);
		}else{
			//получить разметку для курсов
			Courses::i()
				->addAuthorCourses(
					f3:$f3,
					courseList:$courseList
				)
				->addDropSearch($f3->get('BASE').'/profile/courses/',$userSearchInput,$sub->count)
				->addPageNavigation(
					pageNum:$pageNum,
					count:$sub->count,
					goToUrl:$f3->get('BASE').'/profile/courses/'
				);
			
			ProfilePage::i()
				->addCourses(Courses::i())
				->addSearch('/profile/courses/my/');;
		}

		//Навигация в верхней части профиля
		ProfilePage::i()->addNavigation('subscribes');

		//Информация о профиле
		ProfilePage::i()->addProfileInfo($f3);
		//Прочие элементы профиля
		if(!$f3->get('user.isAuthor')){ ProfilePage::i()->addBecomeAuthorHelper(); }
		else{ProfilePage::i()->addCreateCourseHelper();}

		ProfilePage::i()->addCntrlPanel(
			[
			[
				'class'=>'page_nums_rev',
				'url'=>$f3->get('BASE').'/profile/subscribes/current',
				'txt'=>'Ваши подписки'],
			[
				'class'=>'page_nums_rev',
				'url'=>$f3->get('BASE').'/profile/subscribes/all',
				'txt'=>'Все авторы'
			],
				['class'=>'page_nums','url'=>$f3->get('BASE').'/profile/courses/my','txt'=>'Курсы']
			]
		);
		ProfilePage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Профиль')
			->addErrorModalWrap()
			->addFindAuthorHelper()
			->addFooter();

		echo ProfilePage::i()->htmlRender(
			body:ProfilePage::i()->body(),
			head:ProfilePage::i()->head()
		);
	}	
	/**
	 * Профиль, раздел с заявками на конкретный курс
	 */
	public function profileRequestsPage($f3,$params=null) 
	{
		$logErr=S::loginTest($this->db);
		if($logErr!=''){ $f3->reroute('/login/'.urlencode($logErr)); }
		$sub=new Subscriber($f3->get('user.id'));
		//получить $courseId 
		if( preg_match("/[^0-9]/",$params['id']) ){
			$f3->error(404);
		}
		$courseId=(int)$params['id'];
		//поиск
		$f3->set('GET', CFuns::sanitizeString($f3->get('GET')) ); // очистка GET
		$userSearchInput = $f3->exists('GET.user_search') ? $f3->get('GET.user_search'):'';
		$searchWords=CFuns::getSearchList($userSearchInput);

		//Навигация по страницам курсов
		$pageNum=1;
        
        if( $f3->exists('GET.page') ){
			if(preg_match("/[^0-9]/",$f3->get('GET.page') )){
                $f3->error(404);
            }else{
                $pageNum = $f3->get('GET.page');
            }
			ProfilePage::i()->addTitle('Страница '.$pageNum.' - Заявки - Testify');
		}else{
			ProfilePage::i()->addTitle('Профиль - Заявки - Testify');
		}
		
		//получить информацию о курсе
		$courseList=$sub->getCourseList(
			db:$this->db,
			searchWords:[''],
			authorId:$f3->get('user.id'),
			page:1,
			isGetRqstCount:true,
			courseId:$courseId
		);
		if($sub->count==0){
			$f3->error(404);
		}
		$count=Subscriber::getRequestCount($this->db,Test::GetWhere($searchWords,['ss.`name`','cs.`created`']),$courseId);
		//Вывод курса
		Courses::i()
			->add(
				f3:$f3,
				courseList:$courseList
			)
			->addDropSearch($f3->get('BASE').'/profile/courses/requests/'.$courseId,$userSearchInput,)
			->addPageNavigation(
				pageNum:$pageNum,
				count:$count,
				goToUrl:$f3->get('BASE').'/profile/courses/requests/'.$courseId
			);
			
		//вывод заявок
		ProfilePage::i()
			->addRequests(
				rqsts:Subscriber::getRequestList($this->db,$searchWords,$pageNum,$courseId),
				courses:Courses::i(),
				count:$count
			)
			->addSearch('/profile/courses/requests/'.$courseId);

		//Навигация в верхней части профиля
		ProfilePage::i()->addNavigation('courses');

		ProfilePage::i()->addCntrlPanel(
			[
				['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/editor/course/new/0','txt'=>'Новый Курс'],
				['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/profile/courses','txt'=>'Ваши курсы'],
				['class'=>'page_nums','url'=>$f3->get('BASE').'/profile/courses/requests','txt'=>'Заявки']
			]
		);
		//прочие элементы профиля
		ProfilePage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Профиль')
			->addErrorModalWrap()
			->addFindAuthorHelper()
			->addProfileInfo()
			->addFooter();

		echo ProfilePage::i()->htmlRender(
			body:ProfilePage::i()->body(),
			head:ProfilePage::i()->head()
		);
	}	
	/**
	 * Профиль, раздел с участниками курса
	 */
	public function profileCourseSubscribersPage($f3,$params=null) 
	{
		$logErr=S::loginTest($this->db);
		if($logErr!=''){ $f3->reroute('/login/'.urlencode($logErr)); }
		$sub=new Subscriber($f3->get('user.id'));
		//получить $courseId 
		if( preg_match("/[^0-9]/",$params['id']) ){
			$f3->error(404);
		}
		$courseId=(int)$params['id'];
		//поиск
		$f3->set('GET', CFuns::sanitizeString($f3->get('GET')) ); // очистка GET
		$userSearchInput = $f3->exists('GET.user_search') ? $f3->get('GET.user_search'):'';
		$searchWords=CFuns::getSearchList($userSearchInput);

		//Навигация по страницам курсов
		$pageNum=1;
        
        if( $f3->exists('GET.page') ){
			if(preg_match("/[^0-9]/",$f3->get('GET.page') )){
                $f3->error(404);
            }else{
                $pageNum = $f3->get('GET.page');
            }
			ProfilePage::i()->addTitle('Страница '.$pageNum.' - Участники - Testify');
		}else{
			ProfilePage::i()->addTitle('Профиль - Участники - Testify');
		}
		
		//получить информацию о курсе
		$courseList=$sub->getCourseList(
			db:$this->db,
			searchWords:[''],
			authorId:$f3->get('user.id'),
			page:1,
			isGetRqstCount:true,
			courseId:$courseId
		);
		if($sub->count==0){
			$f3->error(404);
		}
		$count=Subscriber::getCourseSubscribesCount($this->db,Test::GetWhere($searchWords,['ss.`name`']),$courseId);
		//Вывод курса
		Courses::i()
			->add(
				f3:$f3,
				courseList:$courseList
			)
			->addDropSearch($f3->get('BASE').'/profile/courses/subscribes/'.$courseId,$userSearchInput,)
			->addPageNavigation(
				pageNum:$pageNum,
				count:$count,
				goToUrl:$f3->get('BASE').'/profile/courses/subscribes/'.$courseId
			);

		//вывод участников курса
		ProfilePage::i()
			->addCourseSubscribes(
				rqsts:Subscriber::getCourseSubscribesList($this->db,$searchWords,$pageNum,$courseId),
				courses:Courses::i(),
				count:$count,
			)
			->addSearch('/profile/courses/subscribes/'.$courseId);

		//Навигация в верхней части профиля
		ProfilePage::i()->addNavigation('courses');

		ProfilePage::i()->addCntrlPanel(
			[
				['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/editor/course/new/0','txt'=>'Новый Курс'],
				['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/profile/courses','txt'=>'Ваши курсы'],
				['class'=>'page_nums','url'=>$f3->get('BASE').'/profile/courses/requests','txt'=>'Участники']
			]
		);
		//прочие элементы профиля
		ProfilePage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Профиль')
			->addErrorModalWrap()
			->addFindAuthorHelper()
			->addProfileInfo()
			->addFooter();

		echo ProfilePage::i()->htmlRender(
			body:ProfilePage::i()->body(),
			head:ProfilePage::i()->head()
		);
	}
	/**
	 * Профиль, раздел статистики 
	 */
	public function profileStatisticsPage(\Base $f3,$params=NULL) 
	{
		$loginerr=S::loginTest($this->db);
		//если пользователь не авторизован вывести ошибку
		if(!$f3->get('user.isAuthor') && !$f3->get('user.isAdmin')){
			$f3->error(404);
		}
		//отобразить профиль
		if(!$f3->get('user.isAuthor')){ ProfilePage::i()->addBecomeAuthorHelper(); }else{ProfilePage::i()->addCreateCourseHelper();}

		
		if( $f3->get('user.isAdmin') )
		{
			//получение статистики для Админа
			ProfilePage::i()
				->addStatistic(Statistics::getAdminStat($this->db,[$f3->get('user.id')]))
				->addCntrlPanel([
					['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/editor/accounts/new/0','txt'=>'Редактор пользователей']]
				);
		}else{
			//получение статистики для Автора
			ProfilePage::i()->addStatistic(Statistics::getStat($this->db,[$f3->get('user.id')]));
		}
		//прочие элементы профиля
		ProfilePage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Профиль')
			->addErrorModalWrap()
			->addFindAuthorHelper()
			->addProfileInfo()
			->addNavigation('statistics')
			->addFooter();

		echo ProfilePage::i()->htmlRender(
			body:ProfilePage::i()->body(),
			head:ProfilePage::i()->head()
		);
	}	
	/**
	 * Профиль, раздел тестов, попытки
	 */
	public function profileTestsPage(\Base $f3,$params=NULL) 
	{
		$logErr=S::loginTest($this->db);
		if($logErr!='' || !$f3->get('user.isAuth')){ $f3->reroute('/login/'.urlencode($logErr)); }
		
		//поиск
		$f3->set('GET', CFuns::sanitizeString($f3->get('GET')) ); // очистка GET
		$userSearchInput = $f3->exists('GET.user_search') ? $f3->get('GET.user_search'):'';
		$searchWords=CFuns::getSearchList($userSearchInput);
		ProfilePage::i()->addSearch('/profile/tests');

		$cntrlPanel=[];//хранит разные кнопки панели управелния в зависимости от доступа
		if($f3->get('user.isAdmin')){
			$cntrlPanel[]=['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/profile/tests/all','txt'=>'Все тесты'];
		}
		//если пользователь автор,  то добавить контролы для создания теста
		if($f3->get('user.isAuthor')){
			$cntrlPanel[]=['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/profile/tests/my','txt'=>'Ваши тесты'];
		}

		//отобразить попытки пользователя
		$cntrlPanel[] = ['class'=>'page_nums','url'=>$f3->get('BASE').'/profile/tests','txt'=>'Ваши попытки'];

		$t=new Test($this->db);

		$tries=$t->GetUserResults(
			$f3->get("user.id"),
			Test::GetWhere($searchWords,['t.title','r.status','r.created'])
		);

		ProfilePage::i()->addTestResults(
			$f3,
			$tries,
			$userSearchInput!=''?$f3->get('BASE').'/profile/tests':''
		);
		
		//Навигация в верхней части профиля
		ProfilePage::i()->addNavigation('tests');
		ProfilePage::i()->addCntrlPanel(
			$cntrlPanel
		);
		//прочие элементы профиля
		if(!$f3->get('user.isAuthor')){ ProfilePage::i()->addBecomeAuthorHelper(); }
		ProfilePage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Профиль')
			->addErrorModalWrap()
			->addFindAuthorHelper()
			->addProfileInfo()
			->addTitle('Ваши попытки - Профиль - Testify')
			->addGoToTestModal($f3)
			->addFooter();

		echo ProfilePage::i()->htmlRender(
			body:ProfilePage::i()->body(),
			head:ProfilePage::i()->head()
		);
	}
	/**
	 * Профиль, раздел тестов, созданных пользователем	
	 */
	public function profileMyTestsPage($f3,$params=null)
	{
		$logErr=S::loginTest($this->db);
		if($logErr!=''){ $f3->reroute('/login/'.urlencode($logErr)); }
		if(!$f3->get('user.isAuthor') && !$f3->get('user.isAdmin')){
			$f3->error(404);
		}

		if(preg_match("/[^(all|my)]/",$params['all']))
		{
			$f3->error(404);
		}

		//поиск
		$f3->set('GET', CFuns::sanitizeString($f3->get('GET')) ); // очистка GET
		$userSearchInput = $f3->exists('GET.user_search') ? $f3->get('GET.user_search'):'';
		$searchWords=CFuns::getSearchList($userSearchInput);
		ProfilePage::i()->addSearch('/profile/tests/'.$params['all']);

		$cntrlPanel=[];//хранит разные кнопки панели управелния в зависимости от доступа
		if($f3->get('user.isAdmin')){
			$cntrlPanel[]=['class'=>$params['all']=='all'?'page_nums':'page_nums_rev','url'=>$f3->get('BASE').'/profile/tests/all','txt'=>'Все тесты'];
		}
		//если пользователь автор, то добавить контролы для создания теста
		if($f3->get('user.isAuthor')){
			$cntrlPanel[]=['class'=>$params['all']=='all'?'page_nums_rev':'page_nums','url'=>$f3->get('BASE').'/profile/tests/my','txt'=>'Ваши тесты'];
		}

		//отобразить попытки пользователя
		$cntrlPanel[] = ['class'=>'page_nums_rev','url'=>$f3->get('BASE').'/profile/tests','txt'=>'Ваши попытки'];
		
		$t=new Test($this->db);

		//преключить отображение всех тестов или только тесты пользователя 
		if($params['all']=='all'){
			$tests = $t->GetAllUserTests(
				Test::GetWhere($searchWords,['title','datetime_start','datetime_end','name'])
			);
		}else{
			$tests = $t->GetUserTests(
				$f3->get("user.id"),
				Test::GetWhere($searchWords,['title','datetime_start','datetime_end'])
			);
		}
		
		ProfilePage::i()
			->addMyTests(
				$f3,
				$tests,
				$userSearchInput!=''?$f3->get('BASE').'/profile/tests/'.$params['all']:''
			)
			->addVariantsLink();

		//Навигация в верхней части профиля
		ProfilePage::i()
			->addNavigation('tests')
			->addCntrlPanel($cntrlPanel);
		//прочие элементы профиля
		if(!$f3->get('user.isAuthor')){ ProfilePage::i()->addBecomeAuthorHelper(); }
		ProfilePage::i()->addBurgerMenu()
			->addHeader('headerTitle.htm','Профиль')
			->addErrorModalWrap()
			->addFindAuthorHelper()
			->addProfileInfo()
			->addTitle('Ваши тесты - Профиль - Testify')
			->addGoToTestModal($f3)
			->addBackupLoad()
			->addFooter();

		echo ProfilePage::i()->htmlRender(
			body:ProfilePage::i()->body(),
			head:ProfilePage::i()->head()
		);
	}	
	/**
	 * Редактор учетных записей
	 */
	public function accountsEditorPage($f3,$params=NULL)
	{
		$loginError=S::loginTest($this->db);
		
		if($loginError!=''){ $f3->reroute('/login/'.urlencode($loginError)); }
		if(!$f3->get('user.isAuthor') && !$f3->get('user.isAdmin')){
			$f3->error(404);
		}
		if(preg_match("/[^0-9]/",$params['id']))
        {
            $f3->reroute('/profile/'.urlencode('Ошибка передачи данных, попробуйте снова позже'));
        }
        if( preg_match("/[^(new|change)]/",$params['ed_type'])){
            $f3->reroute('/profile/'.urlencode('Ошибка передачи данных, попробуйте снова позже'));
        }
		AccountEditorPage::i()->addTitle('Редактор пользователей - Testify');
		//Пагинация
		$pageNum=1;
         
        if( $f3->exists('GET.page') ){
			if(preg_match("/[^0-9]/",$f3->get('GET.page') )){
                $f3->error(404);
            }else{
                $pageNum = $f3->get('GET.page');
            }
            AccountEditorPage::i()->addTitle('Страница '.$pageNum.'- Редактор пользователей - Testify');
		}
		
		//поиск
		$f3->set('GET', CFuns::sanitizeString($f3->get('GET')) ); // очистка GET
		$userSearchInput = $f3->exists('GET.user_search') ? $f3->get('GET.user_search'):'';
		$searchWords=CFuns::getSearchList($userSearchInput);
		AccountEditorPage::i()->addSearch($params[0]);

		//Получение изначальных данных
		 $msg='';
		 $head_label='Добавить пользователя';
		 $pass_notiece='';
		 $rec=S::getUsershema();
		 $user_id=$params['id'];$params['ed_type'];
		 $roles=[
			['type'=>'isAuthor','title'=>'Автор','checked'=>''],
			['type'=>'isAdmin','title'=>'Админ','checked'=>'']
		 ];
		 $f3->set('POST',CFuns::sanitizeString($_POST));

		if($user_id!=='0')
		{
			/**(И) если: Первый вход на страницу */
			$pass_notiece='<p class="alert_txt">Внимание: пароль зашфрован</p><p class="alert_txt">Если хотите изменить пароль, введите новое значение</p>';
			$head_label='Изменить пользователя';

			$rec=S::getUsers($this->db,
				['where'=>'1','having'=>'1','ws'=>[]],
				$user_id,
				0
			)[0];
			if(empty($rec)){
				$f3->error(404);
			}
		}
	
		//принять посланные данные
		if(
			$f3->exists('POST.u_login') && 
			$f3->exists('POST.u_pass')&&
			$f3->exists('POST.u_name')&&
			$f3->exists('POST.u_created')&&
			$f3->exists('POST.u_status')
		){
			$login = $f3['POST']['u_login'];
			$pass = strlen($f3['POST']['u_pass'])==64 ?  $f3['POST']['u_pass']: hash('sha256',$f3['POST']['u_pass']);
			$name = $f3['POST']['u_name'];
			$creat = $f3['POST']['u_created']!=''?$f3['POST']['u_created']:date('Y-m-d H:i:s');
			$status=$f3['POST']['u_status'];
			
			switch($params['ed_type']){
				case 'change'://Изменение
					S::update($this->db,[$login,$pass,$creat,$user_id],[$name,$status,$user_id]);
					if($f3->exists('POST.isAdmin')){
						S::becomeAdmin($this->db,S::getUser_login($this->db,$login)[0]['id']);
					}else{
						S::removeAdmin($this->db,S::getUser_login($this->db,$login)[0]['id']);
					}
					if($f3->exists('POST.isAuthor')){
						S::becomeAuthor($this->db,S::getUser_login($this->db,$login)[0]['id']);
					}else{
						S::removeAuthor($this->db,S::getUser_login($this->db,$login)[0]['id']);
					}
					$msg.='Пользователь изменен!';
					break;
				case 'new'://добавление
					
					//Проверка на существующего пользователя
					if(count(S::getUser_login($this->db,$login))===0){
						S::create($this->db,[$login,$pass,$creat],[$name,$status]);
						$msg.='Пользователь добавлен!';
						
						if($f3->exists('POST.isAdmin')){
							S::becomeAdmin($this->db,S::getUser_login($this->db,$login)[0]['id']);
						}
						if($f3->exists('POST.isAuthor')){
							S::becomeAuthor($this->db,S::getUser_login($this->db,$login)[0]['id']);
						}
					}else{
						
						$rec["name"]=$name;
						$rec["login"]=$login;
						$rec["created"]=$creat;
						$rec["status"]=$status;
						$rec["pass"]=$pass;
						$msg.='Пользователь с таким логином уже существует!';
				   }
					break;
			}
		}
		else if($_SERVER['REQUEST_METHOD']=='POST')
		{
			$f3->reroute('/'.urlencode('Ошибка данные не переданы! Попробуйте снова'));
		}
		$where = Test::GetWhere( $searchWords, ["name","login","pass","id","created"],["authorName","adminName"] );
		$users=S::getUsers($this->db,
		$where,
			0,
			$pageNum
		);			

		AccountEditorPage::i()
			->addAccountEditor(
				$rec,
				$head_label,
				$user_id,
				$pass_notiece,
				$f3['BASE'].$params[0],
				$roles
			)
			->addUsersList(
				$users,
				$userSearchInput!=''?$f3['BASE'].$params[0]:'')
			->addPageNavigation(
				Courses::i()->addPageNavigation(
					$pageNum,
					S::getUsersCount($this->db,$where),
					$f3['BASE'].$params[0]
					)->pageHtml
			)
			->addHeader('headerTitle.htm','Управление пользователями')
			->addGoBackBtns()
			->addErrorModalWrap($msg)
			->addBurgerMenu()
			->addFooter();

		echo AccountEditorPage::i()->htmlRender(
			body:AccountEditorPage::i()->body(),
			head:AccountEditorPage::i()->head()
		);
	}	
	/**
	 * Релизация возможности стать Автором
	 */
	public function becomeAuthor($f3,$params=null)
	{
		$loginerr=S::loginTest($this->db);
		if($f3->get('user.isAuth') && S::becomeAuthor($this->db, $f3->get('user.id')))
		{
			$f3->reroute('/profile/notes/'.urlencode('Вы стали Автором!'));
		}
	}
	/**
	 * Выход пользователя из аккаунта
	 */
	public function exitPage($f3,$params=NULL) {
		S::exit();
		header('Location: '.$f3->get("BASE"));
	}
}
?>