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
			$view_data['u']=$f3->get("user");
			$view_data['u']['ava_url']=$f3->get("SITE_DOMAIN").'user_avas/default_ava.png';
			$view_data['ur']=$t->GetUserResults($f3->get("user.id"));
			$view_data['s_ur']='Поиск по вашим попыткам';//TODO
			$view_data['err_txt']=$err_txt;
			// В зависимости от уровня доступа шаблону нужны разные данные для отображения
			switch ($view_data['u']['access']) {
				case 1: // Роль Участник теста
					//У Участника тестирования нет тестов
					$view_data['ut'] = array();
					break;
				case 2:// Роль Создатель теста
					$view_data['s_ut'] = 'Поиск по созданным вами тестам';//TODO
					$view_data['ut'] = $t->GetUserTests($f3->get("user.id"));
					break;
				case 3: // Роль Менеджер
					$view_data['s_aut'] = 'Поиск по всем созданым тестам';//TODO
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
					if( $t->CheckTestAuthor_link($params["variant_link"], $f3->get('user.id')) )
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
				$err_txt='Произошла ошибка при получении данных теста. Повторите операцию или создайте новый тест';
			}
			if($err_txt!==''){
				
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
		if(preg_match("/[^0-9a-z_]/",$params["variant_link"]))
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
    function profilePage()
	{

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


	function exitPage($f3,$params=NULL) {
		Security::exit();
		header('Location: '.$f3->get("SITE_DOMAIN"));
	
	}

}

?>