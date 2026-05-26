<?php namespace model;
class Security
{

	/**
	 * <p>Проверка авторизации и заполнение $f3[user]</p>
	 * @return string login_error текст ошибки в случае провала авторизации
	 */
	public static function loginTest($db)
	{
		$f3=\Base::instance();
		$login_error='';
		// Извлекается логин и пароль из переданных данных предварительно отфильтровывая лишие символы если они есть
		$f3->set("POST",\model\CFuns::sanitizeString($f3->get("POST")));

		// Изначально устанавливается id пользователя=0 access=-1 (пользователя нет)
		$f3->mset(
			array(
				"user.login"=> $f3->get("POST.login") !== NULL ? $f3->get("POST.login") : ( $f3->get("COOKIE.security_login") !== NULL ? $f3->get("COOKIE.security_login") : ''),
				"user.password"=> $f3->exists("POST.password")  ? hash('sha256', $f3->get("POST.password") )  : ( $f3->exists("COOKIE.security_password") ? $f3->get("COOKIE.security_password") : ''),
				"user.id"=>0,
				"user.isAuth"=>false
			)
		);

		// Если заданы логин и пароль, проверяется их актуальность
		if( $f3->get("user.login")!=='' && $f3->get("user.password")!=='' )
		{
			$q="SELECT s.id,s.pass, p.name, p.ava_url, p.status, DATE_FORMAT(s.`created`,'%e %M %Y') as 'frmtd_created', a.id as authorRole, adm.id as adminRole FROM `secured_account` s 
			INNER JOIN `profile` p USING(id)
			LEFT JOIN `author` a USING(id)
			LEFT JOIN `admin` adm USING(id)
			WHERE  s.login=:login AND s.pass=:pass";
			$result=$db->exec(
				[
					"SET lc_time_names = 'ru_ru'",
					$q
				]
				,array(
					[],
					[
						":login"=>preg_replace("/[^a-zA-Z0-9_@.]/","",$f3->get("user.login")),
						":pass"=>$f3->get("user.password")
					]
				)
			);
			// Если пользователь с такими данными найден
			if (count($result)>0)
			{
                // Данные о пользователе сохраняются в переменную
				$f3->mset([
					"user.isAuth"=>true,
					"user.isAuthor"=>$result[0]['authorRole'] !== null,
					"user.isAdmin"=>$result[0]['adminRole'] !== null,
					"user.name"=> $result[0]['name'],
					"user.id"=> (int)$result[0]['id'],
					"user.ava_url"=> $result[0]['ava_url'],
					"user.created"=> $result[0]['frmtd_created'],
					"user.status"=> $result[0]['status']
				]);
				// Логин и пароль(зашифрованный) сохраняются в COOKIE пользователя
				static::updateCookie('security_login',$f3->get("user.login"),'/',$_SERVER['HTTP_HOST']);
				static::updateCookie('security_password',$f3->get("user.password"),'/',$_SERVER['HTTP_HOST']);
				static::updateCookie('security_id',$f3->get("user.id"),'/',$_SERVER['HTTP_HOST']);
			}
			else
			{
				$login_error='Неправильный логин или пароль.';	
			}
		}else if($f3->get("user.login")!=='' xor $f3->get("user.password")!==''){
			$login_error='Переданы не все Параметры авторизации, попробуйте Войти снова!';
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
		$return_out['err_txt']='';
		//Проверка на существующую учетную запись в БД
		$q="SELECT 1 FROM secured_account WHERE login=:login";
		
		$result = $db->exec($q,
			[
				":login"=>$login
			]
		);

		if(count($result)==0)
		{
			$db->exec(
				"INSERT INTO secured_account (login, pass) VALUES (:login, :pass)",
				[
					":login"=>$login,
					":pass"=>$pass
				]
			);

			$id=$db->exec(
				"SELECT id FROM secured_account WHERE login=:login AND pass=:pass",
				[
					":login"=>$login,
					":pass"=>$pass
				]
			)[0]['id'];

			$db->exec(
				"INSERT INTO profile (id,name) VALUES (:id,:name)",
				[
					":id"=>$id,
					":name"=>$name
				]
			);
			
			//Обновление куки для последующей авторизации
			static::updateCookie('security_login',$login,'/',$_SERVER['HTTP_HOST']);
			static::updateCookie('security_password',$pass,'/',$_SERVER['HTTP_HOST']);
			static::updateCookie('security_id',$id,'/',$_SERVER['HTTP_HOST']);
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
	public static function updateCookie($key,$val,$url,$host)
	{
		// время жизни COOKIE-данных продлевается на 24 часа или до закрытия браузера 
		$cookie_time=time() + 24 * 3600;
		
		setcookie($key,$val,$cookie_time,$url,$host);
	}
	
	public static function exit()
	{
		// Логин и пароль будут удалены из cookie
		setcookie('security_login', '',1, '/', $_SERVER['HTTP_HOST']);
		setcookie('security_password', '', 1, '/', $_SERVER['HTTP_HOST']);
		setcookie('security_id', '', 1, '/', $_SERVER['HTTP_HOST']);
		setcookie('security_id', '', 1, '/', $_SERVER['HTTP_HOST']);
		session_start();
		session_unset();
		session_regenerate_id(true);
	}
   
    /**
     * <p>Получение данных учетных записей пользователей</p>
     *
     * @param  object $db
     * @param  array $where 
     * @param  int $user_id
     * @param  int $pageNum
     * @return array
	 * @see Tests::GetWhere
     */
    public static function getUsers(\DB\SQL &$db, array $where, int $user_id=0, int $pageNum=1){
		//Пагинация
		$limit=$pageNum!==0?'LIMIT '.($pageNum*10-10).',10':'';
		if($user_id!==0){
			$where['where']='sa.id=:uid AND('.$where['where'].')';
			$where['ws'][':uid']=$user_id;
		}
        return $db->exec(
			"SELECT
			 sa.id,
			 sa.login,
			 sa.pass,
			 p.name,
			 p.status,
			 sa.created,
			 CASE WHEN adm.id IS NOT NULL THEN 'Админ' ELSE '' END as adminName,
			 CASE WHEN a.id IS NOT NULL THEN 'Автор' ELSE '' END as authorName
			FROM secured_account sa
			INNER JOIN profile p USING(id)
			LEFT JOIN admin adm USING(id)
			LEFT JOIN author a USING(id)
			WHERE ".$where['where']."
			HAVING ".$where['having'].' '.
			$limit,
			$where['ws']         
        );
    }	
	/**
	 * <p>Вовращает кол-во найденых пользователей</p>
	 *
	 * @param  mixed $db
	 * @param  mixed $where
	 * @return int
	 * @see SHttp->accountsEditorPage()
	 * @see static::getUsers()
	 */
	public static function getUsersCount(\DB\SQL &$db, array $where):int
	{		
        return $db->exec(
			"SELECT COUNT(DISTINCT sa.id) as 'user_count'
			FROM secured_account sa
			INNER JOIN profile p USING(id)
			LEFT JOIN admin adm USING(id)
			LEFT JOIN author a USING(id)
			WHERE ".$where['where']."
			HAVING ".$where['having'],
			$where['ws']         
        )[0]['user_count'];
    }
	/**
	 * <p>Возвращает струкутру пользователя для вывода в интерфейсе</p>
	 *
	 * @return array
	 * @see SHttp->accountsEditorPage()
	 */
	public static function getUsershema():array
	{
		return [
			'id'=>'',
			'login'=>'',
			'pass'=>'',
			'name'=>'',
			'status'=>'',
			'created'=>'',
			'adminName'=>'',
			'authorName'=>''
		];
	}
	public static function getUserLogin(\DB\SQL &$db,$userID) : string
	{
		$out=$db->exec("SELECT login FROM secured_account WHERE id=?",$userID);
		return empty($out)?'':$out[0]['login'];
	}
    public static function getUser_login(\DB\SQL &$db,$user_login){
        return $db->exec("SELECT * FROM secured_account WHERE login=?",[$user_login]);
    }
	/**
	 * <p>Изменяет пользователя в системе. Универсальный метод.</p>
	 *
	 * @param  mixed $db
	 * @param  array $sa_arams
	 * @param  array $p_params
	 * @return void
	 * @see SHttp->accountsPage()
	 */
	public static function update(\DB\SQL &$db,array $sa_arams,array $p_params){
		$db->exec(
			["UPDATE secured_account 
			SET login = ?, 
				pass = ?,
				created=? 
			WHERE id = ?",
			"UPDATE profile 
			SET name = ?, 
				status = ? 
			WHERE id = ?"],
			[$sa_arams,$p_params]
		);
	}	
	/**
	 * <p>Создает пользователя в системе. Универсальный метод.</p>
	 *
	 * @param  mixed $db
	 * @param  array $sa_arams (login, pass, created)
	 * @param  array $p_params (name,status)
	 * @return void
	 * @see SHttp->accountsPage()
	 */
	public static function create(\DB\SQL &$db,array $sa_params,array $p_params){
		$db->begin();
		$db->exec("INSERT INTO secured_account (login, pass, created) VALUES (?, ?, ?)",$sa_params);
		$db->exec("SET @parent_id = LAST_INSERT_ID()");
		$db->exec("INSERT INTO profile (id,name,status)VALUES(@parent_id, ?, ?)",$p_params);
		$db->commit();
	}

	/**
	 * <p>Добавляет пользователю роль Админа. Универсальный метод.</p>
	 *
	 * @param  object $db
	 * @param  int $userID
	 * @return void
	 * @see SHttp->accountsPage()
	 */
	public static function becomeAdmin(\DB\SQL &$db,int $userID){
		if(count($db->exec("SELECT id FROM admin WHERE id=?",$userID))==0){
			return $db->exec("INSERT INTO admin (id) VALUES (?)",$userID);
		}
		return 0;
	}
	public static function removeAdmin(\DB\SQL &$db,int $userID){
		$db->exec("DELETE FROM admin WHERE id=?",$userID);
	}
	public static function deleteUser(\DB\SQL $db,$userID){
		return $db->exec("DELETE FROM secured_account WHERE id=?",[$userID]);
	}
	// позволяет пользователю стать автором
	public static function becomeAuthor(\DB\SQL &$db,$userID) : int
	{
		if(count($db->exec("SELECT id FROM author WHERE id=".$userID))==0){
			return $db->exec("INSERT INTO author(id) VALUES($userID)");
		}
		return 0;
	}
	public static function removeAuthor(\DB\SQL &$db,int $userID){
		return $db->exec("DELETE FROM author WHERE id=?",$userID);
	}
	public static function updateProfile(\DB\SQL &$db,$name,$status,$userId) 
	{
		$db->exec("UPDATE `profile` SET `name`=:name,`status`=:status  WHERE `profile`.`id` = :id",
		[
			':name'=>$name,
			':status'=>$status,
			':id'=>$userId
		]
		);
	}
	public static function updateAccount(\DB\SQL &$db,$login,$pass,$userId) 
	{
		$db->exec("UPDATE `secured_account` SET `login`=:login,`pass`=:pass WHERE `secured_account`.`id` = :id",
		[
			':login'=>$login,
			':pass'=>$pass,
			':id'=>$userId
		]
		);
		
	}

	/**
	 * Сменить ava_url у пользователя в БД
	 * 
	 */
	public static function changeUserAva(\DB\SQL &$db,int $userId,string $ava_url): ?bool
	{
		return $db->exec("UPDATE `profile` SET `ava_url`=? WHERE `id`=?",[$ava_url,$userId]);
	}
		
	/**
	 * Удаляет пользователя из БД
	 *
	 * @param  mixed $db
	 * @param  mixed $userID
	 * @return void
	 */
	public static function delete(\DB\SQL &$db,int $userID) 
	{
		return $db->exec("DELETE FROM secured_account WHERE `id`=?",$userID);
	}
}
?>