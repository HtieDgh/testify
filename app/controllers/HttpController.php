<?php
class HttpController
{
	/**
     * <p>Отображение Профиля пользователя;
	 * Попытка входа в профиль - проверка авторизации и отображение формы в случае провала</p>
    */
	public function mainPage($f3,$params=NULL)
	{
	    global $db;
		$f3->copy("main_title","my_title");
		
		$logErrTxt=Security::loginTest($f3,$db);
		$v=new Views($f3);
		$err_txt = urldecode( isset($params["err_txt"])?$params["err_txt"]:'');
		if($f3->get("user.id")===0){
			//Отображение формы входа
			$logErrTxt.=$err_txt;
			echo $v->Htmlrender(
				'Вход - Testify',
				$v->BodyMainPage(
				$v->Header(),
				[$v->Login($logErrTxt)],
				$v->Footer()
				),
			);

		}else{
			//Отображение Профиля, если пользователь прошел аутентификацию
			$t=new Tests($db);
			$view_data['sr_cancel']='';
			$view_data['teast_head']='Ваши тесты';
			$view_data['st_cancel']='';
			$view_data['u']=$f3->get("user");
			$view_data['u']['ava_url']=$f3->get("SITE_DOMAIN").'user_avas/default_ava.png';
			
			if(isset($_GET['s_ur'])){
				$view_data['s_ur']=$_GET['s_ur'];
				$s_ur=$_GET['s_ur'];
				$view_data['sr_cancel']='Отменить поиск';
			}else{
				$s_ur='';
				$view_data['s_ur']='Поиск по вашим попыткам';//TODO
			}
			$view_data['ur']=$t->GetUserResults(
				$f3->get("user.id"),
				Tests::GetWhere($s_ur,['t.title','r.status::text','r.date::text'])
			);
			$view_data['err_txt']=$err_txt;

			if(isset($_GET['s_ut'])){
				$view_data['s_ut']=$_GET['s_ut'];
				$s_ut=$_GET['s_ut'];
				$view_data['st_cancel']='Отменить поиск';
			}else{
				$s_ut='';
				$view_data['s_ut']='Поиск по созданным вами тестам';
			}

			// В зависимости от уровня доступа шаблону нужны разные данные для отображения
			switch ($view_data['u']['access']) {
				case 1: // Роль Участник теста
					//У Участника тестирования нет тестов
					$view_data['ut'] = array();
					break;
				case 2:// Роль Создатель теста
					$view_data['ut'] = $t->GetUserTests(
						$f3->get("user.id"),
						Tests::GetWhere($s_ut,['title','"start"::text','"end"::text'])
					);

					break;
				case 3: // Роль Менеджер
					$view_data['ut'] = $t->GetAllUserTests(
						Tests::GetWhere($s_ut,['title','"start"::text','"end"::text','s.name'])
					);
					$view_data['s_ut'] = 'Поиск по всем созданым тестам';
					$view_data['teast_head']='Тесты в системе';
					break;
				default:
					# TODO
					break;
			}
			
			echo $v->Htmlrender(
				'Профиль - Testify',
				$v->BodyMainPage(
				$v->Header(TRUE),
				[$v->Profile($view_data)],
				$v->Footer()
				),
			);
		}
	}
	/**
     * <p>Редактирование данных теста (без вопросов) в интерфейсе будет шаг 1/3</p>
    */
	public function editorTestPage($f3,$params=NULL)
	{
		global $db;
		$logErrTxt=Security::loginTest($f3,$db);
		$log_err_txt=Security::loginTest($f3,$db);

		if($f3->get("user.access")>1){
			// Пользователь найден
			$test_data=null;
			$err_txt='';
			$vd=null;
			if(!preg_match("/[^0-9a-z_]/",$params["variant_link"]))
			{
			// Определение: редактирование существующего или создание нового теста	
				if($params["variant_link"]!=='0')
				{
					// Изменение существующего теста: получение данных
					$t=new Tests($db);
					if( $t->CheckTestAuthor_link($params["variant_link"], $f3->get('user.id')) || $f3->get("user.access")==3)
					{
						$test_data=$t->GetUserTest($params["variant_link"])[0];
						
						if (count($test_data) == 0) {
							$f3->reroute("/".urlencode('Ошибка: Данных теста не найдено'));
						}
						$vd=$t->GetAllTestVariants($params["variant_link"]);
					}else{
						$err_txt='Попытка изменить тест, которого не существует, или тест другого пользователя. Повторите операцию или закройте данное окно и попробуйте создать свой тест.';
					}
				}
			}else{
				$f3->reroute("/".urlencode('Перед тем как редактировать тесты вам необходимо войти.'));
			}

			// Отображение Редактора теста с данными если они были найдены
			$v=new Views($f3);
			echo $v->Htmlrender(
				'Редактор - Testify',
				$v->BodyMainPage(
				$v->Header(TRUE),
				[
					$v->TestEditor($test_data,$vd)
				],
				$v->Footer()
				)
			);

		}else{
			//Пользователь не найден: переход на начальную страницу
			$f3->reroute("/".urlencode("Перед тем как использовать Редактор, Вам необходимо Войти"));
		}
	}
	/**
     * <p>Редактирование вопросов конкретного варианта: в интерфейсе будет шаг 2/3</p>
    */
	public function editorQuestionsPage($f3,$params=NULL) {
		global $db;
		$logErrTxt=Security::loginTest($f3,$db);
		
		if($f3->get("user.access")<=1){
			//Пользователь не найден: переход на начальную страницу
			$f3->reroute("/".urlencode("Перед тем как использовать Редактор, Вам необходимо Войти"));
		}
		// Пользователь найден
		if(preg_match("/[^0-9a-z]/",$params["variant_link"]) || $params["variant_link"]=='0')
		{
			$f3->reroute("/".'Произошла ошибка при получении данных теста. Повторите операцию или создайте новый тест');
		}
		$err_txt='';
		//variant_link чист и готов к обработке
		//получение списка вопросов
		$t=new Tests($db);
		$q_data=$t->getQuestionData($params["variant_link"]);

		//формаирование разметки и возврат клиенту
		$v=new Views($f3);
		echo $v->Htmlrender(
			'Редактор - Testify',
			$v->BodyMainPage(
			$v->Header(TRUE),
			[
				$v->QuestionEditor($q_data)
			],
			$v->Footer()
			)
		);
	}
	/**
	 * <p>Плеер для теста</p>
	*/
	public function testPage($f3,$params=NULL) {
		global $db;
		Security::loginTest($f3,$db);
        if( $f3->get('user.access')==-1 )
        {
           $f3->reroute('/','Прежде чем проходить тест вам необходимо войти');
        }
		if(preg_match("/[^0-9a-z]/",$params["variant_link"]) || $params["variant_link"]=='0')
		{
			$f3->reroute("/".'Произошла ошибка при получении данных теста. Повторите операцию или откройте другой вариант');
		}

		$t=new Tests($db);
		//Получение данных о вопросах/варианте/тесте
		 $test_data=$t->GetFullUserTest($params["variant_link"]);
		//Проверка периода действия теста
		$curDate=time();
		
		 if($curDate<strtotime($test_data['test']['start'])){
			$f3->reroute("/".'Данный тест недоступен для прохождения, начало тестирования: '.$test_data['test']['start']);
		 }
		 if($curDate>=strtotime($test_data['test']['end'])){
			$f3->reroute("/".'Прохождение невозможно, тестирование закончено: '.$test_data['test']['end']);
		 }
		//Для ссылки на файлы теста необходим логин пользователя создавшего тест
		 $author_login=$t->GetAuthorLogin($test_data['test']['id']);


		$v=new Views($f3);
		echo $v->Htmlrender(
			'Тестирование - Testify',
			$v->BodyMainPage(
			$v->Header(TRUE),
			[
				$v->Test( $test_data, Uploads::GetUserPath($f3->get('SITE_DOMAIN').$f3->get('user_test_data_path'),$author_login).$test_data['variant']['link'] )
			],
			$v->Footer()
			)
		);
	}

	/**
     * <p>Переносит на редактор вариантов теста editorTestPage</p>
    */
    public function editorPage($f3,$params=NULL)
	{
		global $db;
		if(preg_match("/[^0-9]/",$params["test_id"]))
		{
			$f3->reroute("/".urlencode("Неверно передана ссылка, повторите операцию еще раз."));
		}
		$t=new Tests($db);
		$f3->reroute("/edit/test/".$t->GetAllTestVariants_tid($params["test_id"])[0]['link']);
    }
	/**
	 * <p>Детализация одной попытки</p>
	*/
    public function checkResultPage($f3,$params=NULL)
	{
		global $db;
		$logErrTxt=Security::loginTest($f3,$db);
		
		if($f3->get("user.access")<1){
			//Пользователь не найден: переход на начальную страницу
			$f3->reroute("/".urlencode("Перед тем как посмотреть попытки, Вам необходимо Войти"));
		}
		// Пользователь найден
		if(preg_match("/[^0-9a-z]/",$params["variant_link"]) || $params["variant_link"]=='0')
		{
			$f3->reroute("/".'Произошла ошибка при получении данных варианта. Повторите операцию');
		}
		$t=new Tests($db);
		$res_data=$t->GetUserTestResults($params["variant_link"],$f3->get('user.id'));

		$v=new Views($f3);
		echo $v->Htmlrender(
			$res_data[0]['title'].': Результаты - Testify',
			$v->BodyMainPage(
			$v->Header(TRUE),
			[$v->Check($res_data)],
			$v->Footer()                  
			)
		);
    }
	/**
	 * <p>Просмотр статистики теста конкретного автора</p>
	*/
	public function statisticsPage($f3,$params=NULL) {
		global $db;
		$logErrTxt=Security::loginTest($f3,$db);
		
		if($f3->get("user.access")<=1){
			//Пользователь не найден: переход на начальную страницу
			$f3->reroute("/".urlencode("Перед тем как посмотреть статистику по тесту, Вам необходимо Войти"));
		}
		// Пользователь найден
		if(preg_match("/[^0-9]/",$params["test_id"]) || $params["test_id"]=='')
		{
			$f3->reroute("/".'Произошла ошибка при получении индентификатора теста. Повторите операцию');
		}
		$res_data=null;
		$visual_data['s_rslts']='Поиск по результатам';
		$visual_data['s_cancel']='';
		//Только Менеджер или Создатель теста может смотреть статистику по конкретному тесту
		$t=new Tests($db);

		if(!$t->CheckTestAuthor_tid($params['test_id'],$f3->get('user.id')) ){
			if($f3->get("user.access")!=3){
				$f3->reroute("/".'Вы не авторизованы для выполнения данной операции');
			}
        }
		//Обработка поискового запроса
		 if(isset($_GET['results_search']))
		 {
			$_GET=CFuns::sanitizeString($_GET);
			
			$search=$_GET['results_search'];
			$visual_data['s_rslts']=$search;
			
		 }else{
			$search='';
		 }
		
		$res_data=$t->GetTestStatistics_tid(
			$params['test_id'],
			[
				'variants'=>Tests::GetWhere( $search, ['v.title'] ),
				'results'=>Tests::GetWhere( $search, ['r.date::text','r.status::text','r.sum::text','s.name','v.title'] )
			]  );

		if($search!==''){
			$visual_data['s_cancel']='<a href="'.$f3->get("SITE_DOMAIN").'statistics/'.$params['test_id'].'">Отменить поиск</a>';
		}
		
		//Вывод статистики
		$v=new Views($f3);
		echo $v->Htmlrender(
			$res_data['test']['title'].': Статистика - Testify',
			$v->BodyMainPage(
			$v->Header(TRUE),
			[$v->Statistics($visual_data,$res_data['test'],$res_data['variants'],$res_data['results'])],
			$v->Footer()                
			)
		);
	}
	// Страница регистрации
	public function registPage($f3,$params=NULL){
		$v=new Views($f3);
		echo $v->Htmlrender(
			'Решистрация - Testify',
			$v->BodyMainPage(
			$v->Header(),
			[$v->Rigist()],
			$v->Footer()
			)
		);
	}

	public function accountsPage($f3,$params=NULL) {
		global $db;
		$logErrTxt=Security::loginTest($f3,$db);
		
		if($f3->get("user.access")!=3){
			//Пользователь не найден или не имеет доступа: переход на начальную страницу
			$f3->reroute("/".urlencode("Перед тем как редактировать учетные записи, Вам необходимо Войти"));
		}
		//Получение данных о поиске
		 if(isset($_GET['s_ua'])){
			$view_data['s_ua']=$_GET['s_ua'];
			$s_ut=$_GET['s_ua'];
			$view_data['st_cancel']='Отменить поиск';
		 }else{
			$s_ut='';
			$view_data['st_cancel']='';
			$view_data['s_ua']='Поиск по учетным записям';
		 }
		 $html_txt='';
		 $head_label='Новый пользователь';
		 $pass_notiece='';
		 $rec=array("name"=>"","login"=>"","pass"=>"","access"=>"","id"=>"","created"=>"");

		if(isset($_GET['user_id'])&&isset($_GET['edit_type'])){
			/**(И) если: Первый вход на страницу */
			$user_id=$_GET['user_id'];
			$ed_type=$_GET['edit_type'];
	
			$pass_notiece='<p class="alert_txt">Внимание пароль зашфрован</p><p class="alert_txt">Введите свое значение если хотите изменить пароль</p>';
			$head_label='Редактирование пользователя';
			$rec=Security::GetUser($db,$user_id);
		}else if(isset($_POST['user_id'])&&isset($_POST['edit_type'])){
			/*(И) если: отправлены данные из формы */
			$_POST=CFuns::sanitizeString($_POST);
			$user_id=$_POST['user_id'];
			$ed_type=$_POST['edit_type'];
		}else{
			$user_id='NULL';
			$ed_type='0';
		}

		if(isset($_POST['u_login']) && 
		isset($_POST['u_pass'])&&
		isset($_POST['u_access'])&&
		isset($_POST['u_name'])&&
		isset($_POST['u_created'])){
			$_POST=CFuns::sanitizeString($_POST);

			$login = $_POST['u_login'];
			$pass = strlen($_POST['u_pass'])==32 ?  $_POST['u_pass']:md5($_POST['u_pass']);
			$acs = preg_replace("/[^0-3]/","",$_POST['u_access']); 
			$name = $_POST['u_name'];
			$creat = $_POST['u_created']!=''?$_POST['u_created']:date('d.m.Y H:i:s');    
			switch($ed_type){
				case '1'://Изменение
					$params=array($login,$pass,$acs,$name,$creat,$user_id);
					Security::SaveUser($db,$ed_type,$params);

					$html_txt.='<p class="good_txt"><br>Пользователь изменен!</p>';
					break;
				case '0'://добавление
					$params=array($login,$pass,$acs,$name,$creat);
					//Проверка на существующего пользователя
					if(count(Security::GetUser_login($db,$login))===0){
						Security::SaveUser($db,$ed_type,$params);
						$html_txt.='<p class="good_txt"><br>Пользователь добавлен!</p>';
					}else{
						$rec=array("name"=>$name,"login"=>$login,"pass"=>"","access"=>$acs,"id"=>"","created"=>$creat);
						$html_txt.='<p class="alert_txt"><br>Пользователь с таким логином уже существует!</p>';
				   }
					break;
				default:
					$f3->reroute('/'.urlencode('Ошибка: передан неверно тип редактирования записи')) ;
					break;
			}
				


		}else if($_SERVER['REQUEST_METHOD']=='POST'){
			$f3->reroute('/'.urlencode('Ошибка данные не переданы! Попробуйте снова'));
		}

		$users=Security::GetAllUsers($db,
			Tests::GetWhere( $s_ut, ["name","login","pass","access::text","id::text","created::text"] )
		);

		$v=new Views($f3);
		echo $v->Htmlrender(
			'Решистрация - Testify',
			$v->BodyMainPage(
			$v->Header(TRUE),
			[$v->Accounts(
				$view_data,
				$users,
				$rec,
				$head_label,
				$user_id,
				$ed_type,
				$pass_notiece,
				$html_txt
			)],
			$v->Footer()
			)
		);

	}
	/**
	 * <p>Удаление пользователя</p>
	*/
    public function deleteUser($f3,$params=NULL) {
        global $db;

        $log_err_txt=Security::loginTest($f3,$db);
        if( $f3->get('user.access')!=3 )
        {
            $f3->reroute('/'.urlencode('Недостаточно прав для удаления пользоватлея'));
        }
		if(preg_match("/[^0-9]/",$params["user_id"])){
			$f3->reroute("/".urlencode('Произошла ошибка при получении индентификатора пользоватлея. Повторите операцию.'));
		}
		Security::deleteUser($db,$params["user_id"]);
		$f3->reroute("/edit/accounts");
    }
	function exitPage($f3,$params=NULL) {
		Security::exit();
		header('Location: '.$f3->get("SITE_DOMAIN"));
	
	}

}

?>