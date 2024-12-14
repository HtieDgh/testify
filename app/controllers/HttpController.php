<?php
class HttpController
{
    //Попытка входа в профиль - проверка авторизации и отображение формы в случае провала
	public function mainPage($f3,$params=NULL)
	{
	    global $db;
		$f3->copy("main_title","my_title");
		
		$logErrTxt=Security::loginTest($f3,$db);
		$v=new Views();
		if($f3->get("user.id")===0){
			//Отображение формы входа
			$f3->set("content","loginPage.htm");
			
			
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
			/* $t=new T; */
			$view_data['u']=$f3->get("user");
			$view_data['u']['ava_url']=$f3->get("SITE_DOMAIN").'user_avas/default_ava.png';
			$view_data['s_ut']='Поиск по созданным вами тестам';//TODO
			$view_data['s_ur']='Поиск по вашим попыткам';//TODO
			$view_data['ut']=array();//$t->GetUserTests($user->user_data['id']);
			$view_data['ur']=array();//$t->GetUserResults($user->user_data['id']);
			
			echo $v->Htmlrender(
				'Профиль - Testify',
				$v->BodyMainPage(
				$v->Header(TRUE),
				[$v->Profile($view_data)],
				$v->Footer()
				),
			);
		}
		/* echo Template::instance()->render('layout.htm'); */
	}
    function loginPage()
	{

    }
    function profilePage()
	{

    }
	// Страница регистрации
	public function registPage($f3,$params=NULL){
		$v=new Views();
		echo $v->Htmlrender(
			'Вход - Testify',
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