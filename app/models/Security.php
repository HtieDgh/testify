<?php 
class Security
{
	

	/**
	 * <p>Проверка авторизации и заполнение $f3[user]</p>
	 * @return string login_error текст ошибки в случае провала авторизации
	 */
	public static function loginTest($f3,$db)
{
		$login_error='';
		// Извлекается логин и пароль из переданных данных предварительно отфильтровывая лишие символы если они есть
		$f3->set("POST",CFuns::sanitizeString($f3->get("POST")));

		// Изначально устанавливается id пользователя=0 (пользователя нет)
		$f3->mset(
			array(
				"user.login"=> $f3->get("POST.login") !== NULL ? $f3->get("POST.login") : ( $f3->get("COOKIE.security_login") !== NULL ? $f3->get("COOKIE.security_login") : ''),
				"user.password"=> $f3->get("POST.password")!==NULL  ?  md5($f3->get("POST.password")) : ( $f3->get("COOKIE.security_password")!==NULL ? $f3->get("COOKIE.security_password") : ''),
				"user.id"=>0
			)
		);
		
		// Если заданы логин и пароль, проверяется их актуальность
		if( $f3->get("user.login")!=='' && $f3->get("user.password")!=='' )
		{
			$query="SELECT s.id,s.access,s.name FROM s_a as s WHERE  s.login='".preg_replace("/[^a-zA-Z0-9_@.]/","",$f3->get("user.login"))."' AND  s.pass='".$f3->get("user.password")."'";
			$result=$db->exec($query);

			// Если пользователь с такими данными найден
			if (count($result)>0)
			{
                // Данные о пользователе сохраняются в переменную
                
				$f3->set("user.access", (int)$result[0]['access']);
				$f3->set("user.name",$result[0]['name']);
				$f3->set("user.id",(int)$result[0]['id']);
				// Логин и пароль(зашифрованный) сохраняются в COOKIE пользователя
				static::updateCookie('security_login',$f3->get("user.login"),"/",$_SERVER['HTTP_HOST'],TRUE);
				static::updateCookie('security_password',$f3->get("user.password"),"/",$_SERVER['HTTP_HOST'],TRUE);
			}
			else
			{
				$login_error='Неправильный логин или пароль.<br><br>';	
			}
		}else if($f3->get("user.login")!=='' xor $f3->get("user.password")!==''){
			$login_error='Переданы не все Параметры авторизации, попробуйте Войти снова!<br><br>';
        }
		return $login_error;
	}
	/**
	 * <p>Регистрирует пользователя в системе</p>
	 * @return array массив return_out - для JSON ответа
	*/
	public function registUser(&$login,&$pass,&$name)
	{
		$return_out['err']=FALSE;
		$cur_date=date('Y-m-d');
		$return_out['err_txt']='';
		//Проверка на существующую учетную запись в БД
		$q="SELECT 1 FROM `s_a` WHERE `name`='$name' OR `login`='$login'";
		$result=queryMysql($q);
		if(count($result)==0){
			$q="INSERT INTO `s_a` (`id`, `login`, `pass`,`name`,`access`,`created`) VALUES (NULL,?, ?, ?,1,'$cur_date')";
			$return_out['err_txt']=preparedQuery($q,array('sss', $login,$pass,$name),'Учетная запись зарегистрирована! Введите свой email и пароль <a href="'.SITE_DOMAIN.'">здесь</a></p>');
			//Обновление куки для последующей авторизации
			$this->updateCookie('security_login',$login,"/",$_SERVER['HTTP_HOST'],TRUE);
			$this->updateCookie('security_password',$pass,"/",$_SERVER['HTTP_HOST'],TRUE);
		}else{
			$return_out['err']=TRUE;
			$return_out['err_txt']='Учетная запись с таким логином или именем уже занята! Попробуйте снова';
		}
		return $return_out;
	}


	
	public function getUserInfo($id=0){
		queryMysql("SET lc_time_names = 'ru_UA'");
		$id=$id===0?$this->user_data['id']:$id;
		$query="SELECT DATE_FORMAT(`created`,'%e %M %Y')as 'frmtd_created',s.`ava`,s.`status` FROM `s_a` as s WHERE  s.`id`=".$id;
		$result=queryMysql($query);
		$rec=$result->fetch_assoc();
		$this->user_data['created']=$rec['frmtd_created'];
		$this->user_data['ava_url']=$rec['ava'];
		$this->user_data['status']=$rec['status'];
		return $this->user_data;
	}
	/**
	 * <p>Обновляет cookie</p>
	 * 
	 */
	public static function updateCookie($key,$val,$url,$host,$cookie_time=false)
	{
		// время жизни COOKIE-данных продлевается на 24 часа или до закрытия браузера 
		if($cookie_time){
			$cookie_time=time() + 24 * 3600;
		}
		setcookie($key,$val,$cookie_time,$url,$host);
	}
	
	public static function exit()
	{
		// Логин и пароль будут удалены из cookie
		setcookie('security_login', '',1, "/", $_SERVER['HTTP_HOST']);
		setcookie('security_password', '', 1, "/", $_SERVER['HTTP_HOST']);
		setcookie('user_name', '', 1, "/", $_SERVER['HTTP_HOST']);
		setcookie('test_link', '', 1, "/", $_SERVER['HTTP_HOST']);
	}


}
?>