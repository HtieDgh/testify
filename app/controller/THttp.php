<?php namespace controller;
use model\Security as S;
use model\Test;
use model\Uploads;
use model\CFuns;
use view\Courses;
use view\TestEditorPage;
use view\TestPage;

//контроллер модуля тестирования
class THttp{
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
     * Редактирование данных теста (без вопросов) в интерфейсе будет шаг 1/3
    */
	public function testEditorPage(\Base $f3,$params=NULL)
	{
		$loginError=S::loginTest($this->db);

		if(!$f3->get("user.isAuth")){
			//Пользователь не найден: переход на форму входа
			$f3->reroute("/login/".urlencode("Перед тем как использовать Редактор, вам необходимо Войти"));
        }
        if(!$f3->get("user.isAuthor") && !$f3->get("user.isAdmin")){
            $f3->error(404);
        }
        // Пользователь найден
        $test_data=null;
        $variant_data=[];
        if(preg_match("/[^0-9a-z_]/",$params["variant_link"]))
        {
            $f3->reroute("/profile/".urlencode('Перед тем как редактировать тесты вам необходимо войти.'));
        }
        // Определение: редактирование существующего или создание нового теста	
        if($params["variant_link"]!=='0')
        {
            // Изменение существующего теста: получение данных
            $t=new Test($this->db);
            if( !$t->CheckTestAuthor_link($params["variant_link"], $f3->get('user.id')) && !$f3->get("user.isAdmin"))
            {
                $f3->reroute("/profile/".urlencode('Попытка изменить тест, которого не существует, или тест другого пользователя. Повторите операцию или закройте данное окно и попробуйте создать свой тест.'));
            }

            $test_data=$t->GetUserTest($params["variant_link"])[0];
                
            if (count($test_data) == 0) {
                $f3->reroute("/profile/".urlencode('Ошибка Данных теста не найдено'));
            }
            $variant_data=$t->GetAllTestVariants($params["variant_link"]);
        }

        // Отображение Редактора теста с данными если они были найдены
        TestEditorPage::i()
            ->addTitle('Редактор - Testify')
            ->addHeader('headerTitle.htm','Редактор теста')
            ->addFooter()
            ->addBurgerMenu()
            ->addErrorModalWrap()
            ->addTestEditor($test_data,$variant_data);

        echo TestEditorPage::i()->htmlRender(
            body:TestEditorPage::i()->body(),
            head:TestEditorPage::i()->head()
        );
	}
    /**
     * Редактирование вопросов конкретного варианта: в интерфейсе будет шаг 2/3
    */
	public function questionsEditorPage($f3,$params=NULL) {
		$loginError=S::loginTest($this->db);
		
		if(!$f3->get("user.isAuth")){
			//Пользователь не найден: переход на форму входа
			$f3->reroute("/login/".urlencode("Перед тем как использовать Редактор, вам необходимо Войти"));
        }
        if(!$f3->get("user.isAuthor") && !$f3->get("user.isAdmin")){
            $f3->error(404);
        }
		// Пользователь найден
		if(preg_match("/[^0-9a-z]/",$params["variant_link"]) || $params["variant_link"]=='0')
		{
			$f3->reroute("/profile/".urlencode('Произошла ошибка при получении данных теста. Повторите операцию или создайте новый тест'));
		}
		$err_txt='';
		//variant_link чист и готов к обработке
		//получение списка вопросов
		$t=new Test($this->db);
		$q_data=$t->getQuestionData($params["variant_link"]);

		//формаирование разметки и возврат клиенту
        TestEditorPage::i()
            ->addTitle('Редактор - Testify')
            ->addHeader('headerTitle.htm','Редактор вопросов')
            ->addFooter()
            ->addBurgerMenu()
            ->addErrorModalWrap()
            ->addQuestionEditor($q_data);

        echo TestEditorPage::i()->htmlRender(
            body:TestEditorPage::i()->body(),
            head:TestEditorPage::i()->head()
        );
	}
    /**
     * Переносит на редактор вариантов теста testEditorPage
    */
    public function editorPage($f3,$params=NULL)
	{
        S::loginTest($this->db);
		if(preg_match("/[^0-9]/",$params["test_id"]))
		{
			$f3->reroute("/profile/".urlencode("Неверно передана ссылка, повторите операцию еще раз."));
		}
        if(!$f3->get("user.isAuthor") && !$f3->get("user.isAdmin")){
            $f3->error(404);
        }
		$t=new Test($this->db);
        $vars = $t->GetAllTestVariants_tid($params["test_id"]);
        if(empty($vars)){
            $f3->reroute("/editor/test/0");
        }
		$f3->reroute("/editor/test/".$vars[0]['unique_url']);
    }
    
    /**
	 * Плеер для теста
	*/
	public function testPage($f3,$params=NULL) {
        $loginError=S::loginTest($this->db);
        if(!$f3->get("user.isAuth")){
			//Пользователь не найден: переход на форму входа
			$f3->reroute("/login/".urlencode("Прежде чем проходить тест, вам необходимо Войти"));
        }

		if(preg_match("/[^0-9a-z]/",$params["variant_link"]) || $params["variant_link"]=='0')
		{
			$f3->reroute("/profile/".urlencode('Произошла ошибка при получении данных теста. Повторите операцию или откройте другой вариант'));
		}
       
		$t=new Test($this->db);
        //проверка на наличие теста
        if($t->isNotTest_link($params["variant_link"])){
            $f3->reroute("/profile/".urlencode('Такого варианта не существует'));
        }
		//Получение данных о вопросах/варианте/тесте
		 $test_data=$t->GetFullUserTest($params["variant_link"]);
		//Проверка периода действия теста
		 $curDate=time();
		
        if($curDate<strtotime($test_data['test']['start'])){
        $f3->reroute("/profile/".urlencode('Данный тест недоступен для прохождения, начало тестирования '));
        }
        if($curDate>=strtotime($test_data['test']['end'])){
        $f3->reroute("/profile/".urlencode('Прохождение невозможно, тестирование закончено '));
        }
		//Для ссылки на файлы теста необходим логин пользователя создавшего тест
         $upl=new Uploads($f3->get('user_data_path'),$t->GetAuthorLogin($test_data['test']['id']));
    
        TestPage::i()
            ->addTest(
                test_data:$test_data,
                testDir:$f3->get('BASE').'/'.$upl->test_dir.$test_data['variant']['unique_url'] )
            ->addTitle('Тестирование - Testify')
            ->addBurgerMenu()
            ->addFooter()
            ->addErrorModalWrap()
            ->addHeader('headerTitle.htm','Тест');

        echo TestPage::i()->htmlRender(
            body:TestPage::i()->body(),
            head:TestPage::i()->head()
        );  
		
	}
    /**
	 * Детализация одной попытки
	*/
    public function checkResultPage($f3,$params=NULL)
	{
		$loginError=S::loginTest($this->db);
        if(!$f3->get("user.isAuth")){
			//Пользователь не найден: переход на форму входа
			$f3->reroute("/login/".urlencode("Перед тем как посмотреть попытки, вам необходимо Войти"));
        }

		if(preg_match("/[^0-9a-z]/",$params["variant_link"]) || $params["variant_link"]=='0')
		{
			$f3->reroute("/profile/".urlencode('Произошла ошибка при получении данных варианта. Повторите операцию'));
		}

		$t=new Test($this->db);
		$res_data=$t->GetUserTestResults($params["variant_link"],$f3->get('user.id'));
        TestPage::i()
            ->addCheck( $res_data)
            ->addTitle('Тестирование - Testify')
            ->addBurgerMenu()
            ->addFooter()
            ->addGoBackBtns()
            ->addHeader('headerTitle.htm','Результаты');

        echo TestPage::i()->htmlRender(
            body:TestPage::i()->body(),
            head:TestPage::i()->head()
        ); 
    }
    /**
	 * Просмотр статистики теста конкретного автора
	*/
	public function statisticsPage($f3,$params=NULL) 
    {
		$loginError=S::loginTest($this->db);
        if(!$f3->get("user.isAuth")){
			//Пользователь не найден: переход на форму входа
			$f3->reroute("/login/".urlencode("Перед тем как посмотреть попытки, вам необходимо Войти"));
        }
        if(!$f3->get("user.isAuthor") && !$f3->get("user.isAdmin")){
            //пользователь не автор или админ
            $f3->error(404);
        }
		// Пользователь найден
		if(preg_match("/[^0-9]/",$params["test_id"]) || $params["test_id"]=='')
		{
			$f3->reroute("/profile/".urlencode('Произошла ошибка при получении индентификатора теста. Повторите операцию.'));
		}
        $params["variant_link"]=isset($params["variant_link"])?$params["variant_link"]:'';

        if( preg_match("/[^0-9a-z]/",$params["variant_link"]))
        {
            $f3->reroute("/profile/".urlencode('Произошла ошибка при получении данных теста'));
        }

		$res_data=null;
		$visual_data['s_rslts']='Поиск по результатам';
		$visual_data['s_cancel']='';
		//Только Менеджер или Создатель теста может смотреть статистику по конкретному тесту
		$t=new Test($this->db);

		if(!$t->CheckTestAuthor_tid($params['test_id'],$f3->get('user.id')) && !$f3->get('user.isAdmin'))
        {
            $f3->reroute("/profile/".urlencode('Вы не авторизованы для выполнения данной операции'));
        }
        
        //поиск
		$userSearchInput = $f3->exists('GET.user_search') ? $f3->get('GET.user_search'):'';
		$searchWords=CFuns::getSearchList($userSearchInput);

		//Навигация по страницам результатов
		$pageNum=1;
        
        if( $f3->exists('GET.page') ){
			if(preg_match("/[^0-9]/",$f3->get('GET.page') )){
                $f3->error(404);
            }else{
                $pageNum = $f3->get('GET.page');
            }
			TestPage::i()->addTitle('Страница '.$pageNum.' - Результаты - Статистика- Testify');
		}else{
			TestPage::i()->addTitle('Результаты - Статистика - Testify');
		}
        $visual_data['s_rslts']=$userSearchInput;
		
		$res_data=$t->GetTestStatistics_tid(
			test_id:$params['test_id'],
            varWhere:Test::GetWhere( $searchWords, ['v.title'] ),
            resWhere:Test::GetWhere( $searchWords, ['r.created','r.status','r.sum','s.name','v.title'] ),
            variant_link:$params["variant_link"]
        );

		if($userSearchInput!=='' || $params["variant_link"]!==''){
			$visual_data['s_cancel']='<a href="'.$f3->get("BASE").'/test/statistics/'.$params['test_id'].'">Отменить поиск</a>';
		}
        TestPage::i()
            ->addSearch($params[0])
            ->addStatistic($visual_data,$res_data['test'],$res_data['variants'],$res_data['results'],
                Courses::i()->addPageNavigation(
                    pageNum:$pageNum,
                    count:count($res_data['test']),
                    goToUrl:$params[0]
                )->pageHtml
            )
            ->addTitle('Результаты - Статистика - Testify')
            ->addBurgerMenu()
            ->addFooter()
            ->addGoBackBtns()
            ->addHeader('headerTitle.htm','Результаты');
        echo TestPage::i()->htmlRender(
            body:TestPage::i()->body(),
            head:TestPage::i()->head()
        );
	}
}
?>