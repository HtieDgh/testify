<?php
class XHttpController
{
    /**
     * <p>Регистрация новго пользователя</p>
    */
    public function newUser($f3,$params=NULL){
        global $db;
        $return_out=array();
        if(isset($_POST['name'])&&
        isset($_POST['password'])&&
        isset($_POST['login'])
        ){
            //подготовка данных перед внесением в бд
            $_POST=CFuns::sanitizeString($_POST);
            $login=$_POST['login'];
            $pass=md5($_POST['password']);
            $name=$_POST['name'];
            // Регистрация пользователя
            $return_out=Security::registUser($db,$login,$pass,$name);
        } else {
            //Редкая ситуация когда, из-за ошибки передачи, не все данные дошли до сервера
            $return_out['err']=TRUE;
            $return_out['err_txt']='Параметры не переданы! Свяжитесь с администратором или попробуйте снова';
        }
        echo json_encode($return_out);
    }
    /**
     * <p>Регистрация новго пользователя</p>
    */
    public function saveTestFiles($f3,$params=NULL) {
        global $db;

        $return_out['err']=FALSE;
        $return_out['err_txt']='';

        $log_err_txt=Security::loginTest($f3,$db);

        if(!isset($_POST['test_link']) || !isset($_POST['test_title']) || preg_match("/[^0-9a-z_]/",$_POST['test_link']))
        {
            //Ошибка когда Важные параметры не передаы
            $return_out['err']=TRUE;
            $return_out['err_txt']='При создании теста параметры POST переданы не были: повторите попытку';
            echo json_encode($return_out);
            return;
        }

        if($f3->get('user.access')>1)
        {
        
            $_POST=sanitizeString($_POST);
            
            $upl=new Uploads($f3->get('user'));
            $t=new Tests($db);

            if($_POST['test_link']=='')
            {
                //Новый тест
                //Генерация test_link для будущего теста который будет создан в new_test
                $test_link=md5( $_POST['test_title'].$f3->get('user.name') );
                
            }else{
                
                //Кеширование ссылки теста для загрузки
                if(isset($_COOKIE['test_link']))
                {
                    $test_link=$_POST['test_link'];
                    //Файл для изменяемого теста уже был отправлен
                    if($_POST['test_link'] != $_COOKIE['test_link'])
                    {
                                                        
                        //Пользователь изменяет другой тест
                       
                        $user->updateCookie('test_link',$_POST['test_link'],"/",$_SERVER['HTTP_HOST'],TRUE);
                        //Удаление файлов если они существуют
                        $upl->DeleteTest($test_link);
                    }
                }else if( $t->CheckTestAuthor_link($f3->get('user.id'),$_POST['test_link']) )
                {
                    //Изменение существующего
                    $test_link=$_POST['test_link'];
                    $user->updateCookie('test_link',$_POST['test_link'],"/",$_SERVER['HTTP_HOST'],TRUE);
                    //Удаление файлов если они существуют
                    $upl->DeleteTest($test_link);
                }else {
                    $return_out['err']=TRUE;
                    $return_out['err_txt']='Пользователь не авторизован для изменения! Пройдите авторизацию.';
                }
            }
            
            foreach ($_FILES as $v)
            {
                $err=$upl->UploadFile($v,$test_link);
                if($err!=='')
                {
                    $return_out['err']=TRUE;
                    $return_out['err_txt'].=$err;
                }
            }
            
        }else{
            $return_out['err']=TRUE;
            $return_out['err_txt']='Пользователь не авторизован! Пройдите авторизацию.';
        }
        echo json_encode($return_out);
    }
    /**
     * <p>Изменение/Сохранение нового теста</p>
    */
    public function editTest($f3,$params=NULL) {
        global $db;
        $return_out['err']=FALSE;
        $return_out['err_txt']='';

        $log_err_txt=Security::loginTest($f3,$db);
        if( $f3->get('user.access')<=1 )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt'].=$log_err_txt;
            echo json_encode($return_out);
            exit;
        }
        if (!isset($_POST['test_data']))
        {
            $return_out['err']=TRUE;
            $return_out['err_txt'].='Ошибка при передаче данных теста. Попробуйте снова.';
            echo json_encode($return_out);
            exit;
        }
        if($params['variant_link']!=='0'){
            if( !$t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id')) )
            {
                $return_out['err']=TRUE;
                $return_out['err_txt'].='Попытка изменить тест, которого не существует, или тест другого пользователя. Повторите операцию или закройте данное окно и попробуйте создать свой тест.';
                echo json_encode($return_out);
                exit;
            }
        }
        
        $_POST=CFuns::sanitizeString($_POST);
        $json_str_test=htmlspecialchars_decode(htmlspecialchars_decode($_POST['test_data']));
        
        $test_data=json_decode($json_str_test,TRUE);

        if($test_data===null)
        {
            $return_out['err']=TRUE;
            $return_out['err_txt'].='Ошибка парсинга файла JSON: Смените кодировку на UTF8.';
            echo json_encode($return_out);
            exit;
        }
        //Ошибки обработаны, выполенение запроса к БД:
        $t=new Tests($db);
            
        switch ($test_data['test_cu']) {
            case 'new':
                //Создание нового теста
                $t->createTest($test_data,$f3->get('user.id'));
                break;
            case 'old':
                //Изменение существующего
                $t->updateTest($test_data);
                break;
            default:
                $return_out['err']=TRUE;
                $return_out['err_txt'].='Ошибка при сохранении теста, орбатитесь к администратору';
                echo json_encode($return_out);
                break;
        }
        echo json_encode($return_out);
        exit;       
    }
    /**
     * <p>Удаление теста</p>
    */
    public function deleteTest($f3,$params=NULL) {
        global $db;
        $return_out['err']=FALSE;
        $return_out['err_txt']='';

        $log_err_txt=Security::loginTest($f3,$db);
        if( $f3->get('user.access')<=1 )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt'].=$log_err_txt;
            echo json_encode($return_out);
            exit;
        }
        if ( !preg_match("/[^0-9]/",$params['test_id']) )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt'].='Ошибка при передаче данных теста. Попробуйте снова.';
            echo json_encode($return_out);
            exit;
        }
        if($params['variant_link']!=='0'){
            if( !$t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id')) )
            {
                $return_out['err']=TRUE;
                $return_out['err_txt'].='Попытка изменить тест, которого не существует, или тест другого пользователя. Повторите операцию или закройте данное окно и попробуйте создать свой тест.';
                echo json_encode($return_out);
                exit;
            }
        }

    }

    function makeJSONtoFolderTest() {
        
                
        $t->DeleteTest($test_data['link']);

        $test_struct=$t->AddTest($test_data,$f3->get('user.id'),$f3->get('user.name'));
        $return_out['link']=SITE_DOMAIN.'test/'.$test_struct['link'];
    //Сохранение загруженных файлов
        
        $upl=new U($user->user_data);
        
    //Вставка json_str в папку с тестом
        if( $upl->UploadJSONTestData($json_str_test, $test_struct['link']) !== FALSE )
        {
        //Генерация архива и ссылки на него
            $test_path=$upl->file_dir.$test_struct['link'].'/';
        //Ссылка на архив теста для загрузки на стороне пользователя
            $link = $test_path.'test_'.date('is').'.zip';

            Zip::zipDir($test_path, $link);

            $return_out['test_file_link']=SITE_DOMAIN.$link;
            
        }else{
            $return_out['err']=TRUE;
            $return_out['err_txt'].=' Ошибка при сохранении JSON данных теста, проверьте кодировку или попробуйте снова';
        }
    
    }
}

?>