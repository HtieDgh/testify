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

		// Изначально устанавливается id пользователя=0 access=-1 (пользователя нет)
		$f3->mset(
			array(
				"user.login"=> $f3->get("POST.login") !== NULL ? $f3->get("POST.login") : ( $f3->get("COOKIE.security_login") !== NULL ? $f3->get("COOKIE.security_login") : ''),
				"user.password"=> $f3->get("POST.password")!==NULL  ?  md5($f3->get("POST.password")) : ( $f3->get("COOKIE.security_password")!==NULL ? $f3->get("COOKIE.security_password") : ''),
				"user.id"=>0,
				"user.access"=>-1
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
	public static function registUser($db,$login,$pass,$name)
	{
		$return_out['err']=FALSE;
		$cur_date=date('Y-m-d');
		$return_out['err_txt']='';
		//Проверка на существующую учетную запись в БД
		$q="SELECT 1 FROM s_a WHERE name=:name OR login=:login";
		
		$result = $db->exec($q,
			array(
				":name"=>$name,
				":login"=>$login,
			)
		);

		if(count($result)==0){
			$q="INSERT INTO s_a (login, pass,name,access,created) VALUES (?, ?, ?,1,'$cur_date')";

			$res=$db->exec(
				$q,
				array(
					
						$login,
						$pass,
						$name						
				)
			);
			
			//Обновление куки для последующей авторизации
			static::updateCookie('security_login',$login,"/",$_SERVER['HTTP_HOST'],TRUE);
			static::updateCookie('security_password',$pass,"/",$_SERVER['HTTP_HOST'],TRUE);
		}else{
			$return_out['err']=TRUE;
			$return_out['err_txt']='Учетная запись с таким логином или именем уже занята! Попробуйте снова';
		}
		return $return_out;
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

   /**
     * <p>Получение данных учетных записей пользователя</p>
     * @param string where - результат работы функции GetWhere
     * @see Tests::GetWhere
    */
    public static function GetAllUsers($db,$where=''){
        return $db->exec("SELECT *
        FROM s_a
        ".($where!=''?"WHERE $where":'')            
        );
    }
    public static function GetUser($db,$user_id){
        return $db->exec("SELECT * FROM s_a WHERE id=$user_id")[0];
    }
    public static function GetUser_login($db,$user_login){
        return $db->exec("SELECT * FROM s_a WHERE login=?",[$user_login]);
    }
    /**
     * <p>
     * Сохраняет пользователя в системе на странице регистрации
     * ed_type 1 $params=array($login,$pass,$acs,$name,$status,$creat,$user_id);
     * ed_type 0 $params=array($login,$pass,$acs,$name,$status,$creat);
     * </p>
     * @return bool true если получилось сохранить
    */
    public static function SaveUser($db,$ed_type,$params){
        switch($ed_type){
            case '1':
                $q="UPDATE s_a SET login = ?, pass = ?,access=?,name=?,created=? WHERE s_a.id = ?";
                $scs_msg='Пользователь изменен!';
                break;
            case '0':
                $q="INSERT INTO s_a (login, pass, access,name, created) VALUES (?, ?, ?, ?, ?)";
                $scs_msg='Пользователь добавлен!';
                break;
            default:
                return false;
                break;
        }
        $db->exec($q,$params);
        return true;
    }
	public static function deleteUser($db,$user_id){
		$db->exec("DELETE FROM s_a WHERE id=?",[$user_id]);
	}
}
?>